<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\Weapon;
use App\Models\WeaponPostAssignment;
use App\Models\WeaponWorkerAssignment;
use App\Models\Worker;
use App\Services\Formats\MonthlyWeaponReviewRowMapper;
use Tests\TestCase;

class MonthlyWeaponReviewRowMapperTest extends TestCase
{
    public function test_post_only_assignment_leaves_holder_name_and_document_empty(): void
    {
        $post = new Post(['name' => 'Puesto Norte']);
        $postAssignment = new WeaponPostAssignment(['is_active' => true]);
        $postAssignment->setRelation('post', $post);

        $weapon = new Weapon(['weapon_type' => 'Pistola', 'serial_number' => 'MAP-POST']);
        $weapon->setRelation('activePostAssignment', $postAssignment);
        $weapon->setRelation('activeWorkerAssignment', null);

        $row = (new MonthlyWeaponReviewRowMapper)->map($weapon, 1);

        $this->assertSame('Puesto Norte', $row[1]);
        $this->assertSame('', $row[3]);
        $this->assertSame('', $row[4]);
    }

    public function test_worker_assignment_fills_holder_name_and_document(): void
    {
        $worker = new Worker(['name' => 'Juan Escolta', 'document' => '1234567890']);
        $workerAssignment = new WeaponWorkerAssignment(['is_active' => true]);
        $workerAssignment->setRelation('worker', $worker);

        $weapon = new Weapon(['weapon_type' => 'Pistola', 'serial_number' => 'MAP-WORKER']);
        $weapon->setRelation('activePostAssignment', null);
        $weapon->setRelation('activeWorkerAssignment', $workerAssignment);

        $row = (new MonthlyWeaponReviewRowMapper)->map($weapon, 2);

        $this->assertSame('', $row[1]);
        $this->assertSame('Juan Escolta', $row[3]);
        $this->assertSame('1234567890', $row[4]);
    }

    public function test_worker_on_post_fills_post_and_holder_fields(): void
    {
        $post = new Post(['name' => 'Recepción']);
        $postAssignment = new WeaponPostAssignment(['is_active' => true]);
        $postAssignment->setRelation('post', $post);

        $worker = new Worker(['name' => 'María Guardia', 'document' => '9876543210']);
        $workerAssignment = new WeaponWorkerAssignment(['is_active' => true]);
        $workerAssignment->setRelation('worker', $worker);

        $weapon = new Weapon(['weapon_type' => 'Pistola', 'serial_number' => 'MAP-MIX']);
        $weapon->setRelation('activePostAssignment', $postAssignment);
        $weapon->setRelation('activeWorkerAssignment', $workerAssignment);

        $row = (new MonthlyWeaponReviewRowMapper)->map($weapon, 3);

        $this->assertSame('Recepción', $row[1]);
        $this->assertSame('María Guardia', $row[3]);
        $this->assertSame('9876543210', $row[4]);
    }
}
