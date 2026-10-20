@extends('layouts.admin')

@section('title', 'Editar Producto ' . $product->sku)
@section('header', 'Editar Producto: ' . $product->sku)
@section('subheader', $product->name)

@section('content')
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.products._form')
    </form>

    @if($product->images->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs mt-6">
            <h3 class="text-sm font-bold text-slate-900 mb-3">Galería de Imágenes del Producto</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($product->images as $img)
                    <div class="border border-slate-200 rounded-lg p-2 bg-slate-50 flex flex-col justify-between">
                        <img src="{{ $img->url() }}" alt="{{ $img->alt_text }}" class="w-full h-28 object-cover rounded">
                        <div class="mt-2 flex items-center justify-between gap-1 text-[11px]">
                            @if($img->is_primary)
                                <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded">Principal</span>
                            @else
                                <form method="POST" action="{{ route('admin.products.images.primary', [$product, $img]) }}">
                                    @csrf
                                    <button type="submit" class="text-blue-600 hover:underline font-medium cursor-pointer">Hacer principal</button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.products.images.destroy', [$product, $img]) }}" onsubmit="return confirm('¿Eliminar esta imagen?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:underline font-semibold cursor-pointer">Eliminar</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(auth()->user()?->hasPermission('catalog.manage'))
        <div class="bg-rose-50/60 rounded-xl border border-rose-200 p-5 shadow-2xs mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-rose-900">Eliminar Producto Definitivamente</h3>
                <p class="text-xs text-rose-700 mt-0.5">
                    Al eliminar este producto se borrarán permanentemente su registro, todas sus imágenes, miniaturas, fichas técnicas PDF y especificaciones asociadas.
                </p>
            </div>
            <form method="POST" action="{{ route('admin.products.force-destroy', $product->id) }}" onsubmit="return confirm('¿Eliminar definitivamente este producto, sus imágenes, fichas técnicas y toda su información asociada? Esta acción no se puede deshacer.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-600 text-white hover:bg-rose-700 font-bold text-xs shadow-2xs cursor-pointer whitespace-nowrap">
                    Eliminar Producto e Imágenes
                </button>
            </form>
        </div>
    @endif
@endsection
