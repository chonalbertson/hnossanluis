@extends('layouts.admin')

@section('title', 'Dashboard Operativo')
@section('header', 'Panel de Control')
@section('subheader', 'Resumen en tiempo real del catálogo, cotizaciones B2B y actividad del sistema.')

@section('content')
    @php
        $user = auth()->user();
        $statusColors = [
            'nueva' => 'bg-blue-500',
            'en_revision' => 'bg-indigo-500',
            'pendiente_info' => 'bg-amber-500',
            'en_preparacion' => 'bg-violet-500',
            'respondida' => 'bg-emerald-500',
            'aceptada' => 'bg-green-600',
            'rechazada' => 'bg-rose-500',
            'cerrada' => 'bg-slate-400',
        ];
        $maxMonthly = max(1, collect($quotesPerMonth)->max('total'));
        $totalStatus = max(1, array_sum($quotesByStatus));
        $maxCategory = max(1, (int) $productsByCategory->max('products_count'));
        $maxQuoted = max(1, (int) $topQuotedProducts->max('quotes_count'));
        $categoryImage = fn ($category) => $category->image_path
            ? (str_starts_with($category->image_path, 'http') ? $category->image_path : asset('storage/'.ltrim($category->image_path, '/')))
            : asset('images/logo-sanluis-icon.png');
        $greeting = now()->hour < 12 ? 'Buenos días' : (now()->hour < 19 ? 'Buenas tardes' : 'Buenas noches');
    @endphp

    {{-- BIENVENIDA --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0F172A] via-[#1E3A8A] to-[#0F172A] text-white shadow-lg">
        <div class="absolute inset-0 opacity-[0.07]" style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 40px 40px;"></div>
        <div class="absolute -right-20 -top-20 w-72 h-72 rounded-full bg-[#DC2626]/30 blur-3xl"></div>
        <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-6 p-6 sm:p-8 items-center">
            <div class="lg:col-span-7 flex flex-col gap-4">
                <span class="text-[11px] font-extrabold uppercase tracking-widest text-amber-300">{{ ucfirst(now()->locale('es')->translatedFormat('l, d \d\e F Y')) }}</span>
                <h2 class="text-2xl sm:text-3xl font-black leading-tight">{{ $greeting }}, {{ \Illuminate\Support\Str::of($user?->name)->before(' ') }} 👋</h2>
                <p class="text-sm text-blue-100 max-w-xl">
                    Tienes <strong class="text-white">{{ $kpis['pending_quotes'] }} cotizaciones pendientes</strong>,
                    <strong class="text-white">{{ $kpis['new_contact_messages'] ?? 0 }} mensajes nuevos</strong> y
                    <strong class="text-white">{{ number_format($kpis['published_products']) }} productos publicados</strong> en el catálogo.
                </p>
                <div class="flex flex-wrap gap-2.5">
                    @if($user?->hasPermission('quotes.view'))
                        <a href="{{ route('admin.quotes.index', ['status' => 'nueva']) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-xs font-extrabold uppercase tracking-wider transition-colors">
                            Atender cotizaciones ({{ $kpis['pending_quotes'] }})
                        </a>
                    @endif
                    @if($user?->hasPermission('catalog.manage'))
                        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-xs font-bold transition-colors">
                            + Nuevo producto
                        </a>
                    @endif
                    @if($user?->hasPermission('quotes.manage') || $user?->hasPermission('quotes.respond'))
                        <a href="{{ route('admin.quotes.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-xs font-bold transition-colors">
                            + Cotización manual
                        </a>
                    @endif
                </div>
            </div>
            <div class="lg:col-span-5 hidden sm:grid grid-cols-4 gap-3">
                @foreach($latestProducts->take(4) as $heroProduct)
                    <div class="aspect-square rounded-2xl bg-white p-2.5 shadow-xl {{ $loop->even ? 'translate-y-4' : '' }}">
                        <img src="{{ $heroProduct->primaryImageUrl() }}" alt="{{ $heroProduct->name }}" class="w-full h-full object-contain" loading="lazy">
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Productos publicados', 'value' => number_format($kpis['published_products']), 'meta' => 'de '.number_format($kpis['total_products']).' en catálogo', 'progress' => $kpis['total_products'] > 0 ? round($kpis['published_products'] / $kpis['total_products'] * 100) : 0, 'color' => 'bg-[#1E3A8A]', 'tone' => 'bg-blue-50 text-[#1E3A8A]', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
            ['label' => 'Cotizaciones del mes', 'value' => number_format($kpis['current_month_quotes']), 'meta' => $kpis['quotes_trend'] === null ? 'Sin datos del mes anterior' : (($kpis['quotes_trend'] >= 0 ? '▲ ' : '▼ ').abs($kpis['quotes_trend']).'% vs mes anterior'), 'progress' => $kpis['total_quotes'] > 0 ? round($kpis['responded_quotes'] / $kpis['total_quotes'] * 100) : 0, 'color' => 'bg-[#DC2626]', 'tone' => 'bg-red-50 text-[#DC2626]', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'],
            ['label' => 'Clientes registrados', 'value' => number_format($kpis['total_customers']), 'meta' => number_format($kpis['total_quotes']).' solicitudes históricas', 'progress' => null, 'color' => 'bg-emerald-500', 'tone' => 'bg-emerald-50 text-emerald-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
            ['label' => 'Mensajes nuevos', 'value' => number_format($kpis['new_contact_messages'] ?? 0), 'meta' => number_format($kpis['total_contact_messages'] ?? 0).' mensajes en total', 'progress' => null, 'color' => 'bg-amber-500', 'tone' => 'bg-amber-50 text-amber-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'],
        ] as $card)
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs hover:shadow-md transition-shadow flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $card['label'] }}</span>
                    <span class="w-10 h-10 rounded-xl {{ $card['tone'] }} flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $card['icon'] !!}</svg>
                    </span>
                </div>
                <div class="text-3xl font-black text-slate-900 tabular-nums">{{ $card['value'] }}</div>
                <div class="text-xs text-slate-500">{{ $card['meta'] }}</div>
                @if($card['progress'] !== null)
                    <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden" title="{{ $card['progress'] }}%">
                        <div class="dash-bar-x h-full rounded-full {{ $card['color'] }}" style="width: {{ $card['progress'] }}%"></div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- GRÁFICOS DE COTIZACIONES --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Cotizaciones recibidas</h2>
                    <p class="text-xs text-slate-500">Últimos 6 meses</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-xs font-bold text-slate-700">Total: {{ collect($quotesPerMonth)->sum('total') }}</span>
            </div>
            <div class="mt-6 h-56 flex items-end gap-3 sm:gap-6 border-b border-slate-200 relative">
                @foreach([100, 75, 50, 25] as $gridLine)
                    <div class="absolute inset-x-0 border-t border-dashed border-slate-100" style="bottom: {{ $gridLine }}%"></div>
                @endforeach
                @foreach($quotesPerMonth as $month)
                    @php $height = $month['total'] > 0 ? max(4, round($month['total'] / $maxMonthly * 100)) : 2; @endphp
                    <div class="relative flex-1 h-full flex flex-col justify-end items-center group">
                        <span class="mb-1.5 text-xs font-black tabular-nums {{ $month['is_current'] ? 'text-[#DC2626]' : 'text-slate-700' }}">{{ $month['total'] }}</span>
                        <div class="dash-bar w-full max-w-14 rounded-t-xl {{ $month['is_current'] ? 'bg-gradient-to-t from-[#B91C1C] to-[#F87171]' : 'bg-gradient-to-t from-[#1E3A8A] to-[#60A5FA]' }} group-hover:opacity-80 transition-opacity" style="height: {{ $height }}%; animation-delay: {{ $loop->index * 80 }}ms"></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex gap-3 sm:gap-6">
                @foreach($quotesPerMonth as $month)
                    <span class="flex-1 text-center text-[11px] font-bold uppercase {{ $month['is_current'] ? 'text-[#DC2626]' : 'text-slate-400' }}">{{ $month['label'] }}</span>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-6 flex flex-col">
            <h2 class="text-sm font-bold text-slate-900">Estado de cotizaciones</h2>
            <p class="text-xs text-slate-500">{{ array_sum($quotesByStatus) }} solicitudes en total</p>
            <div class="mt-5 flex h-3 rounded-full overflow-hidden bg-slate-100">
                @foreach(\App\Models\Quote::STATUSES as $statusKey => $statusLabel)
                    @if(($quotesByStatus[$statusKey] ?? 0) > 0)
                        <div class="{{ $statusColors[$statusKey] }}" style="width: {{ ($quotesByStatus[$statusKey] / $totalStatus) * 100 }}%" title="{{ $statusLabel }}"></div>
                    @endif
                @endforeach
            </div>
            <ul class="mt-5 flex flex-col gap-3 text-xs">
                @foreach(\App\Models\Quote::STATUSES as $statusKey => $statusLabel)
                    @php $statusTotal = $quotesByStatus[$statusKey] ?? 0; @endphp
                    <li>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 font-semibold text-slate-700"><span class="w-2.5 h-2.5 rounded-full {{ $statusColors[$statusKey] }}"></span>{{ $statusLabel }}</span>
                            <span class="font-black tabular-nums text-slate-900">{{ $statusTotal }}</span>
                        </div>
                        <div class="mt-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                            <div class="dash-bar-x h-full rounded-full {{ $statusColors[$statusKey] }}" style="width: {{ round($statusTotal / $totalStatus * 100) }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- CATÁLOGO --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Productos por categoría</h2>
                    <p class="text-xs text-slate-500">Top {{ $productsByCategory->count() }} líneas con más productos publicados</p>
                </div>
                @if($user?->hasPermission('catalog.manage'))
                    <a href="{{ route('admin.categories.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">Gestionar &rarr;</a>
                @endif
            </div>
            <ul class="mt-5 flex flex-col gap-3.5">
                @foreach($productsByCategory as $category)
                    <li class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 p-1.5 shrink-0">
                            <img src="{{ $categoryImage($category) }}" alt="{{ $category->name }}" class="w-full h-full object-contain" loading="lazy">
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-800 truncate">{{ $category->name }}</span>
                                <span class="font-black tabular-nums text-slate-900">{{ $category->products_count }}</span>
                            </div>
                            <div class="mt-1.5 h-2.5 rounded-full bg-slate-100 overflow-hidden">
                                <div class="dash-bar-x h-full rounded-full bg-gradient-to-r from-[#1E3A8A] to-[#3B82F6]" style="width: {{ round($category->products_count / $maxCategory * 100) }}%; animation-delay: {{ $loop->index * 60 }}ms"></div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-6 flex flex-col gap-5">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Salud del catálogo</h2>
                <p class="text-xs text-slate-500">Productos con imagen</p>
            </div>
            <div class="flex items-center gap-5">
                <div class="relative w-28 h-28 rounded-full shrink-0" style="background: conic-gradient(#10B981 {{ $kpis['image_coverage'] * 3.6 }}deg, #E2E8F0 0deg)">
                    <div class="absolute inset-2.5 rounded-full bg-white flex flex-col items-center justify-center">
                        <span class="text-2xl font-black text-slate-900">{{ $kpis['image_coverage'] }}%</span>
                        <span class="text-[10px] font-bold uppercase text-slate-400">con foto</span>
                    </div>
                </div>
                <dl class="flex flex-col gap-2 text-xs">
                    <div><dt class="text-slate-500">Con imagen</dt><dd class="font-black text-slate-900">{{ number_format($kpis['products_with_images']) }}</dd></div>
                    <div><dt class="text-slate-500">Sin imagen</dt><dd class="font-black text-rose-600">{{ number_format($kpis['total_products'] - $kpis['products_with_images']) }}</dd></div>
                </dl>
            </div>
            <div class="grid grid-cols-3 gap-2 pt-4 border-t border-slate-100 text-center">
                <div class="rounded-xl bg-slate-50 p-3">
                    <div class="text-lg font-black text-[#1E3A8A]">{{ $kpis['categories'] }}</div>
                    <div class="text-[10px] font-bold uppercase text-slate-500">Categorías</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <div class="text-lg font-black text-[#DC2626]">{{ $kpis['brands'] }}</div>
                    <div class="text-[10px] font-bold uppercase text-slate-500">Marcas</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <div class="text-lg font-black text-amber-600">{{ number_format($kpis['on_request_products']) }}</div>
                    <div class="text-[10px] font-bold uppercase text-slate-500">A pedido</div>
                </div>
            </div>
        </div>
    </div>

    {{-- PRODUCTOS: MÁS COTIZADOS + ÚLTIMOS AGREGADOS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h2 class="text-sm font-bold text-slate-900">Productos más cotizados</h2>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse($topQuotedProducts as $item)
                    @php $quotedModel = $topQuotedProductModels[$item->product_id] ?? null; @endphp
                    <li class="px-5 py-3 flex items-center gap-3">
                        <span class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200 p-1.5 shrink-0">
                            <img src="{{ $quotedModel?->primaryImageUrl() ?? asset('images/placeholder-product.svg') }}" alt="{{ $item->product_name_snapshot }}" class="w-full h-full object-contain" loading="lazy">
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <span class="font-bold text-slate-800 truncate">{{ $item->product_name_snapshot }}</span>
                                <span class="shrink-0 font-black text-slate-900">{{ $item->quotes_count }} cot.</span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="dash-bar-x h-full rounded-full bg-gradient-to-r from-[#DC2626] to-amber-400" style="width: {{ round($item->quotes_count / $maxQuoted * 100) }}%"></div>
                                </div>
                                <span class="text-[10px] font-mono text-slate-400 shrink-0">{{ $item->sku_snapshot }} · {{ number_format((float) $item->total_quantity, 0) }} und.</span>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="p-8 text-center text-xs text-slate-500">Aún no hay productos cotizados.</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900">Últimos productos agregados</h2>
                @if($user?->hasPermission('catalog.manage'))
                    <a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">Ver catálogo &rarr;</a>
                @endif
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4">
                @foreach($latestProducts as $latestProduct)
                    <a href="{{ route('catalog.show', $latestProduct->slug) }}" target="_blank" rel="noopener" class="group rounded-xl border border-slate-200 hover:border-[#1E3A8A] hover:shadow-md transition-all overflow-hidden">
                        <div class="aspect-square bg-slate-50 p-2.5">
                            <img src="{{ $latestProduct->primaryImageUrl() }}" alt="{{ $latestProduct->name }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform" loading="lazy">
                        </div>
                        <div class="p-2 border-t border-slate-100">
                            <p class="text-[11px] font-bold text-slate-800 line-clamp-2 leading-snug">{{ $latestProduct->name }}</p>
                            <p class="text-[10px] text-slate-400 truncate mt-0.5">{{ $latestProduct->brand?->name ?? $latestProduct->category?->name }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @if($user?->hasPermission('settings.manage'))
        {{-- SEGURIDAD --}}
        <div class="bg-slate-900 text-white rounded-2xl border border-slate-800 p-4 sm:p-5 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-xs">
            <div class="flex items-start sm:items-center gap-3.5">
                <span class="p-2.5 rounded-xl {{ ($kpis['security_pending_incidents'] ?? 0) > 0 ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }} shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-sm text-white">Escudo Antimalware, WAF y Bóveda de Cuarentena</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">PROTECCIÓN ACTIVA</span>
                    </div>
                    <p class="text-slate-300 mt-1">
                        Incidentes pendientes: <strong class="{{ ($kpis['security_pending_incidents'] ?? 0) > 0 ? 'text-rose-400' : 'text-emerald-400' }}">{{ $kpis['security_pending_incidents'] ?? 0 }}</strong>
                        | Archivos en cuarentena: <strong class="text-amber-300">{{ $kpis['security_quarantined_files'] ?? 0 }}</strong>
                        @if(!empty($kpis['latest_security_scan']))
                            | Último escaneo: <strong class="text-slate-200">{{ $kpis['latest_security_scan']->created_at?->diffForHumans() }} ({{ $kpis['latest_security_scan']->files_scanned }} archivos)</strong>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <a href="{{ route('admin.backups.index') }}" class="px-3.5 py-2 rounded-lg bg-white/10 hover:bg-white/20 font-bold transition">Copias de seguridad</a>
                <a href="{{ route('admin.security.index') }}" class="px-3.5 py-2 rounded-lg bg-amber-500 text-slate-950 hover:bg-amber-400 font-bold transition">Centro de Seguridad &rarr;</a>
            </div>
        </div>
    @endif

    {{-- COTIZACIONES RECIENTES + AUDITORÍA --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900">Últimas solicitudes de cotización</h2>
                @if($user?->hasPermission('quotes.view'))
                    <a href="{{ route('admin.quotes.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">Ver todas &rarr;</a>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-3 text-left">Código</th>
                            <th class="px-4 py-3 text-left">Cliente / Empresa</th>
                            <th class="px-4 py-3 text-center">Ítems</th>
                            <th class="px-4 py-3 text-left">Estado</th>
                            <th class="px-4 py-3 text-left">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentQuotes as $quote)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-mono font-bold text-slate-900">
                                    @if($user?->hasPermission('quotes.view'))
                                        <a href="{{ route('admin.quotes.show', $quote) }}" class="text-blue-600 hover:underline">{{ $quote->reference_code }}</a>
                                    @else
                                        {{ $quote->reference_code }}
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-full bg-[#1E3A8A] text-white font-black flex items-center justify-center shrink-0">{{ mb_strtoupper(mb_substr($quote->contact_name, 0, 1)) }}</span>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-slate-900 truncate">{{ $quote->contact_name }}</div>
                                            <div class="text-slate-500 truncate">{{ $quote->company_name ?: $quote->contact_email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center font-semibold">{{ $quote->items->count() }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $quote->statusBadgeClass() }}">{{ $quote->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $quote->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">No hay cotizaciones registradas aún.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-col gap-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Accesos rápidos</h2>
                <div class="grid grid-cols-2 gap-2">
                    @if($user?->hasPermission('imports.manage'))
                        <a href="{{ route('admin.imports.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700 transition">Importar / Exportar</a>
                    @endif
                    @if($user?->hasPermission('quotes.view'))
                        <a href="{{ route('admin.contact-messages.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700 transition">Mensajes ({{ $kpis['new_contact_messages'] ?? 0 }})</a>
                    @endif
                    @if($user?->hasPermission('settings.manage'))
                        <a href="{{ route('admin.settings.commercial') }}" class="px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700 transition">Modo de precios</a>
                    @endif
                    @if($user?->hasPermission('reports.view'))
                        <a href="{{ route('admin.reports.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700 transition">Informes</a>
                    @endif
                </div>
            </div>

            @if($user?->hasPermission('activity_logs.view'))
                <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-900">Auditoría reciente</h2>
                        <a href="{{ route('admin.activity-logs.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">Historial &rarr;</a>
                    </div>
                    <ol class="px-5 py-4 flex flex-col gap-4 text-xs">
                        @forelse($recentActivity as $log)
                            <li class="relative pl-5 border-l-2 border-slate-200">
                                <span class="absolute -left-[5px] top-1 w-2 h-2 rounded-full bg-[#1E3A8A]"></span>
                                <div class="flex items-center justify-between text-[11px] text-slate-400">
                                    <span class="font-mono font-semibold text-slate-600">{{ $log->action }}</span>
                                    <span>{{ $log->created_at?->diffForHumans() }}</span>
                                </div>
                                <div class="text-slate-700 mt-0.5 line-clamp-2">{{ $log->description }}</div>
                            </li>
                        @empty
                            <li class="text-center text-slate-500">Sin actividad registrada.</li>
                        @endforelse
                    </ol>
                </div>
            @endif
        </div>
    </div>
@endsection
