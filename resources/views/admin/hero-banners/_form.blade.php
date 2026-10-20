<div class="space-y-6 text-xs">
    {{-- Bloque 1: Imagen de Fondo y Medidas Recomendadas --}}
    <div class="bg-slate-50 rounded-xl border border-slate-200 p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-200">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">1. Imagen de Fondo del Banner (Desktop y Móvil)</h3>
                <p class="text-[11px] text-slate-600">
                    Si sube una imagen de fondo, esta se mostrará en el slider con efecto suave Ken Burns y un velo de contraste configurable. Si la deja vacía, se usará el fondo técnico animado con el color de acento elegido.
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-900 font-mono text-[11px] font-bold shrink-0">
                Recomendado: {{ \App\Models\HeroBanner::RECOMMENDED_DESKTOP_DIMENSIONS }}
            </span>
        </div>

        {{-- Cuadro resumen de medidas recomendadas --}}
        <div class="rounded-lg bg-blue-50/80 border border-blue-200 p-3.5 text-slate-700 grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <span class="block text-[10px] font-extrabold uppercase tracking-wider text-blue-900">Medida Recomendada Escritorio</span>
                <p class="font-mono font-bold text-slate-900 text-xs mt-0.5">1920 x 720 px (Proporción 8:3)</p>
                <p class="text-[11px] text-slate-600">Mínimo aceptable: 1600 x 600 px. Ideal en formato <strong>WEBP</strong> o JPG optimizado.</p>
            </div>
            <div>
                <span class="block text-[10px] font-extrabold uppercase tracking-wider text-blue-900">Medida Recomendada Móvil (Opcional)</span>
                <p class="font-mono font-bold text-slate-900 text-xs mt-0.5">1080 x 1350 px (Proporción 4:5)</p>
                <p class="text-[11px] text-slate-600">También admite 1080 x 1080 px. Si no se sube, el sistema adapta la imagen de escritorio automáticamente.</p>
            </div>
            <div>
                <span class="block text-[10px] font-extrabold uppercase tracking-wider text-blue-900">Peso y Zona Segura</span>
                <p class="font-bold text-slate-900 text-xs mt-0.5">Peso ideal &lt; 350 KB (Máx. 4 MB)</p>
                <p class="text-[11px] text-slate-600">Evite colocar textos pequeños pegados a los bordes; el titular se superpone al lado izquierdo.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-1">
            {{-- Imagen Desktop --}}
            <div class="bg-white rounded-lg border border-slate-200 p-4 space-y-3">
                <label class="block font-bold text-slate-800">
                    Imagen de Fondo Principal (Escritorio / Horizontal)
                    <span class="block text-[11px] font-normal text-slate-500 mt-0.5">
                        Medida recomendada: <strong>1920 x 720 px</strong> • Formatos: JPG, PNG, WEBP (máx. 4 MB)
                    </span>
                </label>

                @if($banner->background_image_path)
                    <div class="space-y-2">
                        <div class="relative h-32 w-full rounded-lg overflow-hidden border border-slate-200 bg-slate-900">
                            <img src="{{ $banner->backgroundImageUrl() }}" alt="Fondo actual" class="w-full h-full object-cover">
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded bg-slate-950/80 text-white text-[10px] font-semibold">
                                Fondo Escritorio Actual
                            </span>
                        </div>
                        <label class="inline-flex items-center gap-2 text-rose-700 font-semibold cursor-pointer">
                            <input type="checkbox" name="remove_background_image" value="1" class="rounded border-slate-300 text-rose-600">
                            <span>Quitar imagen de fondo actual y volver al fondo con gradiente técnico</span>
                        </label>
                    </div>
                @endif

                <input type="file"
                       name="background_image"
                       accept=".jpg,.jpeg,.png,.webp"
                       class="w-full text-slate-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
            </div>

            {{-- Imagen Mobile --}}
            <div class="bg-white rounded-lg border border-slate-200 p-4 space-y-3">
                <label class="block font-bold text-slate-800">
                    Imagen de Fondo para Celulares (Móvil / Vertical — Opcional)
                    <span class="block text-[11px] font-normal text-slate-500 mt-0.5">
                        Medida recomendada: <strong>1080 x 1350 px</strong> • Formatos: JPG, PNG, WEBP (máx. 4 MB)
                    </span>
                </label>

                @if($banner->mobile_background_image_path)
                    <div class="space-y-2">
                        <div class="relative h-32 w-40 rounded-lg overflow-hidden border border-slate-200 bg-slate-900">
                            <img src="{{ $banner->mobileBackgroundImageUrl() }}" alt="Fondo móvil actual" class="w-full h-full object-cover">
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded bg-slate-950/80 text-white text-[10px] font-semibold">
                                Móvil Actual
                            </span>
                        </div>
                        <label class="inline-flex items-center gap-2 text-rose-700 font-semibold cursor-pointer">
                            <input type="checkbox" name="remove_mobile_background_image" value="1" class="rounded border-slate-300 text-rose-600">
                            <span>Quitar imagen móvil actual</span>
                        </label>
                    </div>
                @endif

                <input type="file"
                       name="mobile_background_image"
                       accept=".jpg,.jpeg,.png,.webp"
                       class="w-full text-slate-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">
                    Opacidad del Velo Oscuro sobre la Imagen (0% a 95%)
                </label>
                <input type="number"
                       name="overlay_opacity"
                       value="{{ old('overlay_opacity', $banner->overlay_opacity ?? 75) }}"
                       min="0"
                       max="95"
                       class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-mono">
                <p class="text-[11px] text-slate-500 mt-1">
                    Recomendado: <strong>65% a 80%</strong> para que el texto blanco resalte con nitidez sobre la foto. Use <strong>20% - 40%</strong> si su banner ya es oscuro.
                </p>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Color de Acento y Brillo</label>
                <select name="accent" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
                    @foreach($accentOptions as $key => $label)
                        <option value="{{ $key }}" @selected(old('accent', $banner->accent) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Vitrina de Productos en Lado Derecho</label>
                <select name="product_source" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
                    @foreach($productSourceOptions as $key => $label)
                        <option value="{{ $key }}" @selected(old('product_source', $banner->product_source) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="inline-flex items-center gap-2 mt-2 cursor-pointer">
                    <input type="checkbox"
                           name="show_products_showcase"
                           value="1"
                           @checked(old('show_products_showcase', $banner->show_products_showcase ?? true))
                           class="rounded border-slate-300 text-amber-500">
                    <span class="text-[11px] font-semibold text-slate-700">Mostrar productos flotantes sobre el lado derecho del banner</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Bloque 2: Textos y Titulares de la Diapositiva --}}
    <div class="space-y-4">
        <h3 class="text-sm font-extrabold text-slate-900 border-b border-slate-200 pb-2">2. Contenido y Titulares del Banner</h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nombre Corto en Pestaña del Slider <span class="text-rose-600">*</span></label>
                <input type="text"
                       name="label"
                       value="{{ old('label', $banner->label) }}"
                       required
                       placeholder="Ej. Catálogo General"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Subtítulo Superior (Eyebrow)</label>
                <input type="text"
                       name="eyebrow"
                       value="{{ old('eyebrow', $banner->eyebrow) }}"
                       placeholder="Ej. Import Hnos San Luis S.A.C. • Mayorista y Minorista"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Etiqueta Técnica / Métrica (Badge)</label>
                <input type="text"
                       name="badge_metric"
                       value="{{ old('badge_metric', $banner->badge_metric) }}"
                       placeholder="Ej. +1,330 SKUs Verificados"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Título Principal (Parte 1) <span class="text-rose-600">*</span></label>
                <input type="text"
                       name="title"
                       value="{{ old('title', $banner->title) }}"
                       required
                       placeholder="Ej. Herrajes, Tiradores y"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Texto Destacado en Gradiente</label>
                <input type="text"
                       name="highlight"
                       value="{{ old('highlight', $banner->highlight) }}"
                       placeholder="Ej. Ferretería Profesional"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Cierre de Título (Opcional)</label>
                <input type="text"
                       name="suffix"
                       value="{{ old('suffix', $banner->suffix) }}"
                       placeholder="Ej. para tus Proyectos"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Descripción Comercial / Técnica</label>
            <textarea name="description"
                      rows="2"
                      placeholder="Resumen de líneas o propuesta de valor para esta diapositiva..."
                      class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('description', $banner->description) }}</textarea>
        </div>
    </div>

    {{-- Bloque 3: Botones de Acción (CTA) y Orden --}}
    <div class="space-y-4">
        <h3 class="text-sm font-extrabold text-slate-900 border-b border-slate-200 pb-2">3. Botones de Acción (Call to Action) y Publicación</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Texto Botón Principal</label>
                <input type="text"
                       name="primary_cta_label"
                       value="{{ old('primary_cta_label', $banner->primary_cta_label) }}"
                       placeholder="Explorar Catálogo"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Enlace Botón Principal</label>
                <input type="text"
                       name="primary_cta_url"
                       value="{{ old('primary_cta_url', $banner->primary_cta_url) }}"
                       placeholder="/catalogo"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Texto Botón Secundario</label>
                <input type="text"
                       name="secondary_cta_label"
                       value="{{ old('secondary_cta_label', $banner->secondary_cta_label) }}"
                       placeholder="Cotizar Lista"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Enlace Botón Secundario</label>
                <input type="text"
                       name="secondary_cta_url"
                       value="{{ old('secondary_cta_url', $banner->secondary_cta_url) }}"
                       placeholder="/cotizacion"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Orden de Aparición</label>
                <input type="number"
                       name="sort_order"
                       value="{{ old('sort_order', $banner->sort_order ?? 1) }}"
                       min="0"
                       class="w-40 rounded-lg border border-slate-300 px-3 py-2 font-mono">
            </div>
            <div class="flex items-center pt-5">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           @checked(old('is_active', $banner->is_active ?? true))
                           class="rounded border-slate-300 text-amber-500">
                    <span class="font-semibold text-slate-800">Publicar este banner en el slider de la página de Inicio</span>
                </label>
            </div>
        </div>
    </div>
</div>
