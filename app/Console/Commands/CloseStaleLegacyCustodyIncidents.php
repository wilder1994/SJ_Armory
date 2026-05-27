<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Weapon;
use App\Services\WeaponLegacyCustodyIncidentService;
use App\Support\LegacyCustodyIncidentTypeCode;
use App\Support\PostCustodyRole;
use Illuminate\Console\Command;

class CloseStaleLegacyCustodyIncidents extends Command
{
    protected $signature = 'weapons:close-stale-legacy-custody-incidents {--dry-run : Solo listar armas afectadas sin cerrar novedades}';

    protected $description = 'Cierra novedades legadas abiertas en armas que ya están en puestos de custodia';

    public function handle(WeaponLegacyCustodyIncidentService $legacyCloser): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $weapons = Weapon::query()
            ->whereHas('activePostAssignment.post', fn ($query) => $query->whereIn('custody_role', PostCustodyRole::all()))
            ->whereHas('openIncidents', function ($query) {
                $query->whereHas('type', fn ($typeQuery) => $typeQuery->whereIn('code', LegacyCustodyIncidentTypeCode::codes()));
            })
            ->with([
                'activePostAssignment.post',
                'openIncidents.type',
            ])
            ->orderBy('id')
            ->get();

        if ($weapons->isEmpty()) {
            $this->info('No hay armas con novedades legadas abiertas en custodia.');

            return self::SUCCESS;
        }

        $closedTotal = 0;

        foreach ($weapons as $weapon) {
            $post = $weapon->activePostAssignment?->post;
            $role = $post?->custody_role;
            $actor = $this->resolveActor($post?->owner_responsible_user_id);

            $openCount = $weapon->openIncidents
                ->filter(fn ($incident) => LegacyCustodyIncidentTypeCode::isLegacy($incident->type?->code))
                ->count();

            $this->line(sprintf(
                '- %s (%s): %d novedad(es) legada(s), custodia %s',
                $weapon->serial_number,
                $weapon->internal_code,
                $openCount,
                $role ?? '—',
            ));

            if ($dryRun || ! $role || ! $actor) {
                continue;
            }

            $closedTotal += $legacyCloser->closeOpenLegacyIncidents(
                $weapon,
                $actor,
                $role,
                __('weapons.legacy_incident_cleanup_custody', ['post' => $post->name]),
            );
        }

        if ($dryRun) {
            $this->warn('Modo simulación: no se cerró ninguna novedad.');

            return self::SUCCESS;
        }

        $this->info("Novedades legadas cerradas: {$closedTotal}");

        return self::SUCCESS;
    }

    private function resolveActor(?int $responsibleUserId): ?User
    {
        if ($responsibleUserId) {
            $user = User::query()->find($responsibleUserId);
            if ($user) {
                return $user;
            }
        }

        return User::query()->where('role', 'ADMIN')->orderBy('id')->first();
    }
}
