@props(['items' => []])

@if(!empty($items))
<nav aria-label="Ruta de navegación" class="mb-5">
    <ol class="flex flex-wrap items-center gap-1.5 text-xs text-slate-600">
        @foreach($items as $idx => $item)
            <li class="flex items-center gap-1.5">
                @if($idx > 0)
                    <span class="text-slate-400" aria-hidden="true">/</span>
                @endif
                @if($idx < count($items) - 1 && !empty($item['url']))
                    <a href="{{ $item['url'] }}" class="hover:text-[#1D4ED8] hover:underline font-medium">
                        {{ $item['name'] }}
                    </a>
                @else
                    <span class="font-semibold text-slate-900 line-clamp-1" aria-current="page">
                        {{ $item['name'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif
