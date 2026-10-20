@extends('layouts.admin')

@section('title', 'Catálogo de Productos')
@section('header', '2. Gestión del Catálogo Técnico de Productos')
@section('subheader', 'Administración de productos por SKU, especificaciones técnicas, disponibilidad y visibilidad de precios.')

@section('content')
    <!-- Filters & Top Bar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-sm text-slate-600">
                Mostrando <strong class="text-slate-900">{{ $products->total() }}</strong> productos registrados
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if(auth()->user()?->hasPermission('imports.manage'))
                    <a href="{{ route('admin.imports.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 text-xs font-semibold">
                        <span>Importar / Exportar CSV</span>
                    </a>
                @endif
                @if(auth()->user()?->hasPermission('catalog.manage'))
                    <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-amber-500 text-slate-950 hover:bg-amber-400 text-xs font-bold shadow-2xs">
                        <span>+ Crear Producto</span>
                    </a>
                @endif
            </div>
        </div>

        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3 pt-2 border-t border-slate-100">
            <div class="lg:col-span-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por SKU, nombre, medida o material..."
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs focus:border-amber-500 focus:outline-none">
            </div>

            <div>
                <select name="category_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs bg-white">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->name }}</option>
                        @foreach($cat->children as $sub)
                            <option value="{{ $sub->id }}" @selected((string) request('category_id') === (string) $sub->id)>— {{ $sub->name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <div>
                <select name="brand_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs bg-white">
                    <option value="">Todas las marcas</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" @selected((string) request('brand_id') === (string) $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="price_visibility" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs bg-white">
                    <option value="">Visibilidad de precio (Todos)</option>
                    <option value="hide" @selected(request('price_visibility') === 'hide')>🔒 Precio Oculto (Cotización)</option>
                    <option value="show" @selected(request('price_visibility') === 'show')>👁️ Precio Público Habilitado</option>
                </select>
            </div>

            <div>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs bg-white">
                    <option value="">Estado publicación (Activos)</option>
                    <option value="published" @selected(request('status') === 'published')>Publicado</option>
                    <option value="draft" @selected(request('status') === 'draft')>Borrador</option>
                    <option value="archived" @selected(request('status') === 'archived')>Archivado</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 text-xs font-semibold cursor-pointer">
                    Filtrar
                </button>
                <a href="{{ route('admin.products.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs">
                    Limpiar
                </a>
            </div>
        </form>

        <div class="flex items-center gap-4 text-xs pt-1">
            <a href="{{ route('admin.products.index') }}" class="{{ !request('trashed') ? 'font-bold text-slate-900 underline' : 'text-slate-500 hover:text-slate-800' }}">
                Catálogo Activo
            </a>
            <a href="{{ route('admin.products.index', ['trashed' => 'only']) }}" class="{{ request('trashed') === 'only' ? 'font-bold text-rose-700 underline' : 'text-slate-500 hover:text-rose-700' }}">
                Papelera / Eliminados lógicos
            </a>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">SKU / Producto</th>
                        <th class="px-4 py-3 text-left">Categoría / Marca</th>
                        <th class="px-4 py-3 text-left">Precio Interno / Visibilidad en Web</th>
                        <th class="px-4 py-3 text-left">Disponibilidad</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $product->primaryImageUrl() }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 bg-slate-50 shrink-0">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-slate-900">{{ $product->sku }}</span>
                                            @if(($product->variants_count ?? 0) > 0)
                                                <span class="px-1.5 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-800 rounded">
                                                    Variable ({{ $product->variants_count }} var.)
                                                </span>
                                            @endif
                                            @if($product->is_featured)
                                                <span class="px-1.5 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800 rounded">Destacado</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('admin.products.show', $product) }}" class="font-semibold text-slate-800 hover:text-amber-600 line-clamp-1">
                                            {{ $product->name }}
                                        </a>
                                        <div class="text-[11px] text-slate-500">
                                            {{ $product->dimensions ?: 'Sin medidas' }} | {{ $product->specifications_count }} espec.
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-800">{{ $product->category?->name ?? '—' }}</div>
                                @if($product->subcategory)
                                    <div class="text-slate-500">↳ {{ $product->subcategory->name }}</div>
                                @endif
                                <div class="text-[11px] font-medium text-slate-400">{{ $product->brand?->name ?? 'Sin marca' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-slate-900 tabular-nums">
                                            @if($product->price !== null && (float) $product->price > 0)
                                                {{ $product->currency }} {{ number_format((float) $product->price, 2) }}
                                            @else
                                                <span class="text-slate-400 italic">Sin precio base</span>
                                            @endif
                                        </span>
                                        <span class="text-[11px] text-slate-400">/ {{ $product->sale_unit }}</span>
                                    </div>

                                    @if(!$product->trashed() && auth()->user()?->hasPermission('catalog.manage'))
                                        <form method="POST" action="{{ route('admin.products.toggle-price', $product) }}" class="inline-flex">
                                            @csrf
                                            @method('PATCH')
                                            @if($product->shouldShowPrice())
                                                <button type="submit"
                                                        title="Haz clic para ocultar el precio público de este producto"
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 transition-colors cursor-pointer">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    <span>Precio Visible (Ocultar)</span>
                                                </button>
                                            @else
                                                <button type="submit"
                                                        title="Haz clic para habilitar el precio público de este producto en el catálogo"
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-300 hover:bg-amber-50 hover:text-amber-900 hover:border-amber-300 transition-colors cursor-pointer">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                    <span>Oculto • Habilitar precio</span>
                                                </button>
                                            @endif
                                        </form>
                                    @else
                                        <span class="text-[11px] text-slate-500">
                                            {{ $product->shouldShowPrice() ? 'Visible en web' : 'Oculto (Solo cotización)' }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $product->availabilityBadgeClass() }}">
                                    {{ $product->availabilityLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($product->trashed())
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800">En Papelera</span>
                                @elseif($product->status === 'published')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">Publicado</span>
                                @elseif($product->status === 'draft')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800">Borrador</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-200 text-slate-700">Archivado</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    @if($product->trashed())
                                        @if(auth()->user()?->hasPermission('catalog.manage'))
                                            <form method="POST" action="{{ route('admin.products.restore', $product->id) }}">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700 font-semibold cursor-pointer">
                                                    Restaurar
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.products.force-destroy', $product->id) }}" onsubmit="return confirm('¿Eliminar definitivamente este producto, sus imágenes, fichas técnicas y toda su información asociada? Esta acción no se puede deshacer.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1 rounded bg-rose-600 text-white hover:bg-rose-700 font-semibold cursor-pointer">
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <a href="{{ route('admin.products.show', $product) }}" class="px-2.5 py-1 rounded border border-slate-300 text-slate-700 hover:bg-slate-100 font-medium">
                                            Ver
                                        </a>
                                        @if(auth()->user()?->hasPermission('catalog.manage'))
                                            <a href="{{ route('admin.products.edit', $product) }}" class="px-2.5 py-1 rounded bg-slate-900 text-white hover:bg-slate-800 font-semibold">
                                                Editar
                                            </a>
                                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('¿Archivar este producto y enviarlo a la papelera?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1 rounded border border-slate-300 text-slate-600 hover:bg-slate-100 font-medium cursor-pointer">
                                                    Archivar
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.products.force-destroy', $product->id) }}" onsubmit="return confirm('¿Eliminar definitivamente este producto, sus imágenes, fichas técnicas y toda su información asociada? Esta acción no se puede deshacer.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1 rounded bg-rose-600 text-white hover:bg-rose-700 font-semibold cursor-pointer">
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                                No se encontraron productos con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                {{ $products->links() }}
            </div>
        @endif
    </div>
@endsection
