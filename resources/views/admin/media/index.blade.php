@extends('layouts.admin')

@section('title', 'Imágenes y Fichas Técnicas')
@section('header', '5. Gestor de Imágenes y Fichas Técnicas PDF')
@section('subheader', 'Repositorio centralizado de fotografías de productos y documentación técnica PDF con validación de seguridad.')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upload Product Image -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
            <h2 class="text-sm font-bold text-slate-900 mb-3">Subir Imagen de Producto (JPG, PNG, WEBP máx. 4MB)</h2>
            <form method="POST" action="{{ route('admin.media.store-image') }}" enctype="multipart/form-data" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Producto Asociado (SKU) <span class="text-rose-600">*</span></label>
                    <select name="product_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                        <option value="">Seleccione un producto...</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->sku }} — {{ $prod->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Archivo de Imagen <span class="text-rose-600">*</span></label>
                        <input type="file" name="image" required accept=".jpg,.jpeg,.png,.webp" class="w-full text-slate-600">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Texto Alternativo (Alt SEO)</label>
                        <input type="text" name="alt_text" placeholder="Descripción de la imagen" class="w-full rounded-lg border border-slate-300 px-3 py-1.5">
                    </div>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_primary" value="1" class="rounded border-slate-300 text-amber-500">
                        <span class="font-medium text-slate-700">Establecer como imagen principal</span>
                    </label>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800 cursor-pointer">Subir Imagen</button>
                </div>
            </form>
        </div>

        <!-- Upload Technical PDF -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
            <h2 class="text-sm font-bold text-slate-900 mb-3">Subir Ficha Técnica o Manual PDF (máx. 10MB)</h2>
            <form method="POST" action="{{ route('admin.media.store-document') }}" enctype="multipart/form-data" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Producto Asociado (SKU) <span class="text-rose-600">*</span></label>
                    <select name="product_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white">
                        <option value="">Seleccione un producto...</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->sku }} — {{ $prod->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Título del Documento <span class="text-rose-600">*</span></label>
                        <input type="text" name="title" required placeholder="Ej. Ficha Técnica Dimensional PDF" class="w-full rounded-lg border border-slate-300 px-3 py-1.5">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Archivo PDF <span class="text-rose-600">*</span></label>
                        <input type="file" name="document" required accept=".pdf" class="w-full text-slate-600">
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 cursor-pointer">Subir Ficha PDF</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Images Gallery -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
        <h3 class="text-sm font-bold text-slate-900 mb-4">Imágenes Registradas ({{ $images->total() }})</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
            @forelse($images as $img)
                <div class="border border-slate-200 rounded-lg p-2.5 bg-slate-50 flex flex-col justify-between text-xs">
                    <div>
                        <img src="{{ $img->url() }}" alt="{{ $img->alt_text }}" class="w-full h-28 object-cover rounded border border-slate-200 bg-white">
                        <div class="font-mono font-bold text-slate-800 mt-2 truncate">{{ $img->product?->sku ?? 'Sin producto' }}</div>
                        <div class="text-[11px] text-slate-500 truncate">{{ $img->product?->name }}</div>
                    </div>
                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-200">
                        <span class="text-[10px] {{ $img->is_primary ? 'font-bold text-emerald-700' : 'text-slate-400' }}">
                            {{ $img->is_primary ? 'Principal' : 'Galería' }}
                        </span>
                        <form method="POST" action="{{ route('admin.media.destroy-image', $img) }}" onsubmit="return confirm('¿Eliminar imagen?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-600 hover:underline font-semibold text-[11px] cursor-pointer">Eliminar</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-6 text-center text-xs text-slate-500">No hay imágenes cargadas aún.</div>
            @endforelse
        </div>
    </div>

    <!-- Documents Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200">
            <h3 class="text-sm font-bold text-slate-900">Fichas Técnicas y Documentos PDF ({{ $documents->total() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Título del Documento</th>
                        <th class="px-4 py-3 text-left">Producto (SKU)</th>
                        <th class="px-4 py-3 text-center">Tamaño</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($documents as $doc)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $doc->title }}</td>
                            <td class="px-4 py-3">
                                <span class="font-mono font-bold">{{ $doc->product?->sku }}</span> — {{ $doc->product?->name }}
                            </td>
                            <td class="px-4 py-3 text-center text-slate-500">{{ $doc->file_size ? round($doc->file_size / 1024, 1) . ' KB' : 'PDF' }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.media.destroy-document', $doc) }}" class="inline" onsubmit="return confirm('¿Eliminar ficha PDF?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:underline font-semibold cursor-pointer">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">No hay fichas técnicas PDF cargadas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
