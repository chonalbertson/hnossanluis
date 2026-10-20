@extends('layouts.admin')

@section('title', 'Testimonios Verificados')
@section('header', '14. Gestión de Testimonios con Autorización Verificada')
@section('subheader', 'Solo se muestran en la web pública aquellos testimonios que cuenten con autorización verificada del cliente.')

@section('content')
    <div class="flex items-center justify-between">
        <p class="text-xs text-slate-600">Testimonios registrados: <strong>{{ $testimonials->total() }}</strong></p>
        <a href="{{ route('admin.testimonials.create') }}" class="px-4 py-2 rounded-lg bg-amber-500 text-slate-950 font-bold text-xs hover:bg-amber-400">
            + Registrar Testimonio
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Autor / Empresa</th>
                        <th class="px-4 py-3 text-left">Testimonio</th>
                        <th class="px-4 py-3 text-center">Autorización</th>
                        <th class="px-4 py-3 text-center">Visible en Web</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($testimonials as $t)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $t->author_name }}</div>
                                <div class="text-slate-500">{{ $t->author_role }} {{ $t->company_name ? '— ' . $t->company_name : '' }}</div>
                            </td>
                            <td class="px-4 py-3 max-w-md">
                                <p class="text-slate-700 line-clamp-2">"{{ $t->content }}"</p>
                                @if($t->authorization_reference)
                                    <span class="text-[10px] text-slate-400">Ref: {{ $t->authorization_reference }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $t->is_verified ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $t->is_verified ? 'Autorizado' : 'Sin verificar' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $t->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $t->is_active ? 'Publicado' : 'Oculto' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <form method="POST" action="{{ route('admin.testimonials.toggle', $t) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2.5 py-1 rounded border border-slate-300 text-slate-700 hover:bg-slate-50 cursor-pointer">
                                            {{ $t->is_active ? 'Ocultar' : 'Publicar' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.testimonials.edit', $t) }}" class="px-2.5 py-1 rounded bg-slate-900 text-white hover:bg-slate-800 font-semibold">Editar</a>
                                    <form method="POST" action="{{ route('admin.testimonials.destroy', $t) }}" onsubmit="return confirm('¿Eliminar testimonio?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 rounded border border-rose-200 text-rose-700 hover:bg-rose-50 cursor-pointer">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                No hay testimonios registrados. El bloque de testimonios permanece oculto en la web mientras no existan testimonios verificados activos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
