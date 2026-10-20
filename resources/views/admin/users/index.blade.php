@extends('layouts.admin')

@section('title', 'Usuarios y Permisos RBAC')
@section('header', '9. Gestión de Usuarios y Matriz de Permisos (RBAC)')
@section('subheader', 'Control de cuentas operativas por rol (Administrador, Vendedor, Editor de Catálogo, Supervisor) bajo principio de mínimo privilegio.')

@section('content')
    <div class="flex items-center justify-between">
        <p class="text-xs text-slate-600">Total cuentas registradas: <strong>{{ $users->total() }}</strong></p>
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 rounded-lg bg-amber-500 text-slate-950 font-bold text-xs hover:bg-amber-400">
            + Crear Usuario
        </a>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Usuario / Correo</th>
                        <th class="px-4 py-3 text-left">Rol Asignado</th>
                        <th class="px-4 py-3 text-left">Empresa / Teléfono</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $u)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $u->name }}</div>
                                <div class="text-slate-500">{{ $u->email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($u->role)
                                    <span class="px-2.5 py-0.5 rounded-md font-bold bg-slate-900 text-amber-400 text-[11px]">
                                        {{ $u->role->name }} ({{ $u->role->slug }})
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[11px]">
                                        Cliente Registrado (Sin acceso Admin)
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <div>{{ $u->company ?: '—' }}</div>
                                <div class="text-[11px]">{{ $u->phone }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $u->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $u->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    @if($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.toggle', $u) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-2.5 py-1 rounded border border-slate-300 text-slate-700 hover:bg-slate-50 cursor-pointer">
                                                {{ $u->is_active ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.users.edit', $u) }}" class="px-2.5 py-1 rounded bg-slate-900 text-white hover:bg-slate-800 font-semibold">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Role & Permission Matrix -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-4">
        <div>
            <h2 class="text-sm font-bold text-slate-900">Matriz de Roles y Permisos Efectivos en Servidor</h2>
            <p class="text-xs text-slate-500">Cada petición HTTP al panel administrativo verifica estos permisos mediante middleware RBAC.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            @foreach($roles as $role)
                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900">{{ $role->name }}</span>
                        <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-slate-200 text-slate-800">{{ $role->slug }}</span>
                    </div>
                    <p class="text-[11px] text-slate-500">{{ $role->description }}</p>
                    <div class="pt-2 border-t border-slate-200">
                        <div class="font-semibold text-slate-700 mb-1.5">Permisos ({{ $role->permissions->count() }}):</div>
                        <div class="flex flex-wrap gap-1">
                            @foreach($role->permissions as $perm)
                                <span class="px-1.5 py-0.5 rounded bg-white border border-slate-200 font-mono text-[10px] text-slate-700">{{ $perm->slug }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
