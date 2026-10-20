@extends('layouts.admin')

@section('title', 'Editar Usuario')
@section('header', 'Editar Usuario: ' . $user->name)

@section('content')
    <div class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nombre Completo <span class="text-rose-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Correo Corporativo / Acceso <span class="text-rose-600">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Rol del Sistema (RBAC)</label>
                    <select name="role_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white font-semibold">
                        <option value="">— Cliente Externo (Sin acceso al Panel Admin) —</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected((int)old('role_id', $user->role_id) === $role->id)>{{ $role->name }} ({{ $role->slug }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Teléfono</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nueva Contraseña (dejar en blanco para mantener)</label>
                    <input type="password" name="password" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmation" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="rounded border-slate-300 text-amber-500">
                <span class="font-semibold text-slate-700">Cuenta activa</span>
            </label>

            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 cursor-pointer">Actualizar Usuario</button>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
