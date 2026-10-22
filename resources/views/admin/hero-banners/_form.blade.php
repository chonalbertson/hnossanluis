<div class="space-y-6 text-xs">
    {{-- Simulador en Tiempo Real del Banner --}}
    <div class="bg-slate-900 text-white rounded-2xl border border-slate-800 p-5 shadow-xl overflow-hidden relative">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-800">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                <h3 class="text-sm font-extrabold text-white tracking-wide">Simulador en Tiempo Real del Slider</h3>
                <span class="px-2 py-0.5 rounded-full bg-slate-800 text-[10px] text-slate-400 font-mono">Vista interactiva</span>
            </div>
            <div class="flex items-center gap-3 text-[11px] text-slate-400">
                <span>Velo: <strong id="preview-overlay-label" class="text-amber-400 font-mono">{{ old('overlay_opacity', $banner->overlay_opacity ?? 35) }}%</strong></span>
                <span>•</span>
                <span>Acento: <strong id="preview-accent-label" class="text-white font-mono uppercase">{{ old('accent', $banner->accent ?? 'red') }}</strong></span>
            </div>
        </div>

        {{-- Contenedor del Preview --}}
        <div class="mt-4 relative rounded-xl overflow-hidden border border-slate-700/80 bg-[#0B1220] min-h-[220px] sm:min-h-[260px] flex items-center">
            {{-- Imagen de Fondo del Preview --}}
            <div id="sim-bg-container" class="absolute inset-0 bg-cover bg-center transition-all duration-300"
                 style="background-image: url('{{ $banner->backgroundImageUrl() ?: '' }}');">
            </div>

            {{-- Velo de Oscuridad Dinámico --}}
            <div id="sim-overlay" class="absolute inset-0 bg-[#0B1220] transition-opacity duration-150"
                 style="opacity: {{ number_format(((int) old('overlay_opacity', $banner->overlay_opacity ?? 35)) / 100, 2, '.', '') }};">
            </div>

            {{-- Gradiente Lateral para Lectura --}}
            <div class="absolute inset-0 bg-gradient-to-r from-[#0B1220]/95 via-[#0B1220]/60 to-transparent"></div>

            {{-- Aura de color según acento --}}
            <div id="sim-aura" class="absolute -top-16 -right-16 w-64 h-64 rounded-full opacity-30 blur-3xl pointer-events-none transition-all duration-300 bg-red-600"></div>

            {{-- Contenido Textual del Preview --}}
            <div class="relative z-10 p-5 sm:p-7 max-w-xl space-y-3">
                <div class="flex items-center gap-2">
                    <span id="sim-eyebrow" class="px-2.5 py-0.5 rounded-full bg-white/10 border border-white/15 text-[10px] font-bold text-red-300 uppercase tracking-wider truncate max-w-[280px]">
                        {{ old('eyebrow', $banner->eyebrow) ?: 'Import Hnos San Luis • Mayorista' }}
                    </span>
                    <span id="sim-badge" class="px-2 py-0.5 rounded-full bg-amber-400/20 text-amber-300 font-mono text-[10px] font-extrabold">
                        {{ old('badge_metric', $banner->badge_metric) ?: '+1,330 SKUs' }}
                    </span>
                </div>

                <div class="text-lg sm:text-2xl font-black text-white leading-tight">
                    <span id="sim-title">{{ old('title', $banner->title) ?: 'Herrajes, Tiradores y' }}</span>
                    <span id="sim-highlight" class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-white to-red-300">
                        {{ old('highlight', $banner->highlight) ?: 'Ferretería Profesional' }}
                    </span>
                    <span id="sim-suffix">{{ old('suffix', $banner->suffix) ?: 'para tus Proyectos' }}</span>
                </div>

                <p id="sim-description" class="text-xs text-slate-300 line-clamp-2 leading-relaxed">
                    {{ old('description', $banner->description) ?: 'Abastecimiento técnico con cotizaciones por volumen (RUC) y por unidad para mueblería y carpintería.' }}
                </p>

                <div class="flex items-center gap-2.5 pt-1">
                    <span id="sim-btn-primary" class="px-4 py-2 rounded-lg bg-[#DC2626] text-white font-extrabold text-[11px] uppercase tracking-wider shadow-md">
                        {{ old('primary_cta_label', $banner->primary_cta_label) ?: 'Explorar Catálogo' }}
                    </span>
                    <span id="sim-btn-secondary" class="px-3.5 py-2 rounded-lg bg-white/10 border border-white/20 text-white font-bold text-[11px] uppercase tracking-wider">
                        {{ old('secondary_cta_label', $banner->secondary_cta_label) ?: 'Cotizar Lista' }}
                    </span>
                </div>
            </div>

            {{-- Indicador de fondo activo --}}
            <div class="absolute bottom-3 right-3 z-10">
                <span id="sim-bg-badge" class="px-2.5 py-1 rounded-md bg-slate-950/80 border border-slate-700 text-[10px] font-mono text-slate-300">
                    {{ $banner->background_image_path ? '✓ Fondo cargado' : 'Fondo por defecto (gradiente)' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Bloque 1: Imagen de Fondo y Medidas Recomendadas --}}
    <div class="bg-slate-50 rounded-xl border border-slate-200 p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-200">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">1. Imagen de Fondo del Banner (Desktop y Móvil)</h3>
                <p class="text-[11px] text-slate-600">
                    Suba una imagen de fondo de alta resolución. Se aplicará automáticamente un suave efecto Ken Burns con el velo de contraste oscuro que configure abajo.
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
                <p class="text-[11px] text-slate-600">Formato vertical para celulares. Si no se sube, se adapta el de escritorio.</p>
            </div>
            <div>
                <span class="block text-[10px] font-extrabold uppercase tracking-wider text-blue-900">Peso y Calidad</span>
                <p class="font-bold text-slate-900 text-xs mt-0.5">Peso ideal &lt; 350 KB (Máx. 4 MB)</p>
                <p class="text-[11px] text-slate-600">La composición deja la zona izquierda libre para textos y botones.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-1">
            {{-- Imagen Desktop --}}
            <div class="bg-white rounded-lg border border-slate-200 p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block font-bold text-slate-800">
                        Imagen de Fondo Principal (Escritorio / Horizontal)
                        <span class="block text-[11px] font-normal text-slate-500 mt-0.5">
                            Medida: <strong>1920 x 720 px</strong> • JPG, PNG, WEBP (máx. 4 MB)
                        </span>
                    </label>
                    <span id="dt-file-size" class="text-[10px] font-mono text-slate-500"></span>
                </div>

                {{-- Preview contenedor Desktop --}}
                <div class="relative h-36 w-full rounded-lg overflow-hidden border border-slate-200 bg-slate-900 group">
                    <img id="dt-preview-img"
                         src="{{ $banner->backgroundImageUrl() ?: 'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'1920\' height=\'720\' fill=\'%230b1220\'><text x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%23475569\' font-family=\'sans-serif\' font-size=\'32\'>Sin imagen seleccionada</text></svg>' }}"
                         alt="Fondo Escritorio"
                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded bg-slate-950/80 text-white text-[10px] font-semibold">
                        Escritorio (1920 x 720)
                    </span>
                </div>

                @if($banner->background_image_path)
                    <label class="inline-flex items-center gap-2 text-rose-700 font-semibold cursor-pointer">
                        <input type="checkbox" name="remove_background_image" value="1" class="rounded border-slate-300 text-rose-600">
                        <span>Quitar imagen de escritorio actual</span>
                    </label>
                @endif

                <input type="file"
                       id="dt-file-input"
                       name="background_image"
                       accept=".jpg,.jpeg,.png,.webp"
                       class="w-full text-slate-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
            </div>

            {{-- Imagen Mobile --}}
            <div class="bg-white rounded-lg border border-slate-200 p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block font-bold text-slate-800">
                        Imagen de Fondo para Celulares (Móvil / Vertical — Opcional)
                        <span class="block text-[11px] font-normal text-slate-500 mt-0.5">
                            Medida: <strong>1080 x 1350 px</strong> (4:5) • JPG, PNG, WEBP (máx. 4 MB)
                        </span>
                    </label>
                    <span id="mb-file-size" class="text-[10px] font-mono text-slate-500"></span>
                </div>

                {{-- Preview contenedor Mobile --}}
                <div class="relative h-36 w-28 mx-auto rounded-lg overflow-hidden border border-slate-200 bg-slate-900 group">
                    <img id="mb-preview-img"
                         src="{{ $banner->mobileBackgroundImageUrl() ?: 'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'1080\' height=\'1350\' fill=\'%230b1220\'><text x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%23475569\' font-family=\'sans-serif\' font-size=\'48\'>Móvil</text></svg>' }}"
                         alt="Fondo Móvil"
                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    <span class="absolute top-2 left-1.5 px-1.5 py-0.5 rounded bg-slate-950/80 text-white text-[9px] font-semibold">
                        Móvil 4:5
                    </span>
                </div>

                @if($banner->mobile_background_image_path)
                    <div class="text-center">
                        <label class="inline-flex items-center gap-2 text-rose-700 font-semibold cursor-pointer">
                            <input type="checkbox" name="remove_mobile_background_image" value="1" class="rounded border-slate-300 text-rose-600">
                            <span>Quitar imagen móvil</span>
                        </label>
                    </div>
                @endif

                <input type="file"
                       id="mb-file-input"
                       name="mobile_background_image"
                       accept=".jpg,.jpeg,.png,.webp"
                       class="w-full text-slate-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
            </div>
        </div>

        {{-- Controles de Opacidad, Acento y Vitrina --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
            <div class="bg-white rounded-lg border border-slate-200 p-3.5 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="font-bold text-slate-800">
                        Opacidad del Velo Oscuro
                    </label>
                    <span id="slider-val-badge" class="px-2 py-0.5 rounded bg-slate-900 text-amber-400 font-mono font-bold text-xs">
                        {{ old('overlay_opacity', $banner->overlay_opacity ?? 35) }}%
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <input type="range"
                           id="overlay-range"
                           min="0"
                           max="95"
                           step="5"
                           value="{{ old('overlay_opacity', $banner->overlay_opacity ?? 35) }}"
                           class="w-full accent-amber-500 cursor-pointer">
                    <input type="number"
                           id="overlay-number"
                           name="overlay_opacity"
                           value="{{ old('overlay_opacity', $banner->overlay_opacity ?? 35) }}"
                           min="0"
                           max="95"
                           class="w-16 rounded-md border border-slate-300 text-center font-mono font-bold text-xs py-1">
                </div>

                {{-- Presets rápidos --}}
                <div class="flex items-center gap-1.5 pt-1 text-[10px]">
                    <span class="text-slate-500">Presets:</span>
                    <button type="button" onclick="setOverlayPreset(40)" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 cursor-pointer">40% Claro</button>
                    <button type="button" onclick="setOverlayPreset(70)" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold cursor-pointer">70% Normal</button>
                    <button type="button" onclick="setOverlayPreset(85)" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 cursor-pointer">85% Alto</button>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 p-3.5 space-y-2">
                <label class="block font-bold text-slate-800">Color de Acento y Brillo</label>
                <select id="accent-select" name="accent" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-medium">
                    @foreach($accentOptions as $key => $label)
                        <option value="{{ $key }}" @selected(old('accent', $banner->accent) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500">Define el resplandor de fondo y el color del degradado en los titulares.</p>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 p-3.5 space-y-2">
                <label class="block font-bold text-slate-800">Vitrina Flotante (Lado Derecho)</label>
                <select name="product_source" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-medium">
                    @foreach($productSourceOptions as $key => $label)
                        <option value="{{ $key }}" @selected(old('product_source', $banner->product_source) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="inline-flex items-center gap-2 pt-1 cursor-pointer">
                    <input type="checkbox"
                           name="show_products_showcase"
                           value="1"
                           @checked(old('show_products_showcase', $banner->show_products_showcase ?? true))
                           class="rounded border-slate-300 text-amber-500">
                    <span class="text-[11px] font-semibold text-slate-700">Mostrar vitrina flotante con productos</span>
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
                       id="input-label"
                       name="label"
                       value="{{ old('label', $banner->label) }}"
                       required
                       placeholder="Ej. Catálogo General"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Subtítulo Superior (Eyebrow)</label>
                <input type="text"
                       id="input-eyebrow"
                       name="eyebrow"
                       value="{{ old('eyebrow', $banner->eyebrow) }}"
                       placeholder="Ej. Import Hnos San Luis S.A.C. • Mayorista y Minorista"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Etiqueta Técnica / Métrica (Badge)</label>
                <input type="text"
                       id="input-badge"
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
                       id="input-title"
                       name="title"
                       value="{{ old('title', $banner->title) }}"
                       required
                       placeholder="Ej. Herrajes, Tiradores y"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Texto Destacado en Gradiente</label>
                <input type="text"
                       id="input-highlight"
                       name="highlight"
                       value="{{ old('highlight', $banner->highlight) }}"
                       placeholder="Ej. Ferretería Profesional"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Cierre de Título (Opcional)</label>
                <input type="text"
                       id="input-suffix"
                       name="suffix"
                       value="{{ old('suffix', $banner->suffix) }}"
                       placeholder="Ej. para tus Proyectos"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Descripción Comercial / Técnica</label>
            <textarea id="input-description"
                      name="description"
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
                       id="input-primary-label"
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
                       id="input-secondary-label"
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const overlayRange = document.getElementById('overlay-range');
        const overlayNumber = document.getElementById('overlay-number');
        const overlayBadge = document.getElementById('slider-val-badge');
        const simOverlay = document.getElementById('sim-overlay');
        const simOverlayLabel = document.getElementById('preview-overlay-label');

        const accentSelect = document.getElementById('accent-select');
        const simAura = document.getElementById('sim-aura');
        const simAccentLabel = document.getElementById('preview-accent-label');
        const simHighlight = document.getElementById('sim-highlight');

        const simBgContainer = document.getElementById('sim-bg-container');
        const simBgBadge = document.getElementById('sim-bg-badge');
        const dtFileInput = document.getElementById('dt-file-input');
        const dtPreviewImg = document.getElementById('dt-preview-img');
        const dtFileSize = document.getElementById('dt-file-size');

        const mbFileInput = document.getElementById('mb-file-input');
        const mbPreviewImg = document.getElementById('mb-preview-img');
        const mbFileSize = document.getElementById('mb-file-size');

        const inputEyebrow = document.getElementById('input-eyebrow');
        const simEyebrow = document.getElementById('sim-eyebrow');

        const inputBadge = document.getElementById('input-badge');
        const simBadge = document.getElementById('sim-badge');

        const inputTitle = document.getElementById('input-title');
        const simTitle = document.getElementById('sim-title');

        const inputHighlight = document.getElementById('input-highlight');
        const inputSuffix = document.getElementById('input-suffix');
        const simSuffix = document.getElementById('sim-suffix');

        const inputDescription = document.getElementById('input-description');
        const simDescription = document.getElementById('sim-description');

        const inputPrimaryLabel = document.getElementById('input-primary-label');
        const simBtnPrimary = document.getElementById('sim-btn-primary');

        const inputSecondaryLabel = document.getElementById('input-secondary-label');
        const simBtnSecondary = document.getElementById('sim-btn-secondary');

        // Sincronizar Opacidad
        function updateOpacity(val) {
            val = Math.max(0, Math.min(95, parseInt(val) || 0));
            overlayRange.value = val;
            overlayNumber.value = val;
            overlayBadge.textContent = val + '%';
            if (simOverlayLabel) simOverlayLabel.textContent = val + '%';
            if (simOverlay) simOverlay.style.opacity = (val / 100).toFixed(2);
        }

        window.setOverlayPreset = function(val) {
            updateOpacity(val);
        };

        if (overlayRange && overlayNumber) {
            overlayRange.addEventListener('input', (e) => updateOpacity(e.target.value));
            overlayNumber.addEventListener('input', (e) => updateOpacity(e.target.value));
        }

        // Acento dinámico
        const accentStyles = {
            red: { bg: 'bg-red-600', grad: 'from-amber-300 via-white to-red-300' },
            blue: { bg: 'bg-blue-600', grad: 'from-sky-300 via-white to-blue-200' },
            amber: { bg: 'bg-amber-500', grad: 'from-amber-300 via-yellow-100 to-amber-200' },
            emerald: { bg: 'bg-emerald-500', grad: 'from-emerald-300 via-white to-teal-200' }
        };

        function updateAccent(key) {
            const st = accentStyles[key] || accentStyles.red;
            if (simAura) {
                simAura.className = `absolute -top-16 -right-16 w-64 h-64 rounded-full opacity-30 blur-3xl pointer-events-none transition-all duration-300 ${st.bg}`;
            }
            if (simHighlight) {
                simHighlight.className = `text-transparent bg-clip-text bg-gradient-to-r ${st.grad}`;
            }
            if (simAccentLabel) {
                simAccentLabel.textContent = key.toUpperCase();
            }
        }

        if (accentSelect) {
            accentSelect.addEventListener('change', (e) => updateAccent(e.target.value));
            updateAccent(accentSelect.value);
        }

        // Preview de imagen Desktop en vivo
        if (dtFileInput) {
            dtFileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        const url = evt.target.result;
                        if (dtPreviewImg) dtPreviewImg.src = url;
                        if (simBgContainer) simBgContainer.style.backgroundImage = `url('${url}')`;
                        if (simBgBadge) simBgBadge.textContent = '✓ Nueva imagen escritorio seleccionada';
                    };
                    reader.readAsDataURL(file);
                    if (dtFileSize) dtFileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
                }
            });
        }

        // Preview de imagen Mobile en vivo
        if (mbFileInput) {
            mbFileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        if (mbPreviewImg) mbPreviewImg.src = evt.target.result;
                    };
                    reader.readAsDataURL(file);
                    if (mbFileSize) mbFileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
                }
            });
        }

        // Sincronizar textos al escribir
        function syncText(input, target, fallback) {
            if (input && target) {
                input.addEventListener('input', () => {
                    target.textContent = input.value.trim() || fallback;
                });
            }
        }

        syncText(inputEyebrow, simEyebrow, 'Import Hnos San Luis');
        syncText(inputBadge, simBadge, '+1,330 SKUs');
        syncText(inputTitle, simTitle, 'Herrajes, Tiradores y ');
        syncText(inputHighlight, simHighlight, 'Ferretería Profesional');
        syncText(inputSuffix, simSuffix, ' para tus Proyectos');
        syncText(inputDescription, simDescription, 'Abastecimiento técnico con cotizaciones por volumen.');
        syncText(inputPrimaryLabel, simBtnPrimary, 'Explorar Catálogo');
        syncText(inputSecondaryLabel, simBtnSecondary, 'Cotizar Lista');
    });
</script>
