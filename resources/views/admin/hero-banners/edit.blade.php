@extends('layouts.admin')

@section('title', 'Editar Banner de Slider')
@section('header', 'Editar Banner: ' . $banner->label)
@section('subheader', 'Actualice la imagen de fondo (1920 x 720 px recomendado), titulares y botones de acción.')

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
        <form method="POST" action="{{ route('admin.hero-banners.update', $banner) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('admin.hero-banners._form')

            <div class="flex items-center gap-3 pt-6 mt-6 border-t border-slate-200 text-xs">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 cursor-pointer">
                    Actualizar Banner
                </button>
                <a href="{{ route('admin.hero-banners.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection
