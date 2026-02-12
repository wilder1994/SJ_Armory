<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Usuarios') }}
            </h2>
            <a href="{{ route('users.create') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                {{ __('Nuevo usuario') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded bg-green-50 p-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Nombre') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Correo electrónico') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Responsable') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Cargo') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Nivel de responsabilidad') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Centro de costo') }}</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Estado activo') }}</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-600">{{ __('Acciones') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($users as $user)
                                    <tr>
                                        <td class="px-3 py-2">{{ $user->name }}</td>
                                        <td class="px-3 py-2">{{ $user->email }}</td>
                                        <td class="px-3 py-2">{{ $roles[$user->role] ?? $user->role }}</td>
                                        <td class="px-3 py-2">{{ $user->position?->name ?? '-' }}</td>
                                        <td class="px-3 py-2">
                                            {{ $user->responsibilityLevel?->level ? $user->responsibilityLevel->level . ' - ' . $user->responsibilityLevel->name : '-' }}
                                        </td>
                                        <td class="px-3 py-2">{{ $user->cost_center ?: '-' }}</td>
                                        <td class="px-3 py-2">
                                            <span class="{{ $user->is_active ? 'text-green-700' : 'text-gray-500' }}">
                                                {{ $user->is_active ? __('Activo') : __('Inactivo') }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-right space-x-2">
                                            <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ __('Editar') }}
                                            </a>
                                            <form method="POST" action="{{ route('users.status', $user) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}">
                                                <button class="text-xs text-amber-600 hover:text-amber-900">
                                                    {{ $user->is_active ? __('Desactivar') : __('Activar') }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-xs text-red-600 hover:text-red-900" onclick="return confirm('¿Eliminar usuario?')">
                                                    {{ __('Eliminar') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-6 text-center text-gray-500">
                                            {{ __('Sin usuarios registrados.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>




