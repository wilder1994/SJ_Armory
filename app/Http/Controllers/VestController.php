<?php

namespace App\Http\Controllers;

use App\Events\VestChanged;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Post;
use App\Models\Vest;
use App\Models\Worker;
use App\Services\VestQueryService;
use App\Support\VestAlert;
use Illuminate\Http\Request;

class VestController extends Controller
{
    public function __construct(
        private readonly VestQueryService $queryService,
    ) {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            abort_unless($user && ($user->isAdmin() || $user->isResponsible() || $user->isAuditor()), 403);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Vest::class);

        $filters = $this->filtersFromRequest($request);
        $vests = $this->queryService
            ->buildIndexQuery($request->user(), $filters)
            ->paginate(20)
            ->withQueryString();

        $user = $request->user();
        $clients = $user->isAdmin() || $user->isAuditor()
            ? Client::orderBy('name')->get(['id', 'name'])
            : $user->clients()->orderBy('name')->get(['clients.id', 'clients.name']);

        return view('vests.index', [
            'vests' => $vests,
            'filters' => $filters,
            'kpiCounts' => $this->queryService->kpiCounts($user),
            'clients' => $clients,
            'alertLabels' => VestAlert::ALERT_LABELS,
        ]);
    }

    public function show(Vest $vest)
    {
        $this->authorize('view', $vest);

        $vest->load(['client', 'worker', 'post', 'photos.file']);

        return view('vests.show', [
            'vest' => $vest,
            'alert' => VestAlert::forVest($vest),
            'photosByDescription' => $vest->photos->keyBy('description'),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Vest::class);

        return view('vests.create', $this->formOptions($request));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Vest::class);

        $data = $this->validated($request);
        $this->assertClientInPortfolio($request->user(), (int) $data['client_id']);

        $vest = Vest::create($data);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'vest_created',
            'auditable_type' => Vest::class,
            'auditable_id' => $vest->id,
            'before' => null,
            'after' => $vest->only(['serial_number', 'client_id', 'worker_id', 'post_id']),
        ]);

        event(new VestChanged('created', $vest->id, ['client_id' => $vest->client_id]));

        return redirect()
            ->route('vests.show', $vest)
            ->with('status', __('Chaleco registrado correctamente.'));
    }

    public function edit(Request $request, Vest $vest)
    {
        $this->authorize('update', $vest);

        return view('vests.edit', array_merge(['vest' => $vest], $this->formOptions($request, $vest)));
    }

    public function update(Request $request, Vest $vest)
    {
        $this->authorize('update', $vest);

        $data = $this->validated($request, $vest);
        $this->assertClientInPortfolio($request->user(), (int) $data['client_id']);

        $before = $vest->only(['serial_number', 'client_id', 'worker_id', 'post_id', 'expires_at']);
        $vest->update($data);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'vest_updated',
            'auditable_type' => Vest::class,
            'auditable_id' => $vest->id,
            'before' => $before,
            'after' => $vest->fresh()->only(['serial_number', 'client_id', 'worker_id', 'post_id', 'expires_at']),
        ]);

        event(new VestChanged('updated', $vest->id, ['client_id' => $vest->client_id]));

        return redirect()
            ->route('vests.show', $vest)
            ->with('status', __('Chaleco actualizado correctamente.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(Request $request, ?Vest $vest = null): array
    {
        $user = $request->user();
        $clients = $user->isAdmin()
            ? Client::orderBy('name')->get(['id', 'name'])
            : $user->clients()->orderBy('name')->get(['clients.id', 'clients.name']);

        $clientId = old('client_id', $vest?->client_id);
        $workers = $clientId
            ? Worker::query()->active()->where('client_id', $clientId)->orderBy('name')->get(['id', 'name', 'document'])
            : collect();
        $posts = $clientId
            ? Post::query()->where('client_id', $clientId)->whereNull('archived_at')->orderBy('name')->get(['id', 'name'])
            : collect();

        return compact('clients', 'workers', 'posts', 'clientId');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Vest $vest = null): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'worker_id' => ['nullable', 'exists:workers,id'],
            'post_id' => ['nullable', 'exists:posts,id'],
            'serial_number' => ['required', 'string', 'max:120', 'unique:vests,serial_number'.($vest ? ','.$vest->id : '')],
            'brand' => ['nullable', 'string', 'max:120'],
            'batch' => ['nullable', 'string', 'max:120'],
            'size' => ['nullable', 'string', 'max:40'],
            'manufactured_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'device_responsible' => ['nullable', 'string', 'max:190'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function assertClientInPortfolio($user, int $clientId): void
    {
        if ($user->isAdmin() || $user->isAuditor()) {
            return;
        }

        abort_unless($user->clients()->whereKey($clientId)->exists(), 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'q' => trim((string) $request->query('q', '')),
            'alert' => VestAlert::normalizeAlertFilter($request->query('alert')),
            'client_id' => $request->query('client_id'),
            'post_id' => $request->query('post_id'),
            'brand' => trim((string) $request->query('brand', '')),
            'assigned' => $request->query('assigned'),
        ];
    }
}
