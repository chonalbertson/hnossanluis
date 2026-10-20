@php
    $existingSpecs = old('specs', $product->exists ? $product->specifications->map(fn($s) => [
        'spec_group' => $s->spec_group,
        'spec_key' => $s->spec_key,
        'spec_value' => $s->spec_value,
        'unit' => $s->unit,
    ])->toArray() : [
        ['spec_group' => 'Dimensiones', 'spec_key' => '', 'spec_value' => '', 'unit' => ''],
    ]);
    if (empty($existingSpecs)) {
        $existingSpecs = [['spec_group' => 'General', 'spec_key' => '', 'spec_value' => '', 'unit' => '']];
    }

    $existingVariants = old('variants', ($product->exists && $product->relationLoaded('variants')) ? $product->variants->map(fn($v) => [
        'id' => $v->id,
        'variant_label' => $v->variant_label,
        'sku' => $v->sku,
        'name' => $v->name,
        'dimensions' => $v->dimensions,
        'material' => $v->material,
        'sale_unit' => $v->sale_unit,
        'box_qty' => $v->box_qty,
        'carton_qty' => $v->carton_qty,
        'price' => $v->price,
        'availability_status' => $v->availability_status,
    ])->toArray() : []);

    $initialProductType = old('product_type', !empty($existingVariants) ? 'variable' : 'simple');
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Main Technical Data, Variants & Dynamic Specs -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Identificación Técnica -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Identificación Técnica del Producto</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Código SKU <span class="text-rose-600">*</span></label>
                    <input type="text" id="product-base-sku" name="sku" value="{{ old('sku', $product->sku) }}" required placeholder="Ej. SL-BIS-35SC-IN"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs font-mono uppercase focus:border-amber-500 focus:outline-none">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre Técnico / Comercial <span class="text-rose-600">*</span></label>
                    <input type="text" id="product-base-name" name="name" value="{{ old('name', $product->name) }}" required placeholder="Ej. Bisagra Cazoleta 35mm Cierre Suave Acero Inox"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs focus:border-amber-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Categoría Principal <span class="text-rose-600">*</span></label>
                    <select name="category_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs bg-white">
                        <option value="">Seleccione categoría...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((int) old('category_id', $product->category_id) === $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Subcategoría</label>
                    <select name="subcategory_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs bg-white">
                        <option value="">Ninguna / General</option>
                        @foreach($categories as $cat)
                            @foreach($cat->children as $sub)
                                <option value="{{ $sub->id }}" @selected((int) old('subcategory_id', $product->subcategory_id) === $sub->id)>
                                    {{ $cat->name }} &rarr; {{ $sub->name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Marca</label>
                    <select name="brand_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs bg-white">
                        <option value="">Sin marca específica</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" @selected((int) old('brand_id', $product->brand_id) === $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Medidas / Dimensiones</label>
                    <input type="text" id="product-base-dimensions" name="dimensions" value="{{ old('dimensions', $product->dimensions) }}" placeholder="Ej. Ø 35mm / Apertura 110°"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Material / Acabado</label>
                    <input type="text" id="product-base-material" name="material" value="{{ old('material', $product->material) }}" placeholder="Ej. Acero Inoxidable SUS304"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Unidad de Venta <span class="text-rose-600">*</span></label>
                    <input type="text" id="product-base-sale-unit" name="sale_unit" value="{{ old('sale_unit', $product->sale_unit ?: 'Unidad') }}" required placeholder="Unidad, Par, Juego, Millar, Caja..."
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Resumen Técnico Breve</label>
                <textarea name="short_description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">{{ old('short_description', $product->short_description) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Descripción Técnica Detallada y Aplicaciones</label>
                <textarea name="detailed_description" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">{{ old('detailed_description', $product->detailed_description) }}</textarea>
            </div>
        </div>

        <!-- Tipo de Producto y Variantes (Producto Simple vs Producto Variable) -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-bold text-slate-900">Modalidad de Producto y Variantes</h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-800 border border-blue-200">
                            Simple / Variable
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Elige <strong>Producto Variable</strong> para registrar múltiples medidas, calibres, acabados o empaques bajo una misma ficha técnica.
                    </p>
                </div>

                <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200 self-start sm:self-auto">
                    <label class="cursor-pointer">
                        <input type="radio" name="product_type" value="simple" class="sr-only peer"
                               @checked($initialProductType === 'simple')
                               onchange="toggleProductTypeMode('simple')">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 peer-checked:bg-white peer-checked:text-slate-900 peer-checked:shadow-2xs transition">
                            Producto Simple
                        </span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="product_type" value="variable" class="sr-only peer"
                               @checked($initialProductType === 'variable')
                               onchange="toggleProductTypeMode('variable')">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 peer-checked:bg-slate-900 peer-checked:text-amber-400 peer-checked:shadow-2xs transition">
                            Producto Variable (Medidas / SKUs)
                        </span>
                    </label>
                </div>
            </div>

            <div id="simple-product-notice" class="{{ $initialProductType === 'variable' ? 'hidden' : '' }} rounded-lg bg-slate-50 border border-slate-200 px-4 py-3 text-xs text-slate-600 flex items-center justify-between gap-3">
                <span>Este producto se comercializa con un <strong>único código SKU</strong> y una sola presentación.</span>
                <button type="button" onclick="activateVariableProductMode()" class="text-amber-700 hover:text-amber-900 font-bold underline shrink-0 cursor-pointer">
                    + Convertir en Producto Variable
                </button>
            </div>

            <div id="variable-product-panel" class="{{ $initialProductType === 'variable' ? '' : 'hidden' }} space-y-4">
                <!-- Generador Rápido por Lote de Medidas / Acabados -->
                <div class="rounded-xl bg-amber-50/70 border border-amber-200 p-3.5 space-y-2.5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-xs font-bold text-slate-900">Generador Rápido de Variantes por Medidas o Acabados</h3>
                            <p class="text-[11px] text-slate-600">Escribe las medidas separadas por coma (ej. <code class="font-mono bg-white px-1 py-0.5 rounded border border-amber-200">300mm, 350mm, 400mm, 450mm, 500mm</code> o <code class="font-mono bg-white px-1 py-0.5 rounded border border-amber-200">Recta (0°), Semicurva (8°), Curva (16°)</code>).</p>
                        </div>
                        <button type="button" onclick="addVariantRow()" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white hover:bg-slate-800 text-xs font-semibold shrink-0 cursor-pointer">
                            + Añadir Variante Manual
                        </button>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" id="batch-variant-labels" placeholder="Ej. 300mm (12&quot;), 350mm (14&quot;), 400mm (16&quot;), 450mm (18&quot;), 500mm (20&quot;)"
                               class="flex-1 rounded-lg border border-amber-300 bg-white px-3 py-1.5 text-xs focus:border-amber-500 focus:outline-none">
                        <button type="button" onclick="generateBatchVariants()"
                                class="px-3.5 py-1.5 rounded-lg bg-amber-500 text-slate-950 hover:bg-amber-400 font-bold text-xs shrink-0 cursor-pointer">
                            Generar Variantes Automáticamente
                        </button>
                    </div>
                </div>

                <!-- Lista de Variantes Registradas -->
                <div id="variants-container" class="space-y-3">
                    @foreach($existingVariants as $vIdx => $variant)
                        <div class="variant-row rounded-xl border border-slate-200 bg-slate-50 p-3.5 space-y-2.5">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-end">
                                <div class="sm:col-span-3">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Etiqueta / Medida <span class="text-rose-600">*</span></label>
                                    <input type="text" name="variants[{{ $vIdx }}][variant_label]" value="{{ $variant['variant_label'] ?? '' }}" placeholder="Ej. 450 mm (18&quot;)"
                                           class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-900">
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-0.5">SKU de Variante <span class="text-rose-600">*</span></label>
                                    <input type="text" name="variants[{{ $vIdx }}][sku]" value="{{ $variant['sku'] ?? '' }}" placeholder="Ej. SL-COR-4545-ZN"
                                           class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-mono uppercase">
                                </div>
                                <div class="sm:col-span-4">
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Nombre Específico (opcional)</label>
                                    <input type="text" name="variants[{{ $vIdx }}][name]" value="{{ $variant['name'] ?? '' }}" placeholder="Nombre completo de la variante"
                                           class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
                                </div>
                                <div class="sm:col-span-2 flex items-center justify-between sm:justify-end gap-2">
                                    <select name="variants[{{ $vIdx }}][availability_status]" class="rounded border border-slate-300 bg-white px-2 py-1.5 text-[11px] font-semibold">
                                        <option value="available" @selected(($variant['availability_status'] ?? 'available') === 'available')>Disponible</option>
                                        <option value="on_request" @selected(($variant['availability_status'] ?? '') === 'on_request')>Bajo pedido</option>
                                        <option value="out_of_stock" @selected(($variant['availability_status'] ?? '') === 'out_of_stock')>Sin stock</option>
                                    </select>
                                    <button type="button" onclick="this.closest('.variant-row').remove()" class="p-1.5 rounded text-rose-600 hover:bg-rose-50 font-bold text-xs cursor-pointer" title="Eliminar variante">&times;</button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-6 gap-2.5 pt-1 border-t border-slate-200/80">
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-500">Dimensiones</label>
                                    <input type="text" name="variants[{{ $vIdx }}][dimensions]" value="{{ $variant['dimensions'] ?? '' }}" placeholder="Ej. 45 x 450 mm"
                                           class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-500">Material / Acabado</label>
                                    <input type="text" name="variants[{{ $vIdx }}][material]" value="{{ $variant['material'] ?? '' }}" placeholder="Ej. Zincado / Negro"
                                           class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-500">Unidad Venta</label>
                                    <input type="text" name="variants[{{ $vIdx }}][sale_unit]" value="{{ $variant['sale_unit'] ?? '' }}" placeholder="Par / Unidad"
                                           class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-500">Unid. x Caja</label>
                                    <input type="number" min="1" name="variants[{{ $vIdx }}][box_qty]" value="{{ $variant['box_qty'] ?? '' }}" placeholder="Ej. 15"
                                           class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs tabular-nums">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-500">Unid. x Cajón Master</label>
                                    <input type="number" min="1" name="variants[{{ $vIdx }}][carton_qty]" value="{{ $variant['carton_qty'] ?? '' }}" placeholder="Ej. 150"
                                           class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs tabular-nums">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-500">Precio Ref.</label>
                                    <input type="number" step="0.01" min="0" name="variants[{{ $vIdx }}][price]" value="{{ $variant['price'] ?? '' }}" placeholder="Opcional"
                                           class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs tabular-nums">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Especificaciones Técnicas Dinámicas -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Especificaciones Técnicas Estructuradas</h2>
                    <p class="text-xs text-slate-500">Atributos filtrables en el catálogo público (ej. Capacidad de Carga, Diámetro, Ciclos).</p>
                </div>
                <button type="button" onclick="addSpecRow()" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white hover:bg-slate-800 text-xs font-semibold cursor-pointer">
                    + Añadir Atributo
                </button>
            </div>

            <div id="specs-container" class="space-y-2.5">
                @foreach($existingSpecs as $idx => $spec)
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center spec-row bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                        <div class="sm:col-span-3">
                            <input type="text" name="specs[{{ $idx }}][spec_group]" value="{{ $spec['spec_group'] ?? 'General' }}" placeholder="Grupo (ej. Dimensiones)"
                                   class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
                        </div>
                        <div class="sm:col-span-4">
                            <input type="text" name="specs[{{ $idx }}][spec_key]" value="{{ $spec['spec_key'] ?? '' }}" placeholder="Atributo (ej. Capacidad de Carga)"
                                   class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold">
                        </div>
                        <div class="sm:col-span-3">
                            <input type="text" name="specs[{{ $idx }}][spec_value]" value="{{ $spec['spec_value'] ?? '' }}" placeholder="Valor (ej. 45 kg)"
                                   class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
                        </div>
                        <div class="sm:col-span-1">
                            <input type="text" name="specs[{{ $idx }}][unit]" value="{{ $spec['unit'] ?? '' }}" placeholder="Und."
                                   class="w-full rounded border border-slate-300 bg-white px-2 py-1.5 text-xs">
                        </div>
                        <div class="sm:col-span-1 text-right">
                            <button type="button" onclick="this.closest('.spec-row').remove()" class="text-rose-600 hover:text-rose-800 text-xs font-bold px-2 py-1 cursor-pointer">
                                &times;
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- SEO Metadata -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Metadatos SEO y URL</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Slug URL (opcional, se autogenera)</label>
                    <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" placeholder="bisagra-cazoleta-35mm-sl-bis-35sc"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Meta Título SEO</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Meta Descripción SEO</label>
                <textarea name="meta_description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">{{ old('meta_description', $product->meta_description) }}</textarea>
            </div>
        </div>
    </div>

    <!-- Right Column: Pricing, Availability, Status & Media -->
    <div class="space-y-6">
        <!-- Publicación y Disponibilidad -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Estado y Disponibilidad</h2>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Estado de Publicación</label>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs bg-white font-semibold">
                    <option value="published" @selected(old('status', $product->status) === 'published')>Publicado en Catálogo</option>
                    <option value="draft" @selected(old('status', $product->status) === 'draft')>Borrador (Interno)</option>
                    <option value="archived" @selected(old('status', $product->status) === 'archived')>Archivado</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Disponibilidad Comercial</label>
                <select name="availability_status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs bg-white">
                    <option value="available" @selected(old('availability_status', $product->availability_status) === 'available')>Disponible</option>
                    <option value="on_request" @selected(old('availability_status', $product->availability_status) === 'on_request')>Bajo consulta / Por proyecto</option>
                    <option value="out_of_stock" @selected(old('availability_status', $product->availability_status) === 'out_of_stock')>Sin stock temporal</option>
                    <option value="unknown" @selected(old('availability_status', $product->availability_status) === 'unknown')>Consultar disponibilidad</option>
                </select>
            </div>

            <label class="flex items-center gap-2.5 pt-1 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))
                       class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                <span class="text-xs font-semibold text-slate-700">Destacar en portada e inicio</span>
            </label>
        </div>

        <!-- Política de Precio -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Precio Referencial y Visibilidad en Catálogo</h2>
            <p class="text-[11px] text-slate-500">Por defecto el catálogo opera bajo modalidad de cotización (precio oculto). Solo se mostrará el precio en la web si selecciona explícitamente <strong>"Habilitar precio público para este producto"</strong>.</p>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Moneda</label>
                    <select name="currency" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs bg-white">
                        <option value="PEN" @selected(old('currency', $product->currency) === 'PEN')>PEN (S/)</option>
                        <option value="USD" @selected(old('currency', $product->currency) === 'USD')>USD ($)</option>
                        <option value="EUR" @selected(old('currency', $product->currency) === 'EUR')>EUR (€)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Precio Unitario</label>
                    <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" placeholder="Opcional"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Visibilidad de Precio en Web</label>
                <select name="price_visibility" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs bg-white font-semibold">
                    <option value="hide" @selected(old('price_visibility', $product->price_visibility ?: 'hide') === 'hide')>🔒 Ocultar precio en web — Solo bajo cotización (Predeterminado)</option>
                    <option value="show" @selected(old('price_visibility', $product->price_visibility) === 'show')>👁️ Habilitar precio público para este producto</option>
                    <option value="inherit" @selected(old('price_visibility', $product->price_visibility) === 'inherit')>⚙️ Heredar modo global</option>
                </select>
            </div>
        </div>

        <!-- Imágenes y Ficha Técnica PDF -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Multimedia y Ficha PDF</h2>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Subir Nueva Imagen Principal (JPG, PNG, WEBP máx. 4MB)</label>
                <input type="file" name="primary_image" accept=".jpg,.jpeg,.png,.webp"
                       class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-800 hover:file:bg-slate-200">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Imágenes de Galería Adicionales</label>
                <input type="file" name="gallery_images[]" multiple accept=".jpg,.jpeg,.png,.webp"
                       class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-800 hover:file:bg-slate-200">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Ficha Técnica en PDF (máx. 10MB)</label>
                <input type="file" name="technical_pdf" accept=".pdf"
                       class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-900 hover:file:bg-amber-100">
                @if($product->technical_pdf_path)
                    <p class="text-[11px] text-emerald-700 font-medium mt-1">
                        ✓ Ficha PDF actual cargada ({{ basename($product->technical_pdf_path) }})
                    </p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-amber-500 text-slate-950 hover:bg-amber-400 font-bold text-xs shadow-sm transition cursor-pointer">
                {{ $product->exists ? 'Guardar Cambios del Producto' : 'Registrar Producto en Catálogo' }}
            </button>
            <a href="{{ route('admin.products.index') }}" class="py-2.5 px-4 rounded-xl border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 font-semibold text-xs">
                Cancelar
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let specIndex = {{ count($existingSpecs) + 1 }};
    let variantIndex = {{ count($existingVariants) + 1 }};

    function addSpecRow() {
        const container = document.getElementById('specs-container');
        const row = document.createElement('div');
        row.className = 'grid grid-cols-1 sm:grid-cols-12 gap-2 items-center spec-row bg-slate-50 p-2.5 rounded-lg border border-slate-200';
        row.innerHTML = `
            <div class="sm:col-span-3">
                <input type="text" name="specs[${specIndex}][spec_group]" value="Especificaciones" placeholder="Grupo" class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
            </div>
            <div class="sm:col-span-4">
                <input type="text" name="specs[${specIndex}][spec_key]" placeholder="Atributo (ej. Espesor)" class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold">
            </div>
            <div class="sm:col-span-3">
                <input type="text" name="specs[${specIndex}][spec_value]" placeholder="Valor (ej. 1.5 mm)" class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
            </div>
            <div class="sm:col-span-1">
                <input type="text" name="specs[${specIndex}][unit]" placeholder="Und." class="w-full rounded border border-slate-300 bg-white px-2 py-1.5 text-xs">
            </div>
            <div class="sm:col-span-1 text-right">
                <button type="button" onclick="this.closest('.spec-row').remove()" class="text-rose-600 hover:text-rose-800 text-xs font-bold px-2 py-1 cursor-pointer">&times;</button>
            </div>
        `;
        container.appendChild(row);
        specIndex++;
    }

    function toggleProductTypeMode(mode) {
        const simpleNotice = document.getElementById('simple-product-notice');
        const variablePanel = document.getElementById('variable-product-panel');
        if (mode === 'variable') {
            simpleNotice?.classList.add('hidden');
            variablePanel?.classList.remove('hidden');
            const container = document.getElementById('variants-container');
            if (container && container.children.length === 0) {
                addVariantRow();
            }
        } else {
            simpleNotice?.classList.remove('hidden');
            variablePanel?.classList.add('hidden');
        }
    }

    function activateVariableProductMode() {
        const variableRadio = document.querySelector('input[name="product_type"][value="variable"]');
        if (variableRadio) {
            variableRadio.checked = true;
        }
        toggleProductTypeMode('variable');
    }

    function addVariantRow(prefill = {}) {
        const container = document.getElementById('variants-container');
        if (!container) {
            return;
        }
        const baseMaterial = document.getElementById('product-base-material')?.value || '';
        const baseSaleUnit = document.getElementById('product-base-sale-unit')?.value || 'Unidad';

        const label = prefill.variant_label || '';
        const sku = prefill.sku || '';
        const name = prefill.name || '';
        const dimensions = prefill.dimensions || label;
        const material = prefill.material || baseMaterial;
        const saleUnit = prefill.sale_unit || baseSaleUnit;

        const row = document.createElement('div');
        row.className = 'variant-row rounded-xl border border-slate-200 bg-slate-50 p-3.5 space-y-2.5';
        row.innerHTML = `
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-end">
                <div class="sm:col-span-3">
                    <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Etiqueta / Medida <span class="text-rose-600">*</span></label>
                    <input type="text" name="variants[${variantIndex}][variant_label]" value="${label}" placeholder="Ej. 450 mm (18&quot;)"
                           class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-900">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-[11px] font-bold text-slate-700 mb-0.5">SKU de Variante <span class="text-rose-600">*</span></label>
                    <input type="text" name="variants[${variantIndex}][sku]" value="${sku}" placeholder="Ej. SL-COR-4545-ZN"
                           class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-mono uppercase">
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Nombre Específico (opcional)</label>
                    <input type="text" name="variants[${variantIndex}][name]" value="${name}" placeholder="Nombre completo de la variante"
                           class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
                </div>
                <div class="sm:col-span-2 flex items-center justify-between sm:justify-end gap-2">
                    <select name="variants[${variantIndex}][availability_status]" class="rounded border border-slate-300 bg-white px-2 py-1.5 text-[11px] font-semibold">
                        <option value="available">Disponible</option>
                        <option value="on_request">Bajo pedido</option>
                        <option value="out_of_stock">Sin stock</option>
                    </select>
                    <button type="button" onclick="this.closest('.variant-row').remove()" class="p-1.5 rounded text-rose-600 hover:bg-rose-50 font-bold text-xs cursor-pointer" title="Eliminar variante">&times;</button>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-6 gap-2.5 pt-1 border-t border-slate-200/80">
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500">Dimensiones</label>
                    <input type="text" name="variants[${variantIndex}][dimensions]" value="${dimensions}" placeholder="Ej. 45 x 450 mm"
                           class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500">Material / Acabado</label>
                    <input type="text" name="variants[${variantIndex}][material]" value="${material}" placeholder="Ej. Zincado / Negro"
                           class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500">Unidad Venta</label>
                    <input type="text" name="variants[${variantIndex}][sale_unit]" value="${saleUnit}" placeholder="Par / Unidad"
                           class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500">Unid. x Caja</label>
                    <input type="number" min="1" name="variants[${variantIndex}][box_qty]" value="" placeholder="Ej. 15"
                           class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs tabular-nums">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500">Unid. x Cajón Master</label>
                    <input type="number" min="1" name="variants[${variantIndex}][carton_qty]" value="" placeholder="Ej. 150"
                           class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs tabular-nums">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500">Precio Ref.</label>
                    <input type="number" step="0.01" min="0" name="variants[${variantIndex}][price]" value="" placeholder="Opcional"
                           class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs tabular-nums">
                </div>
            </div>
        `;
        container.appendChild(row);
        variantIndex++;
    }

    function generateBatchVariants() {
        const input = document.getElementById('batch-variant-labels');
        if (!input || !input.value.trim()) {
            return;
        }
        const baseSku = (document.getElementById('product-base-sku')?.value || 'SL-VAR').trim().toUpperCase();
        const baseName = (document.getElementById('product-base-name')?.value || '').trim();
        const items = input.value.split(',').map(v => v.trim()).filter(Boolean);

        const container = document.getElementById('variants-container');
        // Remove empty initial placeholder row if present
        if (container && container.children.length === 1) {
            const firstLabel = container.querySelector('input[name*="[variant_label]"]')?.value || '';
            const firstSku = container.querySelector('input[name*="[sku]"]')?.value || '';
            if (!firstLabel.trim() && !firstSku.trim()) {
                container.innerHTML = '';
            }
        }

        items.forEach((label) => {
            const slugToken = label.toUpperCase().replace(/[^A-Z0-9]+/g, '').slice(0, 6);
            addVariantRow({
                variant_label: label,
                sku: `${baseSku}-${slugToken}`,
                name: baseName ? `${baseName} — ${label}` : label,
                dimensions: label,
            });
        });

        input.value = '';
    }
</script>
@endpush
