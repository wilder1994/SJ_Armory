<?php

namespace App\Services;

use App\Models\IncidentType;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponIncident;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WeaponIncidentReportService
{
    public function filtersFromRequest(array $input): array
    {
        return [
            'year' => $this->normalizeYear($input['year'] ?? null),
        ];
    }

    public function availableYears(User $user, ?IncidentType $selectedType = null): Collection
    {
        $query = WeaponIncident::query()
            ->whereNotNull('event_at');

        if ($selectedType) {
            $query->where('incident_type_id', $selectedType->id);
        }

        if ($user->isResponsible() && !$user->isAdmin()) {
            $query->whereHas('weapon.activeClientAssignment', function (Builder $assignmentQuery) use ($user) {
                $assignmentQuery->where('responsible_user_id', $user->id);
            });
        }

        return $query
            ->get(['event_at'])
            ->map(fn (WeaponIncident $incident) => (int) optional($incident->event_at)->format('Y'))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();
    }

    public function paginated(User $user, array $filters, ?IncidentType $selectedType = null): LengthAwarePaginator
    {
        return $this->baseQuery($user, $filters, $selectedType)
            ->with($this->relationships())
            ->orderByDesc('event_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function dashboard(User $user, array $filters, ?IncidentType $selectedType = null): array
    {
        $incidents = $this->baseQuery($user, $filters, $selectedType)
            ->with($this->relationships())
            ->get();

        $selectedTypeId = $selectedType?->id;
        $selectedYear = $filters['year'];
        $periodLabel = $selectedYear ? (string) $selectedYear : 'todos los años';

        $allTypes = IncidentType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $typeItems = $allTypes->map(function (IncidentType $type) use ($incidents, $selectedTypeId) {
            return [
                'id' => $type->id,
                'code' => $type->code,
                'label' => $type->name,
                'value' => $incidents->where('incident_type_id', $type->id)->count(),
                'color' => $type->color ?? '#475569',
                'selected' => $selectedTypeId === $type->id,
            ];
        })->values();

        $modalityItems = $incidents
            ->filter(fn (WeaponIncident $incident) => $selectedTypeId ? $incident->incident_type_id === $selectedTypeId : true)
            ->groupBy(fn (WeaponIncident $incident) => $incident->modality?->name ?? 'Sin modalidad')
            ->map(fn (Collection $group, string $label) => [
                'label' => $label,
                'value' => $group->count(),
            ])
            ->sortByDesc('value')
            ->values();

        $recentItems = $incidents
            ->sortByDesc(fn (WeaponIncident $incident) => $incident->latestActivityAt()?->timestamp ?? 0)
            ->take(5)
            ->map(function (WeaponIncident $incident) {
                $latestNote = $incident->latestUpdate?->note ?: ($incident->note ?: $incident->observation);

                return [
                    'id' => $incident->id,
                    'label' => $incident->type?->name ?? 'Novedad',
                    'weapon' => trim(($incident->weapon?->internal_code ?? '-') . ' / ' . ($incident->weapon?->serial_number ?? '-')),
                    'status' => WeaponIncident::statusOptions()[$incident->status] ?? $incident->status,
                    'event_at' => $incident->latestActivityAt(),
                    'note' => $latestNote ?: '-',
                ];
            })
            ->values()
            ->all();

        $typeChart = $this->buildDonutChart(
            $typeItems->all(),
            'Total de reportes',
            $incidents->count(),
            'Participación por tipo'
        );

        $modalityChart = $this->buildDonutChart(
            $modalityItems->all(),
            $selectedType ? 'Modalidades de ' . $selectedType->name : 'Modalidades vigentes',
            $modalityItems->sum('value'),
            $selectedType ? 'Distribución de ' . $selectedType->name : 'Distribución global por modalidad'
        );

        $timelineChart = $this->buildTimelineChart($incidents, $selectedYear);
        $openStatuses = [WeaponIncident::STATUS_OPEN, WeaponIncident::STATUS_IN_PROGRESS];
        $topModality = $modalityItems->sortByDesc('value')->first();

        return [
            'as_of' => now(),
            'selected_year' => $selectedYear,
            'selected_type' => $selectedType?->only(['id', 'code', 'name']),
            'kpis' => [
                [
                    'label' => 'Novedades abiertas',
                    'value' => $incidents->whereIn('status', $openStatuses)->count(),
                    'tone' => 'red',
                    'helper' => 'Casos activos en el alcance actual',
                ],
                [
                    'label' => 'Armas afectadas',
                    'value' => $incidents->pluck('weapon_id')->unique()->count(),
                    'tone' => 'blue',
                    'helper' => 'Armas con al menos una novedad',
                ],
                [
                    'label' => 'Modalidad principal',
                    'value' => $topModality['value'] ?? 0,
                    'tone' => 'orange',
                    'helper' => $topModality['label'] ?? 'Sin modalidad dominante',
                ],
                [
                    'label' => 'En el periodo',
                    'value' => $incidents->count(),
                    'tone' => 'slate',
                    'helper' => 'Novedades registradas en ' . $periodLabel,
                ],
            ],
            'type_chart' => $typeChart,
            'modality_chart' => $modalityChart,
            'timeline_chart' => $timelineChart,
            'recent_items' => $recentItems,
        ];
    }

    public function searchWeapons(User $user, ?string $term = null, int $limit = 8): Collection
    {
        $query = Weapon::query()
            ->select(['id', 'brand', 'serial_number', 'permit_expires_at'])
            ->with('activeClientAssignment.client')
            ->orderBy('serial_number');

        $this->applyRoleScopeToWeapons($query, $user);

        $term = trim((string) $term);

        if ($term !== '') {
            $query->where(function (Builder $builder) use ($term) {
                $builder->where('serial_number', 'like', '%' . $term . '%')
                    ->orWhere('brand', 'like', '%' . $term . '%')
                    ->orWhereHas('activeClientAssignment.client', function (Builder $clientQuery) use ($term) {
                        $clientQuery->where('name', 'like', '%' . $term . '%');
                    });
            });
        }

        return $query
            ->limit(max(1, min($limit, 12)))
            ->get()
            ->map(fn (Weapon $weapon) => $this->mapWeaponSearchResult($weapon));
    }

    public function findSearchWeapon(User $user, int $weaponId): ?array
    {
        $query = Weapon::query()
            ->select(['id', 'brand', 'serial_number', 'permit_expires_at'])
            ->with('activeClientAssignment.client')
            ->whereKey($weaponId);

        $this->applyRoleScopeToWeapons($query, $user);

        $weapon = $query->first();

        return $weapon ? $this->mapWeaponSearchResult($weapon) : null;
    }

    private function baseQuery(User $user, array $filters, ?IncidentType $selectedType = null): Builder
    {
        $query = WeaponIncident::query();

        if ($selectedType) {
            $query->where('incident_type_id', $selectedType->id);
        }

        if (!empty($filters['year'])) {
            $year = (int) $filters['year'];
            $query->whereBetween('event_at', [
                now()->setYear($year)->startOfYear(),
                now()->setYear($year)->endOfYear(),
            ]);
        }

        if ($user->isResponsible() && !$user->isAdmin()) {
            $query->whereHas('weapon.activeClientAssignment', function (Builder $assignmentQuery) use ($user) {
                $assignmentQuery->where('responsible_user_id', $user->id);
            });
        }

        return $query;
    }

    private function relationships(): array
    {
        return [
            'weapon.activeClientAssignment.client',
            'weapon.activeClientAssignment.responsible',
            'type',
            'modality',
            'reporter',
            'attachmentFile',
            'updates.creator',
            'updates.attachmentFile',
            'latestUpdate.creator',
            'latestUpdate.attachmentFile',
        ];
    }

    private function applyRoleScopeToWeapons(Builder $query, User $user): void
    {
        if ($user->isResponsible() && !$user->isAdmin()) {
            $query->whereHas('activeClientAssignment', function (Builder $assignmentQuery) use ($user) {
                $assignmentQuery->where('responsible_user_id', $user->id)
                    ->where('is_active', true);
            });
        }
    }

    private function mapWeaponSearchResult(Weapon $weapon): array
    {
        $expiresAt = $weapon->permit_expires_at instanceof Carbon
            ? $weapon->permit_expires_at
            : ($weapon->permit_expires_at ? Carbon::parse($weapon->permit_expires_at) : null);

        return [
            'id' => $weapon->id,
            'client' => $weapon->activeClientAssignment?->client?->name ?? 'Sin destino',
            'brand' => $weapon->brand ?? '-',
            'serial' => $weapon->serial_number ?? '-',
            'permit_expires_at' => $expiresAt?->format('Y-m-d'),
            'permit_expires_label' => $expiresAt?->format('Y-m-d') ?? 'Sin vencimiento',
            'summary' => trim(($weapon->brand ?? '-') . ' / ' . ($weapon->serial_number ?? '-')),
        ];
    }

    private function buildDonutChart(array $items, string $centerLabel, int $centerValue, string $centerHelper): array
    {
        $palette = ['#0b6fb6', '#dc2626', '#d97706', '#7c3aed', '#0f766e', '#334155', '#1d4ed8', '#be123c'];

        $normalized = collect($items)->values()->map(function (array $item, int $index) use ($palette) {
            return [
                'label' => $item['label'],
                'value' => (int) ($item['value'] ?? 0),
                'color' => $item['color'] ?? $palette[$index % count($palette)],
                'code' => $item['code'] ?? null,
                'selected' => (bool) ($item['selected'] ?? false),
            ];
        });

        $total = max(0, (int) $normalized->sum('value'));
        $offset = 0.0;

        $itemsWithShare = $normalized->map(function (array $item) use ($total, &$offset) {
            $share = $total > 0 ? round(($item['value'] / $total) * 100, 1) : 0.0;
            $item['share'] = $share;
            $item['share_label'] = number_format($share, 1) . '%';
            $item['from'] = $offset;
            $offset += $share;
            $item['to'] = $offset;

            return $item;
        });

        return [
            'center_label' => $centerLabel,
            'center_value' => $centerValue,
            'center_helper' => $centerHelper,
            'gradient' => $this->buildConicGradient($itemsWithShare),
            'has_data' => $total > 0,
            'items' => $itemsWithShare->all(),
        ];
    }

    private function buildTimelineChart(Collection $incidents, ?int $selectedYear): array
    {
        if ($selectedYear) {
            $items = collect(range(1, 12))
                ->map(function (int $monthNumber) use ($incidents, $selectedYear) {
                    $month = now()->setYear($selectedYear)->setMonth($monthNumber)->startOfMonth();
                    $key = $month->format('Y-m');

                    return [
                        'label' => ucfirst($month->translatedFormat('M')),
                        'value' => $incidents->filter(fn (WeaponIncident $incident) => $incident->event_at?->format('Y-m') === $key)->count(),
                    ];
                })
                ->values();

            return [
                'title' => 'Evolución mensual',
                'items' => $items->all(),
                'max' => max(1, (int) $items->max('value')),
            ];
        }

        $years = $incidents
            ->map(fn (WeaponIncident $incident) => (int) optional($incident->event_at)->format('Y'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        if ($years->isEmpty()) {
            $years = collect([(int) now()->year]);
        }

        $items = $years->map(function (int $year) use ($incidents) {
            return [
                'label' => (string) $year,
                'value' => $incidents->filter(fn (WeaponIncident $incident) => (int) $incident->event_at?->format('Y') === $year)->count(),
            ];
        })->values();

        return [
            'title' => 'Evolución anual',
            'items' => $items->all(),
            'max' => max(1, (int) $items->max('value')),
        ];
    }

    private function buildConicGradient(Collection $items): string
    {
        $segments = $items
            ->filter(fn (array $item) => $item['value'] > 0)
            ->values();

        if ($segments->isEmpty()) {
            return 'conic-gradient(#dbe4ef 0deg 360deg)';
        }

        $stops = [];
        $offset = 0.0;

        foreach ($segments as $item) {
            $degrees = max(0.0, ($item['share'] / 100) * 360);
            $nextOffset = min(360.0, $offset + $degrees);
            $stops[] = sprintf('%s %.2fdeg %.2fdeg', $item['color'], $offset, $nextOffset);
            $offset = $nextOffset;
        }

        if ($offset < 360.0) {
            $stops[] = sprintf('%s %.2fdeg 360deg', $segments->last()['color'], $offset);
        }

        return 'conic-gradient(' . implode(', ', $stops) . ')';
    }

    private function normalizeYear(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $yearText = trim((string) $value);

        if ($yearText === '' || strtolower($yearText) === 'all') {
            return null;
        }

        $year = (int) $yearText;
        $maxYear = (int) now()->year + 1;

        return $year >= 2000 && $year <= $maxYear
            ? $year
            : null;
    }
}
