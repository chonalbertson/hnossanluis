@props([
    'url' => url()->current(),
    'title' => '',
    'sku' => '',
    'description' => '',
    'variant' => 'default', // 'default' | 'compact'
])

@php
    $cleanTitle = trim(strip_tags((string) $title));
    $cleanSku = trim((string) $sku);
    $shareText = $cleanSku !== ''
        ? "{$cleanTitle} (Código SKU: {$cleanSku}) — Import Hnos San Luis"
        : "{$cleanTitle} — Import Hnos San Luis";

    $encodedUrl = rawurlencode((string) $url);
    $encodedText = rawurlencode($shareText);
    $encodedWhatsapp = rawurlencode("{$shareText}\n{$url}");

    $whatsappShareUrl = "https://api.whatsapp.com/send?text={$encodedWhatsapp}";
    $facebookShareUrl = "https://www.facebook.com/sharer/sharer.php?u={$encodedUrl}";
    $instagramShareUrl = 'https://www.instagram.com/';
    $twitterShareUrl = "https://twitter.com/intent/tweet?url={$encodedUrl}&text={$encodedText}";
    $telegramShareUrl = "https://t.me/share/url?url={$encodedUrl}&text={$encodedText}";
@endphp

<div data-social-share-root
     data-share-base-title="{{ $cleanTitle }}"
     data-share-base-sku="{{ $cleanSku }}"
     data-share-url="{{ $url }}"
     {{ $attributes->merge(['class' => $variant === 'compact' ? 'flex flex-wrap items-center justify-between gap-3' : 'rounded-xl bg-slate-50/90 border border-slate-200/90 p-4']) }}>

    <div class="flex items-center gap-2">
        <span class="w-7 h-7 rounded-lg bg-[#1E3A8A]/10 text-[#1E3A8A] flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
        </span>
        <div>
            <span class="text-xs font-extrabold uppercase tracking-wider text-[#0F172A] block">
                Compartir Ficha por Redes Sociales
            </span>
            @if($variant !== 'compact')
                <span class="text-[11px] text-slate-500 block">
                    Envía este producto con su código SKU a tu equipo de compras, obra o clientes.
                </span>
            @endif
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 {{ $variant !== 'compact' ? 'mt-3 pt-3 border-t border-slate-200/80' : '' }}" role="group" aria-label="Botones para compartir en redes sociales">
        {{-- WhatsApp --}}
        <a href="{{ $whatsappShareUrl }}"
           target="_blank"
           rel="noopener noreferrer"
           data-share-network="whatsapp"
           data-ga-event="share"
           data-ga-params='@json(["method" => "whatsapp", "content_type" => "product", "item_id" => $cleanSku])'
           title="Compartir por WhatsApp"
           aria-label="Compartir por WhatsApp"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-[#22C55E] hover:bg-[#16A34A] text-white text-xs font-bold shadow-2xs transition-all hover:-translate-y-0.5">
            <x-icons.whatsapp class="w-4 h-4 shrink-0" />
            <span>WhatsApp</span>
        </a>

        {{-- Facebook --}}
        <a href="{{ $facebookShareUrl }}"
           target="_blank"
           rel="noopener noreferrer"
           data-share-network="facebook"
           data-ga-event="share"
           data-ga-params='@json(["method" => "facebook", "content_type" => "product", "item_id" => $cleanSku])'
           title="Compartir en Facebook"
           aria-label="Compartir en Facebook"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-[#1877F2] hover:bg-[#166FE5] text-white text-xs font-bold shadow-2xs transition-all hover:-translate-y-0.5">
            <svg class="w-4 h-4 shrink-0 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
            <span>Facebook</span>
        </a>

        {{-- Instagram --}}
        <a href="{{ $instagramShareUrl }}"
           target="_blank"
           rel="noopener noreferrer"
           data-share-network="instagram"
           data-share-copy-url="{{ $url }}"
           data-ga-event="share"
           data-ga-params='@json(["method" => "instagram", "content_type" => "product", "item_id" => $cleanSku])'
           title="Compartir en Instagram"
           aria-label="Compartir en Instagram"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-gradient-to-r from-[#833AB4] via-[#E1306C] to-[#F77737] hover:opacity-95 text-white text-xs font-bold shadow-2xs transition-all hover:-translate-y-0.5">
            <svg class="w-4 h-4 shrink-0 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
            </svg>
            <span>Instagram</span>
        </a>

        {{-- X (Twitter) --}}
        <a href="{{ $twitterShareUrl }}"
           target="_blank"
           rel="noopener noreferrer"
           data-share-network="twitter"
           data-ga-event="share"
           data-ga-params='@json(["method" => "twitter", "content_type" => "product", "item_id" => $cleanSku])'
           title="Compartir en X (Twitter)"
           aria-label="Compartir en X (Twitter)"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-[#0F172A] hover:bg-slate-800 text-white text-xs font-bold shadow-2xs transition-all hover:-translate-y-0.5">
            <svg class="w-3.5 h-3.5 shrink-0 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
            </svg>
            <span>X</span>
        </a>

        {{-- Telegram --}}
        <a href="{{ $telegramShareUrl }}"
           target="_blank"
           rel="noopener noreferrer"
           data-share-network="telegram"
           data-ga-event="share"
           data-ga-params='@json(["method" => "telegram", "content_type" => "product", "item_id" => $cleanSku])'
           title="Compartir en Telegram"
           aria-label="Compartir en Telegram"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-[#0284C7] hover:bg-[#0369A1] text-white text-xs font-bold shadow-2xs transition-all hover:-translate-y-0.5">
            <svg class="w-4 h-4 shrink-0 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
            </svg>
            <span>Telegram</span>
        </a>

        {{-- Copiar enlace --}}
        <button type="button"
                data-share-copy-url="{{ $url }}"
                data-ga-event="share"
                data-ga-params='@json(["method" => "copy_link", "content_type" => "product", "item_id" => $cleanSku])'
                title="Copiar enlace directo del producto"
                aria-label="Copiar enlace directo del producto"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white hover:bg-blue-50 text-[#1E3A8A] border border-blue-200 hover:border-blue-400 text-xs font-bold shadow-2xs transition-all hover:-translate-y-0.5 cursor-pointer">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
            <span data-share-copy-label>Copiar enlace</span>
        </button>
    </div>
</div>
