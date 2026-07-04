<?php

namespace App\Http\Controllers;

use App\Models\WeaponImportBatch;
use App\Services\WeaponImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class VestImportController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            $user = $request->user();
            abort_unless($user?->isAdmin() || $user?->isResponsibleLevelOne(), 403);

            return $next($request);
        });
    }

    public function index()
    {
        $this->authorize('import', \App\Models\Vest::class);

        $batches = WeaponImportBatch::query()
            ->with(['file', 'uploadedBy', 'executedBy'])
            ->where('type', WeaponImportBatch::TYPE_VEST)
            ->where('status', 'executed')
            ->latest()
            ->get();

        return view('vest-imports.center', compact('batches'));
    }

    public function show(Request $request, WeaponImportBatch $vestImportBatch)
    {
        abort_unless($vestImportBatch->isVestImport(), 404);

        $selectedBatch = $this->loadBatch($vestImportBatch->id);

        return view('vest-imports.batch', [
            'selectedBatch' => $selectedBatch,
            'openPreview' => $request->boolean('preview') && ($selectedBatch->isDraft() || $selectedBatch->isProcessing()),
        ]);
    }

    public function preview(Request $request, WeaponImportService $importService)
    {
        $this->authorize('import', \App\Models\Vest::class);

        $data = $request->validate([
            'document' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:10240'],
        ]);

        try {
            $batch = $importService->createPreviewBatch(
                $data['document'],
                $request->user(),
                WeaponImportBatch::TYPE_VEST
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'document' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Vest import preview failed.', [
                'user_id' => $request->user()?->id,
                'exception' => $exception,
            ]);

            throw ValidationException::withMessages([
                'document' => 'No se pudo procesar el archivo seleccionado.',
            ]);
        }

        $redirectUrl = route('vest-imports.show', [
            'vestImportBatch' => $batch->id,
            'preview' => 1,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'redirect_url' => $redirectUrl,
                'batch_id' => $batch->id,
            ]);
        }

        return redirect()->to($redirectUrl);
    }

    public function startExecution(Request $request, WeaponImportBatch $vestImportBatch, WeaponImportService $importService)
    {
        abort_unless($vestImportBatch->isVestImport(), 404);

        try {
            $batch = $importService->startBatchExecution($vestImportBatch, $request->user());
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'batch' => 'No se pudo iniciar la ejecución del lote.',
            ]);
        }

        return response()->json([
            'progress' => $importService->progressData($batch),
            'status_url' => route('vest-imports.status', $batch),
            'process_url' => route('vest-imports.process', $batch),
            'redirect_url' => route('vest-imports.show', $batch),
        ]);
    }

    public function processExecution(Request $request, WeaponImportBatch $vestImportBatch, WeaponImportService $importService)
    {
        abort_unless($vestImportBatch->isVestImport(), 404);

        try {
            $batch = $importService->processBatchChunk($vestImportBatch, $request->user());
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'batch' => 'No se pudo continuar la ejecución del lote.',
            ]);
        }

        return response()->json([
            'progress' => $importService->progressData($batch),
            'redirect_url' => route('vest-imports.show', $batch),
        ]);
    }

    public function executionStatus(Request $request, WeaponImportBatch $vestImportBatch, WeaponImportService $importService)
    {
        abort_unless($vestImportBatch->isVestImport(), 404);
        abort_unless(
            $vestImportBatch->uploaded_by === null
            || $vestImportBatch->uploaded_by === $request->user()?->id
            || $request->user()?->isAdmin(),
            403
        );

        $batch = $this->loadBatch($vestImportBatch->id);

        return response()->json([
            'progress' => $importService->progressData($batch),
            'redirect_url' => route('vest-imports.show', $batch),
        ]);
    }

    public function execute(Request $request, WeaponImportBatch $vestImportBatch, WeaponImportService $importService)
    {
        abort_unless($vestImportBatch->isVestImport(), 404);

        try {
            $batch = $importService->executeBatch($vestImportBatch, $request->user());
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'batch' => 'No se pudieron ejecutar los cambios del lote.',
            ]);
        }

        return redirect()
            ->route('vest-imports.show', $batch)
            ->with('status', 'Carga ejecutada correctamente.');
    }

    public function discard(Request $request, WeaponImportBatch $vestImportBatch, WeaponImportService $importService)
    {
        abort_unless($vestImportBatch->isVestImport(), 404);

        try {
            $importService->discardDraftBatch($vestImportBatch);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'batch' => 'No se pudo cancelar la carga pendiente.',
            ]);
        }

        return redirect()
            ->route('vest-imports.index')
            ->with('status', 'Carga cancelada. Puedes subir un nuevo documento.');
    }

    private function loadBatch(int $id): WeaponImportBatch
    {
        return WeaponImportBatch::query()
            ->where('type', WeaponImportBatch::TYPE_VEST)
            ->with([
                'file',
                'uploadedBy',
                'executedBy',
                'rows' => fn ($query) => $query
                    ->orderByRaw("CASE action WHEN 'error' THEN 0 WHEN 'create' THEN 1 WHEN 'update' THEN 2 WHEN 'no_change' THEN 3 ELSE 4 END")
                    ->orderBy('row_number'),
                'rows.vest',
                'rows.client',
            ])
            ->findOrFail($id);
    }
}
