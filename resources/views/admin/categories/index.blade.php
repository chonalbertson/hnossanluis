@extends('layouts.admin')

@section('title', 'Categorías y Subcategorías')
@section('header', '3. Gestión de Categorías y Jerarquía del Catálogo')
@section('subheader', 'Estructura jerárquica de familias y subcategorías técnicas con validación de slug único.')

@section('content')
    <div class="flex items-center justify-between">
        <p class="text-xs text-slate-600">Total de familias principales: <strong>{{ $parentCategories->count() }}</strong></p>
        <a href="{{ route('admin.categories.create') }}" class="px-4 py-2 rounded-lg bg-amber-500 text-slate-950 hover:bg-amber-400 text-xs font-bold shadow-2xs">
            + Nueva Categoría / Subcategoría
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Nombre / Jerarquía</th>
                        <th class="px-4 py-3 text-left">Slug URL</th>
                        <th class="px-4 py-3 text-center">Orden</th>
                        <th class="px-4 py-3 text-center">Productos</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($parentCategories as $cat)
                        <tr class="bg-slate-50/70 hover:bg-slate-100/70">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $cat->name }}</div>
                                @if($cat->description)
                                    <div class="text-[11px] text-slate-500 line-clamp-1">{{ $cat->description }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-600">{{ $cat->slug }}</td>
                            <td class="px-4 py-3 text-center">{{ $cat->sort_order }}</td>
                            <td class="px-4 py-3 text-center font-bold text-slate-800">{{ $cat->products_count }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $cat->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $cat->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <form method="POST" action="{{ route('admin.categories.toggle', $cat) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2.5 py-1 rounded border border-slate-300 text-slate-700 hover:bg-white cursor-pointer">
                                            {{ $cat->is_active ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.categories.edit', $cat) }}" class="px-2.5 py-1 rounded bg-slate-900 text-white hover:bg-slate-800 font-semibold">Editar</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" onsubmit="return confirm('¿Eliminar esta categoría?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 rounded border border-rose-200 text-rose-700 hover:bg-rose-50 cursor-pointer">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        @foreach($cat->children as $sub)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2.5 pl-9">
                                    <span class="text-slate-400 mr-1">↳</span>
                                    <span class="font-semibold text-slate-700">{{ $sub->name }}</span>
                                </td>
                                <td class="px-4 py-2.5 font-mono text-slate-500">{{ $sub->slug }}</td>
                                <td class="px-4 py-2.5 text-center text-slate-500">{{ $sub->sort_order }}</td>
                                <td class="px-4 py-2.5 text-center font-semibold text-slate-700">{{ $sub->sub_products_count }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $sub->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                        {{ $sub->is_active ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <form method="POST" action="{{ route('admin.categories.toggle', $sub) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-2 py-1 rounded border border-slate-300 text-slate-700 hover:bg-slate-50 cursor-pointer">
                                                {{ $sub->is_active ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.categories.edit', $sub) }}" class="px-2.5 py-1 rounded bg-slate-900 text-white hover:bg-slate-800 font-semibold">Editar</a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $sub) }}" onsubmit="return confirm('¿Eliminar esta subcategoría?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 rounded border border-rose-200 text-rose-700 hover:bg-rose-50 cursor-pointer">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">No hay categorías registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
