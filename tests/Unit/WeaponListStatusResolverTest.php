<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\Weapon;
use App\Models\WeaponDocument;
use App\Models\WeaponPostAssignment;
use App\Support\PostCustodyRole;
use App\Support\WeaponListStatusResolver;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WeaponListStatusResolverTest extends TestCase
{
    public function test_armerillo_with_expired_renewal_composes_status_and_uses_alert_row_color(): void
    {
        $weapon = $this->weaponWithCustody(PostCustodyRole::ARMERILLO);
        $weapon->setRelation('documents', collect([
            $this->expiredRenewalDocument(348),
        ]));

        $status = WeaponListStatusResolver::for($weapon);

        $this->assertStringStartsWith(__('Armerillo').' — ', $status['text']);
        $this->assertStringContainsString('348', $status['text']);
        $this->assertStringContainsString('vencido', $status['text']);
        $this->assertSame('bg-red-200', $status['row_class']);
        $this->assertSame('text-red-800', $status['text_class']);
        $this->assertSame('danger', $status['tone']);
    }

    public function test_sin_destino_with_expired_renewal_composes_status(): void
    {
        $weapon = new Weapon;
        $weapon->setRelation('openIncidents', collect());
        $weapon->setRelation('activePostAssignment', null);
        $weapon->setRelation('activeWorkerAssignment', null);
        $weapon->setRelation('documents', collect([
            $this->expiredRenewalDocument(10),
        ]));

        $status = WeaponListStatusResolver::for($weapon);

        $this->assertStringStartsWith(__('Sin destino').' — ', $status['text']);
        $this->assertSame('bg-red-200', $status['row_class']);
    }

    public function test_armerillo_without_renewal_alert_keeps_operational_status_only(): void
    {
        $weapon = $this->weaponWithCustody(PostCustodyRole::ARMERILLO);
        $weapon->setRelation('documents', collect());

        $status = WeaponListStatusResolver::for($weapon);

        $this->assertSame(__('Armerillo'), $status['text']);
        $this->assertSame('text-emerald-700', $status['text_class']);
        $this->assertSame('', $status['row_class']);
    }

    private function weaponWithCustody(string $custodyRole): Weapon
    {
        $post = new Post(['custody_role' => $custodyRole, 'name' => 'Puesto test']);
        $assignment = new WeaponPostAssignment;
        $assignment->setRelation('post', $post);

        $weapon = new Weapon;
        $weapon->setRelation('openIncidents', collect());
        $weapon->setRelation('activePostAssignment', $assignment);
        $weapon->setRelation('activeWorkerAssignment', null);

        return $weapon;
    }

    private function expiredRenewalDocument(int $daysAgo): WeaponDocument
    {
        return new WeaponDocument([
            'is_renewal' => true,
            'is_permit' => false,
            'valid_until' => Carbon::now()->startOfDay()->subDays($daysAgo),
        ]);
    }
}
