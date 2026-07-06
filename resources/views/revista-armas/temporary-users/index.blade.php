<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Usuarios temporales') }}</h2>
                <p class="sj-section-header__subtitle">{{ __('Perfiles reutilizables para acceso de campo a Revista armas.') }}</p>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('revista-armas.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Revista armas') }}</a>
                <a href="{{ route('revista-armas.temporary-users.create') }}" class="sj-ui-btn sj-ui-btn--primary">{{ __('Crear usuario') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="sj-page-shell sj-page-shell--wide">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="sj-ui-card overflow-hidden">
                <div class="sj-table-wrap overflow-x-auto">
                <table class="sj-table sj-table--align-left min-w-full text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Nombre') }}</th>
                            <th>{{ __('Correo') }}</th>
                            @if ($isAdmin)
                                <th>{{ __('Responsable') }}</th>
                                <th>{{ __('Compartido') }}</th>
                                <th>{{ __('Autorizados') }}</th>
                            @endif
                            <th>{{ __('Accesos activos') }}</th>
                            <th>{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-3 py-2 font-medium">{{ $user->name }}</td>
                                <td class="px-3 py-2">{{ $user->email }}</td>
                                @if ($isAdmin)
                                    <td class="px-3 py-2">{{ $user->ownerResponsible?->name ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $user->is_shared ? __('Sí') : __('No') }}</td>
                                    <td class="px-3 py-2">
                                        @if ($user->is_shared)
                                            {{ $user->authorized_responsibles_count }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                @endif
                                <td class="px-3 py-2 text-center">{{ $user->active_grants_count }}</td>
                                <td class="px-3 py-2 text-right space-x-2">
                                    <a href="{{ route('revista-armas.temporary-users.edit', $user) }}" class="text-[#0b6fb6] font-semibold">{{ __('Editar') }}</a>
                                    <form method="POST" action="{{ route('revista-armas.temporary-users.destroy', $user) }}" class="inline" onsubmit="return confirm(@js(__('¿Desactivar este usuario temporal? Las fotos en revisión se conservan.')))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 font-semibold">{{ __('Eliminar') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $isAdmin ? 7 : 4 }}" class="px-3 py-8 text-center text-slate-500">{{ __('No hay usuarios temporales.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
            <div class="mt-4">{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
