@extends('layouts.admin')

@section('title', 'Marcas del Catálogo')
@section('header', '4. Gestión de Marcas Representadas y Comercializadas')
@section('subheader', 'Control de marcas industriales, estado de verificación y visibilidad en filtros del catálogo.')

@section('content')
    <div class="flex items-center justify-between">
        <p class="text-xs text-slate-600">Marcas registradas: <strong>{{ $brands->total() }}</strong></p>
        <a href="{{ route('admin.brands.create') }}" class="px-4 py-2 rounded-lg bg-amber-500 text-slate-950 hover:bg-amber-400 text-xs font-bold shadow-2xs">
            + Nueva Marca
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Marca</th>
                        <th class="px-4 py-3 text-left">Slug</th>
                        <th class="px-4 py-3 text-center">Productos</th>
                        <th class="px-4 py-3 text-center">Verificada</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($brands as $brand)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $brand->name }}</div>
                                @if($brand->description)
                                    <div class="text-[11px] text-slate-500 line-clamp-1">{{ $brand->description }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-600">{{ $brand->slug }}</td>
                            <td class="px-4 py-3 text-center font-bold">{{ $brand->products_count }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($brand->is_verified)
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-800">Verificada</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[11px] text-slate-500 bg-slate-100">No verificada</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $brand->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $brand->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <form method="POST" action="{{ route('admin.brands.toggle', $brand) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2.5 py-1 rounded border border-slate-300 text-slate-700 hover:bg-slate-50 cursor-pointer">
                                            {{ $brand->is_active ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.brands.edit', $brand) }}" class="px-2.5 py-1 rounded bg-slate-900 text-white hover:bg-slate-800 font-semibold">Editar</a>
                                    <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" onsubmit="return confirm('¿Eliminar esta marca?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 rounded border border-rose-200 text-rose-700 hover:bg-rose-50 cursor-pointer">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">No hay marcas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
