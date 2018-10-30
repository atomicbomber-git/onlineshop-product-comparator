@extends('shared.layout')
@section('title', 'Hasil Pencarian Produk')
@section('content')
<div class="container my-5">
    <h1 class='mb-5'>
        <i class='fa fa-search'></i>
        Hasil Pencarian Produk
    </h1>

    <div id="app">
        <item-search/>
    </div>
</div>
@endsection