@extends('shared.layout')
@section('title', 'Halaman Utama')
@section('content')
<div class="container my-5">
    <h1 class='mb-5'>
        <i class='fa fa-list'></i>
        Halaman Utama
    </h1>

    <div class="card">
        <div class="card-header">
            <i class="fa fa-search"></i>
            Pencarian Produk
        </div>
        <div class="card-body">
           <form action="{{ route('recommendation.search') }}" method="GET">
                <div class='form-group'>
                    <label for='keyword'> Kata Kunci: </label>
                
                    <input
                        id='keyword' name='keyword' type='text'
                        placeholder='Kata Kunci'
                        value='{{ old('keyword') }}'
                        class='form-control {{ !$errors->has('keyword') ?: 'is-invalid' }}'>
                
                    <div class='invalid-feedback'>
                        {{ $errors->first('keyword') }}
                    </div>
                </div>
        
                <div class="form-group text-right">
                    <button class="btn btn-primary">
                        Cari Produk
                        <i class="fa fa-search"></i>
                    </button>
                </div>
           </form>
        </div>
    </div>
</div>
@endsection