@extends('shared.layout')
@section('title', 'Halaman Utama')
@section('content')
<div class="container my-5">
    <img src="{{ asset('png/logo_large.png') }}" alt="logo">

    <div class="card mt-5">
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

                <div class="form-group d-inline-block mr-3">
                    <div class="custom-control custom-checkbox">
                        <input {{ request('bukalapak') == 'on' ? 'checked' : '' }} name="bukalapak" type="checkbox" class="custom-control-input" id="bukalapak">
                        <label class="custom-control-label" for="bukalapak"> Bukalapak </label>
                    </div>
                </div>

                <div class="form-group d-inline-block mr-3">
                    <div class="custom-control custom-checkbox">
                        <input {{ request('jdid') == 'on' ? 'checked' : '' }} name="jdid" type="checkbox" class="custom-control-input" id="jdid">
                        <label class="custom-control-label" for="jdid"> JD.id </label>
                    </div>
                </div>

                <div class="form-group d-inline-block mr-3">
                    <div class="custom-control custom-checkbox">
                        <input {{ request('elevenia') == 'on' ? 'checked' : '' }} name="elevenia" type="checkbox" class="custom-control-input" id="elevenia">
                        <label class="custom-control-label" for="elevenia"> Elevenia </label>
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