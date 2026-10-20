@extends('layouts.admin')

@section('title', 'Nuevo Producto')
@section('header', 'Crear Nuevo Producto Técnico')
@section('subheader', 'Registre un nuevo ítem en el catálogo con su código SKU único y especificaciones técnicas.')

@section('content')
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.products._form')
    </form>
@endsection
