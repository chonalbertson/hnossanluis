/**
 * Import Hns San Luis — Portal Público, Catálogo Técnico & Analítica GA4 (Zero-PII)
 */

(function () {
    'use strict';

    /**
     * Forbidden PII keys that must NEVER be transmitted to Google Analytics 4 or dataLayer.
     */
    const FORBIDDEN_PII_KEYS = [
        'name',
        'contact_name',
        'author_name',
        'first_name',
        'last_name',
        'email',
        'contact_email',
        'phone',
        'contact_phone',
        'telephone',
        'mobile',
        'whatsapp',
        'ruc',
        'dni',
        'tax_id',
        'document_number',
        'company',
        'company_name',
        'address',
        'message',
        'customer_observations',
        'notes',
        'item_notes',
        'password',
        'token',
    ];

    /**
     * Sanitize string values to ensure emails, phone numbers, or RUCs are not leaked in free-text parameters like search_term.
     */
    function sanitizeStringValue(key, value) {
        if (typeof value !== 'string') {
            return value;
        }
        const trimmed = value.trim();
        // Redact if value looks like an email address
        if (/[^\s@]+@[^\s@]+\.[^\s@]+/.test(trimmed)) {
            return '[REDACTED_EMAIL]';
        }
        // If search_term or general string is purely a 9-11 digit phone/RUC/DNI number, redact only if not prefixed like an SKU
        if (key === 'search_term' && /^\+?\d[\d\s-]{7,14}$/.test(trimmed)) {
            return '[REDACTED_NUMBER]';
        }
        return trimmed.slice(0, 150);
    }

    /**
     * Deep-clean an event payload object removing any PII keys.
     */
    function sanitizePayload(params) {
        if (!params || typeof params !== 'object') {
            return {};
        }

        const clean = {};
        for (const [rawKey, rawValue] of Object.entries(params)) {
            const lowerKey = String(rawKey).toLowerCase().trim();

            const isForbidden = FORBIDDEN_PII_KEYS.some(
                (forbidden) => lowerKey === forbidden || lowerKey.endsWith('_' + forbidden)
            );

            if (isForbidden) {
                continue;
            }

            if (Array.isArray(rawValue)) {
                clean[rawKey] = rawValue.map((entry) =>
                    typeof entry === 'object' && entry !== null ? sanitizePayload(entry) : sanitizeStringValue(lowerKey, entry)
                );
            } else if (typeof rawValue === 'object' && rawValue !== null) {
                clean[rawKey] = sanitizePayload(rawValue);
            } else if (typeof rawValue === 'string') {
                clean[rawKey] = sanitizeStringValue(lowerKey, rawValue);
            } else {
                clean[rawKey] = rawValue;
            }
        }

        return clean;
    }

    window.dataLayer = window.dataLayer || [];
    window.__sanLuisEvents = window.__sanLuisEvents || [];

    /**
     * Global Analytics Façade (Zero-PII GA4 Wrapper)
     */
    const SanLuisAnalytics = {
        track(eventName, params = {}) {
            if (!eventName || typeof eventName !== 'string') {
                return;
            }

            const safeParams = sanitizePayload(params);

            window.__sanLuisEvents.push({
                event: eventName,
                params: safeParams,
                timestamp: new Date().toISOString(),
            });

            if (typeof window.gtag === 'function') {
                window.gtag('event', eventName, safeParams);
            } else if (Array.isArray(window.dataLayer)) {
                window.dataLayer.push({
                    event: eventName,
                    ...safeParams,
                });
            }
        },

        trackSearch(searchTerm, categoryScope = 'all', resultsCount = null) {
            if (!searchTerm || String(searchTerm).trim() === '') {
                return;
            }
            this.track('search', {
                search_term: String(searchTerm).trim(),
                category_scope: categoryScope || 'all',
                results_count: resultsCount,
            });
        },

        trackViewItem(itemData = {}) {
            this.track('view_item', {
                currency: itemData.currency || 'PEN',
                value: itemData.value ?? null,
                items: [
                    {
                        item_id: itemData.sku || itemData.item_id || '',
                        item_name: itemData.item_name || '',
                        item_category: itemData.item_category || '',
                        item_brand: itemData.item_brand || '',
                        item_variant: itemData.sale_unit || itemData.item_variant || 'Unidad',
                        price: itemData.price ?? null,
                    },
                ],
            });
        },

        trackFilterCatalog(filters = {}) {
            this.track('filter_catalog', {
                filter_category: filters.category || null,
                filter_subcategory: filters.subcategory || null,
                filter_brand: filters.brand || null,
                filter_availability: filters.availability || null,
                filter_material: filters.material || null,
                sort_by: filters.sort || 'relevance',
                results_count: filters.results_count ?? null,
            });
        },

        trackFileDownload(fileData = {}) {
            this.track('file_download', {
                file_name: fileData.file_name || 'ficha-tecnica.pdf',
                file_extension: 'pdf',
                link_text: fileData.link_text || 'Descargar Ficha Técnica PDF',
                item_id: fileData.item_id || '',
                item_name: fileData.item_name || '',
                item_category: fileData.item_category || '',
            });
        },

        trackBeginQuote(quoteItemData = {}) {
            this.track('begin_quote', {
                cta_location: quoteItemData.cta_location || 'catalog',
                item_id: quoteItemData.item_id || '',
                item_name: quoteItemData.item_name || '',
                item_category: quoteItemData.item_category || '',
                item_brand: quoteItemData.item_brand || '',
                unit_of_measure: quoteItemData.unit_of_measure || 'Unidad',
                quantity: Number(quoteItemData.quantity || 1),
            });
        },

        trackSubmitQuote(quoteSummary = {}) {
            this.track('submit_quote', {
                quote_reference: quoteSummary.quote_reference || '',
                client_type: quoteSummary.client_type || 'b2c',
                request_type: quoteSummary.request_type || 'multiple',
                items_count: Number(quoteSummary.items_count || 1),
                total_units: Number(quoteSummary.total_units || 1),
                currency: quoteSummary.currency || 'PEN',
            });
        },

        trackWhatsAppClick(waData = {}) {
            this.track('whatsapp_click', {
                cta_location: waData.cta_location || 'floating_widget',
                context_type: waData.context_type || 'general',
                item_id: waData.item_id || null,
                item_name: waData.item_name || null,
                items_count: Number(waData.items_count || 0),
            });
        },

        trackContactSubmit(contactMeta = {}) {
            this.track('contact_form_submit', {
                inquiry_type: contactMeta.inquiry_type || 'general',
                client_type: contactMeta.client_type || 'general',
            });
        },
    };

    window.SanLuisAnalytics = SanLuisAnalytics;

    /**
     * Toast Notification Helper for Quote & Comparator Actions
     */
    function showToast(message, type = 'success', actionHref = '/cotizacion', actionLabel = 'Ver Lista') {
        const container = document.getElementById('sanluis-toast-container');
        if (!container) {
            return;
        }

        const toast = document.createElement('div');
        const bgClass =
            type === 'error'
                ? 'bg-red-800 border-red-600 text-white'
                : 'bg-slate-900 border-l-4 border-l-red-600 border-slate-700 text-white';

        toast.className = `${bgClass} border rounded-lg shadow-xl px-4 py-3 text-sm flex items-center justify-between gap-3 transition-all duration-200`;
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');

        const textSpan = document.createElement('span');
        textSpan.className = 'font-medium';
        textSpan.textContent = message;

        toast.appendChild(textSpan);

        if (actionHref && actionLabel) {
            const actionLink = document.createElement('a');
            actionLink.href = actionHref;
            actionLink.className = 'text-xs font-bold uppercase tracking-wider bg-red-600 hover:bg-red-700 text-white px-2.5 py-1 rounded shrink-0';
            actionLink.textContent = actionLabel;
            toast.appendChild(actionLink);
        }

        container.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 4500);
    }

    /**
     * Escape HTML special characters safely for dynamic dropdown templates
     */
    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Initialize UI Interactions once DOM is ready
     */
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Declarative GA4 Event Listener on [data-ga-event]
        document.body.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-ga-event]');
            if (!trigger) {
                return;
            }

            const eventName = trigger.getAttribute('data-ga-event');
            let params = {};
            const rawParams = trigger.getAttribute('data-ga-params');
            if (rawParams) {
                try {
                    params = JSON.parse(rawParams);
                } catch {
                    params = {};
                }
            }
            SanLuisAnalytics.track(eventName, params);
        });

        // 2. Track search form submission & Initialize Instant SKU Autocomplete (200ms debounce)
        const searchForms = document.querySelectorAll('[data-tech-search-form]');

        function closeAllSearchDropdowns() {
            document.querySelectorAll('[data-tech-search-dropdown]').forEach((dropdown) => {
                dropdown.classList.add('hidden');
            });
            document.querySelectorAll('[data-tech-search-input]').forEach((input) => {
                input.setAttribute('aria-expanded', 'false');
            });
        }

        searchForms.forEach((form) => {
            const qInput = form.querySelector('[data-tech-search-input]');
            const catSelect = form.querySelector('select[name="category"]');
            const wrapper = form.parentElement;
            const dropdown = wrapper ? wrapper.querySelector('[data-tech-search-dropdown]') : null;
            const suggestionsUrl = form.getAttribute('data-suggestions-url') || '/catalogo/sugerencias';

            form.addEventListener('submit', () => {
                if (qInput && qInput.value.trim() !== '') {
                    SanLuisAnalytics.trackSearch(
                        qInput.value.trim(),
                        catSelect && catSelect.value ? catSelect.value : 'all'
                    );
                }
            });

            if (!qInput || !dropdown || !window.fetch) {
                return;
            }

            let debounceTimer = null;
            let activeAbortController = null;

            function renderSuggestions(data) {
                const products = Array.isArray(data.products) ? data.products : [];
                const categories = Array.isArray(data.categories) ? data.categories : [];
                const catalogUrl = data.catalog_url || '/catalogo';
                const queryText = data.query || '';

                if (products.length === 0 && categories.length === 0) {
                    dropdown.innerHTML = `
                        <div class="p-4 text-center space-y-2">
                            <p class="text-xs font-semibold text-slate-600">No encontramos coincidencias directas para "<strong>${escapeHtml(queryText)}</strong>".</p>
                            <a href="/cotizacion" class="inline-block text-xs font-bold text-[#1D4ED8] hover:underline">
                                ¿Requerimiento especial? Solicitar cotización &rarr;
                            </a>
                        </div>
                    `;
                    dropdown.classList.remove('hidden');
                    qInput.setAttribute('aria-expanded', 'true');
                    return;
                }

                let html = '';

                if (categories.length > 0) {
                    html += `<div class="bg-slate-50 px-3.5 py-2 border-b border-slate-200 flex flex-wrap items-center gap-1.5">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500 mr-1">Categorías:</span>`;
                    categories.forEach((cat) => {
                        html += `<a href="${escapeHtml(cat.url)}" class="text-xs font-bold text-[#1E3A8A] bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2.5 py-0.5 rounded-full transition-colors">
                            ${escapeHtml(cat.name)}
                        </a>`;
                    });
                    html += `</div>`;
                }

                if (products.length > 0) {
                    html += `<div class="divide-y divide-slate-100">`;
                    products.forEach((prod) => {
                        const priceHtml = prod.price_formatted
                            ? `<span class="font-mono text-xs font-extrabold text-[#0F172A] tabular-nums">${escapeHtml(prod.price_formatted)}</span>`
                            : `<span class="text-[11px] font-semibold text-[#1E3A8A]">Bajo cotización</span>`;

                        html += `
                            <a href="${escapeHtml(prod.url)}"
                               role="option"
                               data-suggestion-item
                               class="flex items-center gap-3 p-3 hover:bg-blue-50/70 focus:bg-blue-50 focus:outline-none transition-colors">
                                <img src="${escapeHtml(prod.image_url)}"
                                     alt="${escapeHtml(prod.name)}"
                                     class="w-11 h-11 rounded-lg border border-slate-200 bg-white p-1 object-contain shrink-0">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5 mb-0.5">
                                        <span class="font-mono text-[11px] font-extrabold uppercase bg-slate-900 text-white px-1.5 py-0.5 rounded tabular-nums">
                                            ${escapeHtml(prod.sku)}
                                        </span>
                                        ${prod.brand_name ? `<span class="text-[10px] font-bold uppercase text-[#1D4ED8] bg-blue-50 px-1.5 py-0.5 rounded">${escapeHtml(prod.brand_name)}</span>` : ''}
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded border ${escapeHtml(prod.availability_badge)}">
                                            ${escapeHtml(prod.availability_label)}
                                        </span>
                                    </div>
                                    <p class="text-xs sm:text-sm font-bold text-[#0F172A] truncate">${escapeHtml(prod.name)}</p>
                                    <p class="text-[11px] text-slate-500">Unidad: <strong>${escapeHtml(prod.sale_unit)}</strong> ${prod.category_name ? `&bull; ${escapeHtml(prod.category_name)}` : ''}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    ${priceHtml}
                                    <span class="block text-[10px] font-bold text-[#1D4ED8] mt-0.5">Ver ficha &rarr;</span>
                                </div>
                            </a>
                        `;
                    });
                    html += `</div>`;
                }

                html += `
                    <div class="bg-slate-900 text-white px-4 py-2.5 flex items-center justify-between text-xs">
                        <span class="text-slate-300 truncate">Búsqueda técnica: <strong class="font-mono text-white">${escapeHtml(queryText)}</strong></span>
                        <a href="${escapeHtml(catalogUrl)}" data-suggestion-item class="font-extrabold text-amber-400 hover:underline shrink-0 ml-2">
                            Ver todos los resultados &rarr;
                        </a>
                    </div>
                `;

                dropdown.innerHTML = html;
                dropdown.classList.remove('hidden');
                qInput.setAttribute('aria-expanded', 'true');
            }

            async function fetchSuggestions() {
                const term = qInput.value.trim();
                if (term.length < 2) {
                    dropdown.classList.add('hidden');
                    qInput.setAttribute('aria-expanded', 'false');
                    return;
                }

                if (activeAbortController) {
                    activeAbortController.abort();
                }
                activeAbortController = new AbortController();

                const url = new URL(suggestionsUrl, window.location.origin);
                url.searchParams.set('q', term);
                if (catSelect && catSelect.value) {
                    url.searchParams.set('category', catSelect.value);
                }

                try {
                    const response = await fetch(url.toString(), {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        signal: activeAbortController.signal,
                    });

                    if (response.ok) {
                        const data = await response.json();
                        renderSuggestions(data);
                    }
                } catch (err) {
                    if (err && err.name !== 'AbortError') {
                        dropdown.classList.add('hidden');
                    }
                }
            }

            qInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchSuggestions, 200);
            });

            qInput.addEventListener('focus', () => {
                if (qInput.value.trim().length >= 2 && dropdown.innerHTML.trim() !== '') {
                    dropdown.classList.remove('hidden');
                    qInput.setAttribute('aria-expanded', 'true');
                }
            });

            if (catSelect) {
                catSelect.addEventListener('change', () => {
                    if (qInput.value.trim().length >= 2) {
                        fetchSuggestions();
                    }
                });
            }

            // Keyboard navigation (ArrowDown / ArrowUp / Escape) inside search input & dropdown
            wrapper.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    dropdown.classList.add('hidden');
                    qInput.setAttribute('aria-expanded', 'false');
                    qInput.focus();
                    return;
                }

                if (dropdown.classList.contains('hidden')) {
                    return;
                }

                const items = Array.from(dropdown.querySelectorAll('[data-suggestion-item]'));
                if (items.length === 0) {
                    return;
                }

                const currentIndex = items.indexOf(document.activeElement);
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const nextIndex = currentIndex < items.length - 1 ? currentIndex + 1 : 0;
                    items[nextIndex]?.focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (currentIndex <= 0) {
                        qInput.focus();
                    } else {
                        items[currentIndex - 1]?.focus();
                    }
                }
            });
        });

        // Close autocomplete dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('[data-tech-search-form]') && !e.target.closest('[data-tech-search-dropdown]')) {
                closeAllSearchDropdowns();
            }
        });

        // 3. Keyboard shortcut '/' to focus technical search bar
        document.addEventListener('keydown', (event) => {
            if (
                event.key === '/' &&
                !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName || '') &&
                !document.activeElement?.isContentEditable
            ) {
                const searchInput = document.querySelector('[data-tech-search-input]');
                if (searchInput) {
                    event.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                }
            }
        });

        // 4. Mobile Navigation Menu Toggle
        const mobileMenu = document.getElementById('mobile-nav-drawer');
        const mobileMenuOpenBtns = document.querySelectorAll('[data-mobile-menu-toggle]');
        const mobileMenuCloseBtns = document.querySelectorAll('[data-mobile-menu-close]');

        function setMobileMenuOpen(isOpen) {
            if (!mobileMenu) return;
            mobileMenu.classList.toggle('hidden', !isOpen);
            document.body.classList.toggle('overflow-hidden', isOpen);
            mobileMenuOpenBtns.forEach((btn) => btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false'));
        }

        mobileMenuOpenBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const isHidden = mobileMenu?.classList.contains('hidden');
                setMobileMenuOpen(Boolean(isHidden));
            });
        });

        mobileMenuCloseBtns.forEach((btn) => {
            btn.addEventListener('click', () => setMobileMenuOpen(false));
        });

        // 5. Desktop Category Mega-Menu Dropdown & Interactive Corporate Split Tabs
        const catMenuWrapper = document.querySelector('[data-category-megamenu-wrapper]');
        const catMenuToggle = document.querySelector('[data-category-menu-toggle]');
        const catMenuChevron = document.querySelector('[data-category-menu-chevron]');
        const catMenuPanel = document.getElementById('desktop-category-megamenu');

        if (catMenuToggle && catMenuPanel) {
            let megaCloseTimer = null;

            function setMegaMenuOpen(isOpen) {
                clearTimeout(megaCloseTimer);
                catMenuPanel.classList.toggle('hidden', !isOpen);
                catMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                if (catMenuChevron) {
                    catMenuChevron.classList.toggle('rotate-180', isOpen);
                }
            }

            catMenuToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const isHidden = catMenuPanel.classList.contains('hidden');
                setMegaMenuOpen(isHidden);
            });

            if (catMenuWrapper) {
                catMenuWrapper.addEventListener('mouseenter', () => {
                    clearTimeout(megaCloseTimer);
                    setMegaMenuOpen(true);
                });

                catMenuWrapper.addEventListener('mouseleave', () => {
                    megaCloseTimer = setTimeout(() => {
                        setMegaMenuOpen(false);
                    }, 160);
                });
            }

            const megaTabs = catMenuPanel.querySelectorAll('[data-megamenu-tab]');
            const megaPanels = catMenuPanel.querySelectorAll('[data-megamenu-panel]');

            function activateMegaTab(slug) {
                megaTabs.forEach((tab) => {
                    const isMatch = tab.getAttribute('data-megamenu-tab') === slug;
                    tab.classList.toggle('bg-[#1E3A8A]', isMatch);
                    tab.classList.toggle('text-white', isMatch);
                    tab.classList.toggle('border-[#DC2626]', isMatch);
                    tab.classList.toggle('shadow-xs', isMatch);
                    tab.classList.toggle('bg-transparent', !isMatch);
                    tab.classList.toggle('text-slate-700', !isMatch);
                    tab.classList.toggle('border-transparent', !isMatch);
                    tab.classList.toggle('hover:bg-blue-50/80', !isMatch);
                    tab.classList.toggle('hover:text-[#1E3A8A]', !isMatch);

                    const iconBox = tab.querySelector('[data-megamenu-tab-icon]');
                    if (iconBox) {
                        iconBox.classList.toggle('bg-[#DC2626]', isMatch);
                        iconBox.classList.toggle('text-white', isMatch);
                        iconBox.classList.toggle('shadow-xs', isMatch);
                        iconBox.classList.toggle('bg-white', !isMatch);
                        iconBox.classList.toggle('text-[#1E3A8A]', !isMatch);
                        iconBox.classList.toggle('border', !isMatch);
                        iconBox.classList.toggle('border-slate-200/90', !isMatch);
                    }

                    const badge = tab.querySelector('[data-megamenu-tab-badge]');
                    if (badge) {
                        badge.classList.toggle('bg-blue-950/70', isMatch);
                        badge.classList.toggle('text-blue-200', isMatch);
                        badge.classList.toggle('bg-slate-200/75', !isMatch);
                        badge.classList.toggle('text-slate-600', !isMatch);
                    }
                });

                megaPanels.forEach((panel) => {
                    const isMatch = panel.getAttribute('data-megamenu-panel') === slug;
                    panel.classList.toggle('hidden', !isMatch);
                    panel.classList.toggle('block', isMatch);
                });
            }

            megaTabs.forEach((tab) => {
                const slug = tab.getAttribute('data-megamenu-tab');
                if (!slug) return;
                tab.addEventListener('mouseenter', () => activateMegaTab(slug));
                tab.addEventListener('focus', () => activateMegaTab(slug));
            });

            document.addEventListener('click', (e) => {
                if (!catMenuPanel.contains(e.target) && !catMenuToggle.contains(e.target)) {
                    setMegaMenuOpen(false);
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !catMenuPanel.classList.contains('hidden')) {
                    setMegaMenuOpen(false);
                }
            });
        }

        // 5b. Home Specialty Product Tabs Switcher
        const homeTabs = document.querySelectorAll('[data-home-tab]');
        const homeTabPanels = document.querySelectorAll('[data-home-tab-panel]');

        if (homeTabs.length > 0 && homeTabPanels.length > 0) {
            homeTabs.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const targetKey = btn.getAttribute('data-home-tab');
                    if (!targetKey) return;

                    homeTabs.forEach((tab) => {
                        const isActive = tab.getAttribute('data-home-tab') === targetKey;
                        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                        tab.classList.toggle('bg-[#1E3A8A]', isActive);
                        tab.classList.toggle('text-white', isActive);
                        tab.classList.toggle('shadow-xs', isActive);
                        tab.classList.toggle('text-slate-700', !isActive);
                        tab.classList.toggle('hover:text-[#0F172A]', !isActive);
                        tab.classList.toggle('hover:bg-white', !isActive);
                    });

                    homeTabPanels.forEach((panel) => {
                        const isMatch = panel.getAttribute('data-home-tab-panel') === targetKey;
                        panel.classList.toggle('hidden', !isMatch);
                        panel.classList.toggle('block', isMatch);
                    });
                });
            });
        }

        // 5b-2. Home Categories Sector Filter & Expand Toggle
        const catSection = document.querySelector('[data-home-categories-section]');
        if (catSection) {
            const sectorBtns = catSection.querySelectorAll('[data-cat-sector-btn]');
            const catCards = catSection.querySelectorAll('[data-cat-card]');
            const flagshipGrid = catSection.querySelector('[data-cat-flagship-grid]');
            const toggleWrapper = catSection.querySelector('[data-cat-toggle-wrapper]');
            const expandBtn = catSection.querySelector('[data-cat-expand-btn]');
            const expandText = catSection.querySelector('[data-cat-expand-text]');
            const expandIcon = catSection.querySelector('[data-cat-expand-icon]');

            const applyCategorySector = (sectorKey) => {
                sectorBtns.forEach((btn) => {
                    const isActive = btn.getAttribute('data-cat-sector-btn') === sectorKey;
                    btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    btn.classList.toggle('bg-[#0F172A]', isActive);
                    btn.classList.toggle('text-white', isActive);
                    btn.classList.toggle('border-[#0F172A]', isActive);
                    btn.classList.toggle('shadow-sm', isActive);
                    btn.classList.toggle('bg-white', !isActive);
                    btn.classList.toggle('text-slate-700', !isActive);
                    btn.classList.toggle('border-slate-200', !isActive);

                    const badge = btn.querySelector('[data-cat-sector-badge]');
                    if (badge) {
                        badge.classList.toggle('bg-[#DC2626]', isActive);
                        badge.classList.toggle('text-white', isActive);
                        badge.classList.toggle('bg-slate-100', !isActive);
                        badge.classList.toggle('text-slate-600', !isActive);
                    }
                });

                let flagshipVisibleCount = 0;
                catCards.forEach((card) => {
                    const cardSector = card.getAttribute('data-cat-sector');
                    const isPrimary = card.getAttribute('data-cat-primary') === 'true';
                    const isInFlagship = flagshipGrid ? flagshipGrid.contains(card) : false;

                    let shouldShow = false;
                    if (sectorKey === 'principales') {
                        shouldShow = isPrimary;
                    } else if (sectorKey === 'all') {
                        shouldShow = true;
                    } else {
                        shouldShow = cardSector === sectorKey;
                    }

                    card.classList.toggle('hidden', !shouldShow);
                    card.classList.toggle('flex', shouldShow);
                    if (isInFlagship && shouldShow) {
                        flagshipVisibleCount++;
                    }
                });

                if (flagshipGrid) {
                    flagshipGrid.classList.toggle('hidden', flagshipVisibleCount === 0);
                }

                if (toggleWrapper && expandBtn) {
                    const showToggle = sectorKey === 'principales' || sectorKey === 'all';
                    toggleWrapper.classList.toggle('hidden', !showToggle);
                    const isAll = sectorKey === 'all';
                    expandBtn.setAttribute('data-expanded', isAll ? 'true' : 'false');
                    if (expandText) {
                        expandText.textContent = isAll
                            ? (expandBtn.getAttribute('data-collapse-label') || 'Mostrar menos')
                            : (expandBtn.getAttribute('data-expand-label') || 'Ver todas las categorías');
                    }
                    if (expandIcon) {
                        expandIcon.classList.toggle('rotate-180', isAll);
                    }
                }
            };

            sectorBtns.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const key = btn.getAttribute('data-cat-sector-btn') || 'principales';
                    applyCategorySector(key);
                });
            });

            if (expandBtn) {
                expandBtn.addEventListener('click', () => {
                    const isExpanded = expandBtn.getAttribute('data-expanded') === 'true';
                    applyCategorySector(isExpanded ? 'principales' : 'all');
                });
            }
        }

        // 5c. Home Hero Slider (autoplay synced with CSS progress, pause, arrows, dots, keyboard & swipe)
        const heroSlider = document.querySelector('[data-hero-slider]');

        if (heroSlider) {
            const slides = Array.from(heroSlider.querySelectorAll('[data-slide]'));
            const dots = Array.from(heroSlider.querySelectorAll('[data-slider-dot]'));
            const counter = heroSlider.querySelector('[data-slider-current]');
            let currentSlide = 0;

            const goToSlide = (index) => {
                currentSlide = (index + slides.length) % slides.length;

                slides.forEach((slide, i) => {
                    const isActive = i === currentSlide;
                    slide.classList.toggle('is-active', isActive);
                    slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                    slide.inert = !isActive;
                });

                dots.forEach((dot, i) => {
                    dot.classList.remove('is-active');
                    dot.setAttribute('aria-current', i === currentSlide ? 'true' : 'false');
                });

                const activeDot = dots[currentSlide];
                if (activeDot) {
                    void activeDot.offsetWidth;
                    activeDot.classList.add('is-active');
                }

                if (counter) {
                    counter.textContent = String(currentSlide + 1).padStart(2, '0');
                }
            };

            if (slides.length > 1) {
                dots.forEach((dot, i) => {
                    dot.addEventListener('click', () => goToSlide(i));
                    dot.querySelector('[data-slider-progress]')?.addEventListener('animationend', () => {
                        if (i === currentSlide) {
                            goToSlide(currentSlide + 1);
                        }
                    });
                });

                heroSlider.querySelector('[data-slider-prev]')?.addEventListener('click', () => goToSlide(currentSlide - 1));
                heroSlider.querySelector('[data-slider-next]')?.addEventListener('click', () => goToSlide(currentSlide + 1));

                const setPaused = (isPaused) => heroSlider.classList.toggle('is-paused', isPaused);
                heroSlider.addEventListener('mouseenter', () => setPaused(true));
                heroSlider.addEventListener('mouseleave', () => setPaused(false));
                heroSlider.addEventListener('focusin', () => setPaused(true));
                heroSlider.addEventListener('focusout', () => setPaused(false));
                document.addEventListener('visibilitychange', () => setPaused(document.hidden));

                heroSlider.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowLeft') goToSlide(currentSlide - 1);
                    if (e.key === 'ArrowRight') goToSlide(currentSlide + 1);
                });

                let touchStartX = null;
                heroSlider.addEventListener('touchstart', (e) => {
                    touchStartX = e.touches[0].clientX;
                }, { passive: true });
                heroSlider.addEventListener('touchend', (e) => {
                    if (touchStartX === null) return;
                    const deltaX = e.changedTouches[0].clientX - touchStartX;
                    if (Math.abs(deltaX) > 50) {
                        goToSlide(currentSlide + (deltaX < 0 ? 1 : -1));
                    }
                    touchStartX = null;
                }, { passive: true });

                goToSlide(0);
            }
        }

        // 6. Catalog Mobile Filter Drawer
        const filterDrawer = document.getElementById('catalog-filter-drawer');
        const filterOpenBtns = document.querySelectorAll('[data-filter-drawer-toggle]');
        const filterCloseBtns = document.querySelectorAll('[data-filter-drawer-close]');

        function setFilterDrawerOpen(isOpen) {
            if (!filterDrawer) return;
            filterDrawer.classList.toggle('hidden', !isOpen);
            document.body.classList.toggle('overflow-hidden', isOpen);
        }

        filterOpenBtns.forEach((btn) => btn.addEventListener('click', () => setFilterDrawerOpen(true)));
        filterCloseBtns.forEach((btn) => btn.addEventListener('click', () => setFilterDrawerOpen(false)));

        // 7. Product Detail Image Gallery Switcher
        const mainGalleryImg = document.querySelector('[data-gallery-main]');
        const galleryThumbs = document.querySelectorAll('[data-gallery-thumb]');

        if (mainGalleryImg && galleryThumbs.length > 0) {
            galleryThumbs.forEach((thumb) => {
                thumb.addEventListener('click', () => {
                    const targetSrc = thumb.getAttribute('data-gallery-src');
                    const targetAlt = thumb.getAttribute('data-gallery-alt');
                    if (targetSrc) {
                        mainGalleryImg.setAttribute('src', targetSrc);
                    }
                    if (targetAlt) {
                        mainGalleryImg.setAttribute('alt', targetAlt);
                    }
                    galleryThumbs.forEach((t) => {
                        t.classList.remove('ring-2', 'ring-blue-700', 'border-blue-700');
                        t.classList.add('border-slate-200');
                    });
                    thumb.classList.remove('border-slate-200');
                    thumb.classList.add('ring-2', 'ring-blue-700', 'border-blue-700');
                });
            });
        }

        // 7b. Interactive Variable Product Selector, Quantity Stepper, SKU Copy & B2B Matrix Counter
        const productDetailRoot = document.querySelector('[data-product-detail-root]');
        if (productDetailRoot) {
            // Copy SKU button
            const copySkuBtn = productDetailRoot.querySelector('[data-copy-sku-btn]');
            const copySkuText = productDetailRoot.querySelector('[data-copy-sku-text]');
            if (copySkuBtn) {
                copySkuBtn.addEventListener('click', async () => {
                    const skuVal = copySkuBtn.getAttribute('data-sku-value') || '';
                    if (!skuVal) return;
                    try {
                        await navigator.clipboard.writeText(skuVal);
                        if (copySkuText) copySkuText.textContent = '¡SKU Copiado!';
                        showToast(`Código SKU "${skuVal}" copiado al portapapeles.`, 'success', '', '');
                        setTimeout(() => {
                            if (copySkuText) copySkuText.textContent = 'Copiar SKU';
                        }, 2000);
                    } catch {
                        showToast('SKU: ' + skuVal, 'success', '', '');
                    }
                });
            }

            // Quantity Stepper & B2B Presets (+10, +50, +100)
            const qtyInput = document.getElementById('detail-quantity-input');
            if (qtyInput) {
                productDetailRoot.querySelectorAll('[data-qty-step]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const delta = parseInt(btn.getAttribute('data-qty-step') || '0', 10);
                        const current = parseInt(qtyInput.value || '1', 10) || 1;
                        qtyInput.value = String(Math.max(1, current + delta));
                    });
                });

                productDetailRoot.querySelectorAll('[data-qty-preset]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const addAmount = parseInt(btn.getAttribute('data-qty-preset') || '0', 10);
                        const current = parseInt(qtyInput.value || '0', 10) || 0;
                        qtyInput.value = String(Math.max(1, current === 1 ? addAmount : current + addAmount));
                    });
                });
            }

            // Variant Dropdown Switcher ([data-variant-select])
            const variantSelect = productDetailRoot.querySelector('[data-variant-select]');
            const quoteSubmitBtn = productDetailRoot.querySelector('[data-quote-submit-btn]');
            const quoteSubmitText = productDetailRoot.querySelector('[data-quote-submit-text]');
            const selectionHint = productDetailRoot.querySelector('[data-variant-selection-hint]');
            const selectionSummary = productDetailRoot.querySelector('[data-variant-selection-summary]');
            const summaryName = productDetailRoot.querySelector('[data-variant-summary-name]');
            const summarySku = productDetailRoot.querySelector('[data-variant-summary-sku]');
            const baseSkuEl = productDetailRoot.querySelector('[data-product-base-sku]');
            const baseSku = baseSkuEl ? baseSkuEl.textContent.trim() : '';

            function setDetailQuoteButtonEnabled(isEnabled) {
                if (!quoteSubmitBtn) return;
                quoteSubmitBtn.disabled = !isEnabled;
                quoteSubmitBtn.classList.toggle('bg-[#DC2626]', isEnabled);
                quoteSubmitBtn.classList.toggle('hover:bg-[#B91C1C]', isEnabled);
                quoteSubmitBtn.classList.toggle('text-white', isEnabled);
                quoteSubmitBtn.classList.toggle('shadow-md', isEnabled);
                quoteSubmitBtn.classList.toggle('cursor-pointer', isEnabled);
                quoteSubmitBtn.classList.toggle('bg-slate-200', !isEnabled);
                quoteSubmitBtn.classList.toggle('text-slate-500', !isEnabled);
                quoteSubmitBtn.classList.toggle('border', !isEnabled);
                quoteSubmitBtn.classList.toggle('border-slate-300', !isEnabled);
                quoteSubmitBtn.classList.toggle('cursor-not-allowed', !isEnabled);
                quoteSubmitBtn.classList.toggle('shadow-none', !isEnabled);

                if (quoteSubmitText) {
                    const enabledLabel = quoteSubmitBtn.getAttribute('data-enabled-label') || 'Añadir a Lista de Cotización';
                    const disabledLabel = quoteSubmitBtn.getAttribute('data-disabled-label') || 'Elige un producto para cotizar';
                    quoteSubmitText.textContent = isEnabled ? enabledLabel : disabledLabel;
                }
            }

            if (variantSelect) {
                variantSelect.addEventListener('change', () => {
                    const option = variantSelect.options[variantSelect.selectedIndex];
                    const variantId = option ? (option.value || '') : '';

                    if (!variantId || !option) {
                        document.querySelectorAll('input[data-active-variant-input]').forEach((input) => {
                            input.value = '';
                        });

                        if (baseSku) {
                            document.querySelectorAll('[data-product-active-sku]').forEach((el) => {
                                el.textContent = baseSku;
                            });
                            if (copySkuBtn) {
                                copySkuBtn.setAttribute('data-sku-value', baseSku);
                            }
                        }

                        productDetailRoot.querySelectorAll('[data-product-active-label]').forEach((el) => {
                            el.textContent = 'Elige un producto para cotizar';
                        });

                        const galleryBadge = productDetailRoot.querySelector('[data-gallery-variant-badge]');
                        if (galleryBadge) {
                            galleryBadge.textContent = '';
                            galleryBadge.classList.add('hidden');
                        }

                        const availEl = productDetailRoot.querySelector('[data-product-active-availability]');
                        if (availEl) {
                            const baseAvailLabel = availEl.getAttribute('data-base-availability-label');
                            const baseAvailClass = availEl.getAttribute('data-base-availability-class');
                            if (baseAvailLabel) availEl.textContent = baseAvailLabel;
                            if (baseAvailClass) availEl.className = `text-xs font-bold px-3 py-1 rounded-md border ${baseAvailClass}`;
                        }

                        if (selectionHint) {
                            selectionHint.classList.remove('hidden');
                            selectionHint.classList.add('flex');
                        }
                        if (selectionSummary) {
                            selectionSummary.classList.add('hidden');
                            selectionSummary.classList.remove('flex');
                        }

                        setDetailQuoteButtonEnabled(false);
                        return;
                    }

                    const sku = option.getAttribute('data-variant-sku') || '';
                    const label = option.getAttribute('data-variant-label') || '';
                    const name = option.getAttribute('data-variant-name') || '';
                    const dimensions = option.getAttribute('data-variant-dimensions') || '';
                    const material = option.getAttribute('data-variant-material') || '';
                    const unit = option.getAttribute('data-variant-unit') || '';
                    const packaging = option.getAttribute('data-variant-packaging') || '';
                    const price = option.getAttribute('data-variant-price') || '';
                    const availLabel = option.getAttribute('data-variant-availability-label') || '';
                    const availClass = option.getAttribute('data-variant-availability-class') || '';

                    document.querySelectorAll('[data-product-active-sku]').forEach((el) => {
                        el.textContent = sku;
                    });
                    if (copySkuBtn) {
                        copySkuBtn.setAttribute('data-sku-value', sku);
                    }

                    productDetailRoot.querySelectorAll('[data-product-active-label]').forEach((el) => {
                        el.textContent = label;
                    });
                    const galleryBadge = productDetailRoot.querySelector('[data-gallery-variant-badge]');
                    if (galleryBadge) {
                        galleryBadge.textContent = label;
                        galleryBadge.classList.remove('hidden');
                    }

                    productDetailRoot.querySelectorAll('[data-product-active-dimensions]').forEach((el) => {
                        el.textContent = dimensions;
                    });
                    productDetailRoot.querySelectorAll('[data-product-active-material]').forEach((el) => {
                        el.textContent = material;
                    });
                    productDetailRoot.querySelectorAll('[data-product-active-unit]').forEach((el) => {
                        el.textContent = unit;
                    });
                    document.querySelectorAll('[data-product-active-unit-label]').forEach((el) => {
                        el.textContent = unit;
                    });
                    productDetailRoot.querySelectorAll('[data-product-active-packaging]').forEach((el) => {
                        el.textContent = packaging;
                    });

                    if (price) {
                        productDetailRoot.querySelectorAll('[data-product-active-price]').forEach((el) => {
                            el.textContent = price;
                        });
                    }

                    const availEl = productDetailRoot.querySelector('[data-product-active-availability]');
                    if (availEl && availLabel) {
                        availEl.textContent = availLabel;
                        if (availClass) {
                            availEl.className = `text-xs font-bold px-3 py-1 rounded-md border ${availClass}`;
                        }
                    }

                    document.querySelectorAll('input[data-active-variant-input]').forEach((input) => {
                        input.value = variantId;
                    });

                    const qqName = document.querySelector('[data-quick-quote-product-name]');
                    if (qqName && name) {
                        qqName.textContent = name;
                    }

                    if (selectionHint) {
                        selectionHint.classList.add('hidden');
                        selectionHint.classList.remove('flex');
                    }
                    if (selectionSummary) {
                        selectionSummary.classList.remove('hidden');
                        selectionSummary.classList.add('flex');
                    }
                    if (summaryName) {
                        summaryName.textContent = label;
                    }
                    if (summarySku) {
                        summarySku.textContent = `SKU: ${sku}`;
                    }

                    setDetailQuoteButtonEnabled(true);

                    // Update browser URL and social share links with active variant SKU
                    if (sku && window.location) {
                        try {
                            const nextUrl = new URL(window.location.href);
                            nextUrl.searchParams.set('variant', sku);
                            if (window.history && typeof window.history.replaceState === 'function') {
                                window.history.replaceState({}, '', nextUrl.toString());
                            }
                            const fullShareUrl = nextUrl.toString();
                            const shareTitle = name || document.title;
                            const shareText = `${shareTitle} (Código SKU: ${sku}) — Import Hnos San Luis`;
                            const encUrl = encodeURIComponent(fullShareUrl);
                            const encText = encodeURIComponent(shareText);

                            document.querySelectorAll('[data-social-share-root]').forEach((root) => {
                                root.setAttribute('data-share-url', fullShareUrl);
                                const waLink = root.querySelector('[data-share-network="whatsapp"]');
                                if (waLink) waLink.href = `https://api.whatsapp.com/send?text=${encodeURIComponent(shareText + '\n' + fullShareUrl)}`;
                                const fbLink = root.querySelector('[data-share-network="facebook"]');
                                if (fbLink) fbLink.href = `https://www.facebook.com/sharer/sharer.php?u=${encUrl}`;
                                const twLink = root.querySelector('[data-share-network="twitter"]');
                                if (twLink) twLink.href = `https://twitter.com/intent/tweet?url=${encUrl}&text=${encText}`;
                                const tgLink = root.querySelector('[data-share-network="telegram"]');
                                if (tgLink) tgLink.href = `https://t.me/share/url?url=${encUrl}&text=${encText}`;
                                root.querySelectorAll('[data-share-copy-url]').forEach((copyBtn) => {
                                    copyBtn.setAttribute('data-share-copy-url', fullShareUrl);
                                });
                            });
                        } catch {
                            // Ignore URL construction errors
                        }
                    }
                });
            }

            // Multi-Variant Matrix Counter
            const matrixInputs = productDetailRoot.querySelectorAll('[data-matrix-qty-input]');
            const matrixSelectedCount = productDetailRoot.querySelector('[data-matrix-selected-count]');
            const matrixTotalUnits = productDetailRoot.querySelector('[data-matrix-total-units]');

            function updateMatrixSummary() {
                let selectedRows = 0;
                let totalUnits = 0;
                matrixInputs.forEach((inp) => {
                    const val = parseFloat(inp.value || '0') || 0;
                    if (val > 0) {
                        selectedRows++;
                        totalUnits += val;
                    }
                });
                if (matrixSelectedCount) matrixSelectedCount.textContent = String(selectedRows);
                if (matrixTotalUnits) matrixTotalUnits.textContent = String(totalUnits);
            }

            if (matrixInputs.length > 0) {
                matrixInputs.forEach((inp) => {
                    inp.addEventListener('input', updateMatrixSummary);
                    inp.addEventListener('change', updateMatrixSummary);
                });
            }
        }

        // 7b-card. Product Card Variant Selector (Catalog Grid / List / Home Cards)
        document.querySelectorAll('[data-card-variant-select]').forEach((select) => {
            select.addEventListener('change', () => {
                const card = select.closest('[data-product-card-root]');
                const option = select.options[select.selectedIndex];
                if (!card || !option) return;

                const hasValue = Boolean(option.value && option.value.trim() !== '');
                const sku = option.getAttribute('data-variant-sku') || '';
                const unit = option.getAttribute('data-variant-unit') || '';
                const packaging = option.getAttribute('data-variant-packaging') || '';

                card.querySelectorAll('[data-card-sku]').forEach((el) => {
                    if (sku) el.textContent = sku;
                });
                card.querySelectorAll('[data-card-unit]').forEach((el) => {
                    if (unit) el.textContent = unit;
                });
                card.querySelectorAll('[data-card-packaging]').forEach((el) => {
                    if (packaging) el.textContent = packaging;
                });

                const quoteBtn = card.querySelector('[data-card-quote-btn]');
                const quoteText = card.querySelector('[data-card-quote-text]');
                if (quoteBtn) {
                    quoteBtn.disabled = !hasValue;
                    quoteBtn.classList.toggle('bg-[#DC2626]', hasValue);
                    quoteBtn.classList.toggle('hover:bg-[#B91C1C]', hasValue);
                    quoteBtn.classList.toggle('text-white', hasValue);
                    quoteBtn.classList.toggle('shadow-2xs', hasValue);
                    quoteBtn.classList.toggle('cursor-pointer', hasValue);
                    quoteBtn.classList.toggle('bg-slate-200', !hasValue);
                    quoteBtn.classList.toggle('text-slate-500', !hasValue);
                    quoteBtn.classList.toggle('border', !hasValue);
                    quoteBtn.classList.toggle('border-slate-300', !hasValue);
                    quoteBtn.classList.toggle('cursor-not-allowed', !hasValue);
                    quoteBtn.classList.toggle('shadow-none', !hasValue);

                    if (quoteText) {
                        const enabledLabel = quoteBtn.getAttribute('data-enabled-label') || 'Cotizar';
                        const disabledLabel = quoteBtn.getAttribute('data-disabled-label') || 'Elige un producto para cotizar';
                        quoteText.textContent = hasValue ? enabledLabel : disabledLabel;
                    }
                }
            });
        });

        // 8. Quick Individual Quote Modal on Product Detail Page
        const quickQuoteModal = document.getElementById('quick-quote-modal');
        const quickQuoteOpenBtns = document.querySelectorAll('[data-quick-quote-open]');
        const quickQuoteCloseBtns = document.querySelectorAll('[data-quick-quote-close]');

        function setQuickQuoteModalOpen(isOpen) {
            if (!quickQuoteModal) return;
            quickQuoteModal.classList.toggle('hidden', !isOpen);
            document.body.classList.toggle('overflow-hidden', isOpen);
            if (isOpen) {
                const firstInput = quickQuoteModal.querySelector('input[name="contact_name"]');
                firstInput?.focus();
            }
        }

        quickQuoteOpenBtns.forEach((btn) => btn.addEventListener('click', () => setQuickQuoteModalOpen(true)));
        quickQuoteCloseBtns.forEach((btn) => btn.addEventListener('click', () => setQuickQuoteModalOpen(false)));

        // Close modals/drawers/dropdowns on Escape key
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setMobileMenuOpen(false);
                setFilterDrawerOpen(false);
                setQuickQuoteModalOpen(false);
                closeAllSearchDropdowns();
                if (catMenuPanel) {
                    catMenuPanel.classList.add('hidden');
                    catMenuToggle?.setAttribute('aria-expanded', 'false');
                }
            }
        });

        // 9. AJAX Add-to-Quote-Cart Enhancement (progressive enhancement over standard POST form)
        document.querySelectorAll('form[data-ajax-quote-form]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                if (form.getAttribute('data-requires-variant') === 'true') {
                    const variantField = form.querySelector('[name="variant_id"]');
                    if (!variantField || !variantField.value || variantField.value.trim() === '') {
                        event.preventDefault();
                        showToast('Elige un producto para cotizar.', 'error', '', '');
                        const selectEl = form.querySelector('[data-card-variant-select]') || document.querySelector('[data-variant-select]');
                        selectEl?.focus();
                        return;
                    }
                }

                if (!window.fetch) {
                    return;
                }
                event.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalHtml = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                }

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json',
                        },
                        body: formData,
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (typeof data.cart_count !== 'undefined') {
                            document.querySelectorAll('[data-quote-cart-count]').forEach((badge) => {
                                badge.textContent = String(data.cart_count);
                                badge.classList.remove('hidden');
                            });
                        }
                        if (form.hasAttribute('data-variant-matrix-form')) {
                            form.querySelectorAll('[data-matrix-qty-input]').forEach((inp) => {
                                inp.value = '0';
                                inp.dispatchEvent(new Event('change'));
                            });
                        }
                        showToast(data.message || 'Producto añadido a tu lista de cotización.', 'success', '/cotizacion', 'Ver Lista');
                    } else if (response.status === 422) {
                        const errData = await response.json().catch(() => ({}));
                        showToast(errData.message || 'Verifica las cantidades seleccionadas.', 'error', '', '');
                    } else {
                        form.submit();
                    }
                } catch {
                    form.submit();
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalHtml;
                    }
                }
            });
        });

        // 10. AJAX Add-to-Compare Enhancement (progressive enhancement over standard POST form)
        document.querySelectorAll('form[data-ajax-compare-form]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                if (!window.fetch) {
                    return;
                }
                event.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                }

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json',
                        },
                        body: formData,
                    });

                    const data = await response.json();

                    if (response.ok) {
                        if (typeof data.compare_count !== 'undefined') {
                            document.querySelectorAll('[data-compare-count]').forEach((badge) => {
                                badge.textContent = String(data.compare_count);
                            });
                        }
                        if (submitBtn) {
                            submitBtn.classList.remove('bg-slate-50', 'text-slate-700', 'border-slate-200', 'border-slate-300');
                            submitBtn.classList.add('bg-[#1E3A8A]', 'text-white', 'border-[#1E3A8A]');
                        }
                        showToast(
                            data.message || 'Producto añadido al comparador técnico.',
                            'success',
                            data.compare_url || '/catalogo/comparar',
                            'Comparar Ahora'
                        );
                    } else {
                        showToast(
                            data.message || 'Puedes comparar hasta un máximo de 4 productos.',
                            'error',
                            data.compare_url || '/catalogo/comparar',
                            'Ver Comparador'
                        );
                    }
                } catch {
                    form.submit();
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                }
            });
        });

        // 11. Copy Shareable Comparison & Product URL Buttons
        document.querySelectorAll('[data-copy-share-url]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const shareUrl = btn.getAttribute('data-copy-share-url');
                if (!shareUrl) return;
                try {
                    await navigator.clipboard.writeText(shareUrl);
                    showToast('Enlace de comparación técnica copiado al portapapeles.', 'success', '', '');
                } catch {
                    showToast('URL: ' + shareUrl, 'success', '', '');
                }
            });
        });

        document.querySelectorAll('[data-share-copy-url]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const shareUrl = btn.getAttribute('data-share-copy-url') || window.location.href;
                const labelEl = btn.querySelector('[data-share-copy-label]');
                const originalLabel = labelEl ? labelEl.textContent : 'Copiar enlace';
                try {
                    await navigator.clipboard.writeText(shareUrl);
                    if (labelEl) {
                        labelEl.textContent = '¡Copiado!';
                        setTimeout(() => {
                            labelEl.textContent = originalLabel;
                        }, 2000);
                    }
                    showToast('Enlace de la ficha técnica copiado al portapapeles.', 'success', '', '');
                } catch {
                    showToast('Enlace: ' + shareUrl, 'success', '', '');
                }
            });
        });

        // 12. Cookie Consent Banner (Ley N° 29733 & Cookie Policy)
        const cookieBanner = document.getElementById('sanluis-cookie-banner');
        if (cookieBanner) {
            try {
                const consent = localStorage.getItem('sanluis_cookie_consent');
                if (!consent) {
                    cookieBanner.classList.remove('hidden');
                }
            } catch {
                cookieBanner.classList.remove('hidden');
            }

            cookieBanner.querySelectorAll('[data-cookie-choice]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const choice = btn.getAttribute('data-cookie-choice') || 'essential';
                    try {
                        localStorage.setItem('sanluis_cookie_consent', choice);
                    } catch {
                        // Ignore storage errors in private mode
                    }
                    cookieBanner.classList.add('hidden');
                });
            });
        }
    });
})();
