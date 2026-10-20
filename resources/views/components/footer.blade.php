@php
    use App\Models\Category;
    use App\Models\SiteSetting;
    use App\Services\WhatsAppService;

    $companyName = (string) SiteSetting::getValue('company_commercial_name', 'Import Hnos San Luis');
    $companyLegalName = (string) SiteSetting::getValue('company_legal_name', 'IMPORT HNOS SAN LUIS S.A.C.');
    $companyRuc = (string) SiteSetting::getValue('company_ruc', '20608628143');
    $contactPhone = (string) SiteSetting::getValue('company_contact_phone', '977 208 514');
    $contactEmail = (string) SiteSetting::getValue('company_contact_email', 'ventas@hnssanluis.com');
    $companyAddress = (string) SiteSetting::getValue('company_address', 'Av. Guillermo Dansey 429 - Lima Centro, Lima, Perú');
    $businessHours = (string) SiteSetting::getValue('company_business_hours', 'Lunes a Viernes: 8:30 am - 6:00 pm | Sábados: 9:00 am - 1:00 pm');

    $footerCategories = Category::cachedNavigationTree()->take(6);

    $whatsappService = app(WhatsAppService::class);
    $whatsappUrl = $whatsappService->generalContactUrl();
    $whatsappAdvisors = $whatsappService->getAdvisors();
@endphp

<footer class="relative bg-[#0B1220] text-slate-300 mt-16" aria-label="Pie de página corporativo">
    <div class="h-1 bg-gradient-to-r from-[#DC2626] via-[#1E3A8A] to-[#DC2626]"></div>

    {{-- Franja de conversión --}}
    <div class="border-b border-white/5 bg-gradient-to-r from-[#0F172A] via-[#132048] to-[#0F172A]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <span class="hidden sm:flex w-12 h-12 rounded-2xl bg-[#DC2626] text-white items-center justify-center shrink-0 shadow-lg shadow-red-900/40">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
                <div>
                    <p class="text-lg sm:text-xl font-black text-white">¿Tienes una lista de materiales para cotizar?</p>
                    <p class="text-sm text-slate-400">Precios por volumen, stock confirmado y factura electrónica con RUC.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('quote.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs font-extrabold uppercase tracking-wider transition-colors">
                    Solicitar Cotización
                </a>
                <a href="{{ $whatsappUrl }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   data-ga-event="whatsapp_click"
                   data-ga-params='{"cta_location":"footer","context_type":"general"}'
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#22C55E] hover:bg-[#16A34A] text-white text-xs font-extrabold uppercase tracking-wider transition-colors">
                    <x-icons.whatsapp class="w-4 h-4" />
                    Consultar por WhatsApp
                </a>
            </div>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-14">
        <div class="grid grid-cols-2 lg:grid-cols-12 gap-x-6 gap-y-10">
            {{-- Marca --}}
            <div class="col-span-2 lg:col-span-4 flex flex-col gap-5">
                <a href="{{ route('home') }}" class="inline-block w-fit bg-white rounded-2xl px-4 py-3 shadow-md ring-1 ring-white/15 hover:shadow-lg transition-all">
                    <picture>
                        <source srcset="{{ asset('images/logo-sanluis-horizontal.webp') }}" type="image/webp">
                        <img src="{{ asset('images/logo-sanluis-horizontal.png') }}"
                             alt="{{ $companyName }}"
                             width="248"
                             height="58"
                             loading="lazy"
                             class="h-13 sm:h-15 w-auto object-contain">
                    </picture>
                </a>
                <p class="text-sm text-slate-400 leading-relaxed max-w-sm">
                    Abastecimiento técnico de herrajes para muebles, sistemas corredizos, fijaciones estructurales y ferretería industrial para proyectos, distribuidores y talleres en el Perú.
                </p>
                <div class="flex items-center gap-3 w-fit rounded-xl bg-white/5 border border-white/10 px-4 py-3">
                    <svg class="w-8 h-8 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <div>
                        <p class="text-xs font-black text-white tracking-wide">{{ $companyLegalName }}</p>
                        <p class="text-xs font-mono text-amber-400 font-semibold">RUC: {{ $companyRuc }}</p>
                    </div>
                </div>
                <ul class="flex flex-col gap-2.5 text-sm">
                    <li class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-[#DC2626] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ $companyAddress }}</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-[#DC2626] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <a href="mailto:{{ $contactEmail }}" class="hover:text-white transition-colors break-all">{{ $contactEmail }}</a>
                    </li>
                    <li class="flex items-start gap-2.5 text-slate-400">
                        <svg class="w-4 h-4 text-[#DC2626] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $businessHours }}</span>
                    </li>
                </ul>
            </div>

            {{-- Líneas de catálogo --}}
            <div class="lg:col-span-2">
                <h2 class="text-xs font-extrabold uppercase tracking-widest text-white mb-5">Líneas de Catálogo</h2>
                <ul class="flex flex-col gap-2.5 text-sm">
                    @foreach($footerCategories as $cat)
                        <li>
                            <a href="{{ route('catalog.index', ['category' => $cat->slug]) }}"
                               class="group inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
                                <span class="w-1 h-1 rounded-full bg-slate-600 group-hover:bg-[#DC2626] transition-colors"></span>
                                {{ $cat->name }}
                            </a>
                        </li>
                    @endforeach
                    <li class="pt-1">
                        <a href="{{ route('catalog.index') }}" class="text-xs font-extrabold uppercase tracking-wider text-blue-400 hover:text-blue-300">
                            Ver catálogo completo &rarr;
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Navegación --}}
            <div class="lg:col-span-2">
                <h2 class="text-xs font-extrabold uppercase tracking-widest text-white mb-5">Navegación</h2>
                <ul class="flex flex-col gap-2.5 text-sm">
                    <li><a href="{{ route('catalog.index') }}" class="text-slate-400 hover:text-white transition-colors">Catálogo de Productos</a></li>
                    <li><a href="{{ route('quote.index') }}" class="text-slate-400 hover:text-white transition-colors">Solicitar Cotización</a></li>
                    <li><a href="{{ route('quote.track') }}" class="text-amber-300 hover:text-white font-semibold transition-colors">Seguimiento de Cotización</a></li>
                    <li><a href="{{ route('pages.company') }}" class="text-slate-400 hover:text-white transition-colors">Empresa</a></li>
                    <li><a href="{{ route('pages.about') }}" class="text-slate-400 hover:text-white transition-colors">Nosotros</a></li>
                    <li><a href="{{ route('contact.index') }}" class="text-slate-400 hover:text-white transition-colors">Contacto</a></li>
                    @auth
                        <li><a href="{{ route('profile.edit') }}" class="text-slate-400 hover:text-white transition-colors">Mi Cuenta</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="text-slate-400 hover:text-white transition-colors">Ingresar</a></li>
                    @endauth
                </ul>
            </div>

            {{-- Asesoras WhatsApp --}}
            <div class="col-span-2 lg:col-span-4">
                <h2 class="text-xs font-extrabold uppercase tracking-widest text-white mb-5">WhatsApp Asesoras</h2>
                <div class="grid grid-cols-2 gap-2.5">
                    @foreach($whatsappAdvisors as $advisor)
                        <a href="{{ $advisor['url'] }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           data-ga-event="whatsapp_click"
                           data-ga-params='{"cta_location":"footer_col1","context_type":"advisor"}'
                           aria-label="WhatsApp {{ $advisor['phone'] }}{{ $advisor['name'] !== '' ? ' - ' . $advisor['name'] : '' }}"
                           class="group flex items-center gap-2.5 p-2.5 rounded-xl bg-white/5 border border-white/10 hover:border-[#22C55E] hover:bg-[#22C55E]/10 transition-colors">
                            <span class="w-9 h-9 rounded-full bg-[#22C55E] text-white flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                <x-icons.whatsapp class="w-5 h-5" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-[11px] font-bold text-slate-400 group-hover:text-emerald-300 truncate">{{ $advisor['name'] !== '' ? $advisor['name'] : 'Central Ventas' }}</span>
                                <span class="block font-mono text-xs sm:text-sm font-black text-white tabular-nums whitespace-nowrap">{{ $advisor['phone'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>

                <a href="{{ route('pages.complaints-book') }}"
                   aria-label="Libro de Reclamaciones Virtual - {{ $companyLegalName }}"
                   class="group mt-5 flex items-center gap-3 w-full px-4 py-3 rounded-xl bg-white hover:bg-blue-50 text-[#0F172A] transition-colors">
                    <span class="w-10 h-10 rounded-lg bg-[#1E3A8A] group-hover:bg-[#DC2626] text-white flex items-center justify-center shrink-0 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </span>
                    <span class="flex-1 leading-tight">
                        <span class="block text-[10px] font-extrabold uppercase tracking-wider text-[#1E3A8A] group-hover:text-[#DC2626] transition-colors">Conforme a INDECOPI</span>
                        <span class="block text-sm font-black uppercase tracking-tight text-slate-900">Libro de Reclamaciones</span>
                    </span>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-[#DC2626] group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Barra legal --}}
    <div class="border-t border-white/5 bg-[#070D18]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col lg:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <p class="text-center lg:text-left">&copy; {{ date('Y') }} <strong class="text-slate-300">{{ $companyLegalName }}</strong> — <span class="font-mono text-slate-400">RUC {{ $companyRuc }}</span>. Todos los derechos reservados. Protección de Datos Personales conforme a la Ley N° 29733 (Perú).</p>
            <nav aria-label="Enlaces legales">
                <ul class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2">
                    <li><a href="{{ route('pages.privacy') }}" class="hover:text-white transition-colors">Política de Privacidad</a></li>
                    <li><a href="{{ route('pages.cookies') }}" class="hover:text-white transition-colors">Política de Cookies</a></li>
                    <li><a href="{{ route('pages.terms') }}" class="hover:text-white transition-colors">Términos y Condiciones</a></li>
                    <li><a href="{{ route('pages.quote-conditions') }}" class="hover:text-white transition-colors">Condiciones de Cotización</a></li>
                </ul>
            </nav>
        </div>
    </div>
</footer>
