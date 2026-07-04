<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vest;
use App\Support\VestAlert;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class VestQueryService
{
    /**
     * @return array<string, int>
     */
    public function kpiCounts(User $user): array
    {
        $base = Vest::query()->forUserPortfolio($user);
        $today = now()->startOfDay();

        return [
            VestAlert::ALERT_ALL => (clone $base)->count(),
            VestAlert::ALERT_VIGENT => (clone $base)->whereDate('expires_at', '>', $today->copy()->addDays(365))->count(),
            VestAlert::ALERT_PREVENTIVE => (clone $base)
                ->whereDate('expires_at', '>', $today->copy()->addDays(180))
                ->whereDate('expires_at', '<=', $today->copy()->addDays(365))
                ->count(),
            VestAlert::ALERT_CRITICAL => (clone $base)
                ->whereDate('expires_at', '>=', $today)
                ->whereDate('expires_at', '<=', $today->copy()->addDays(179))
                ->count(),
            VestAlert::ALERT_EXPIRED => (clone $base)->whereDate('expires_at', '<', $today)->count(),
            VestAlert::ALERT_UNASSIGNED => (clone $base)->whereNull('worker_id')->count(),
        ];
    }

    public function buildIndexQuery(User $user, array $filters): Builder
    {
        $query = Vest::query()
            ->forUserPortfolio($user)
            ->with(['client', 'worker', 'post', 'photos'])
            ->withCount('photos');

        $this->applySearch($query, $filters['q'] ?? null);
        $this->applyFilters($query, $filters);

        return $query->orderByDesc('updated_at');
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        $search = trim((string) $search);
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $inner) use ($search) {
            $inner->where('serial_number', 'like', "%{$search}%")
                ->orWhere('brand', 'like', "%{$search}%")
                ->orWhere('batch', 'like', "%{$search}%")
                ->orWhereHas('worker', fn (Builder $worker) => $worker
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('document', 'like', "%{$search}%"))
                ->orWhereHas('client', fn (Builder $client) => $client->where('name', 'like', "%{$search}%"))
                ->orWhereHas('post', fn (Builder $post) => $post->where('name', 'like', "%{$search}%"));
        });
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['client_id'])) {
            $query->where('client_id', (int) $filters['client_id']);
        }

        if (! empty($filters['post_id'])) {
            $query->where('post_id', (int) $filters['post_id']);
        }

        if (! empty($filters['brand'])) {
            $query->where('brand', 'like', '%'.trim((string) $filters['brand']).'%');
        }

        if (($filters['assigned'] ?? '') === 'yes') {
            $query->whereNotNull('worker_id');
        } elseif (($filters['assigned'] ?? '') === 'no') {
            $query->whereNull('worker_id');
        }

        $this->applyAlertFilter($query, VestAlert::normalizeAlertFilter($filters['alert'] ?? null));
    }

    private function applyAlertFilter(Builder $query, ?string $alert): void
    {
        if ($alert === null || $alert === VestAlert::ALERT_ALL) {
            return;
        }

        $today = Carbon::today();

        if ($alert === VestAlert::ALERT_UNASSIGNED) {
            $query->whereNull('worker_id');

            return;
        }

        if ($alert === VestAlert::ALERT_EXPIRED) {
            $query->whereDate('expires_at', '<', $today);

            return;
        }

        if ($alert === VestAlert::ALERT_CRITICAL) {
            $query->whereDate('expires_at', '>=', $today)
                ->whereDate('expires_at', '<=', $today->copy()->addDays(179));

            return;
        }

        if ($alert === VestAlert::ALERT_PREVENTIVE) {
            $query->whereDate('expires_at', '>', $today->copy()->addDays(180))
                ->whereDate('expires_at', '<=', $today->copy()->addDays(365));

            return;
        }

        if ($alert === VestAlert::ALERT_VIGENT) {
            $query->whereDate('expires_at', '>', $today->copy()->addDays(365));
        }
    }
}
