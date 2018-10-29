@extends('shared.layout')
@section('title', 'Hasil Pencarian Produk')
@section('content')
<div class="container my-5">
    <h1 class='mb-5'>
        <i class='fa fa-icon'></i>
        Hasil Pencarian Produk
    </h1>

    @foreach ($products as $product)
    <div class="card mb-4" style="width: 18rem;">
        <img class="card-img-top" src="{{ $product['img_url'] }}" alt="Card image cap">
        <div class="card-body">
            <h5 class="card-title"> {{ $product['name'] }} </h5>
            <p class="card-text">
                <dl>
                    <dt> Harga: </dt> <dd> {{ $product['price'] }} </dd>
                    <dt> T. Penjualan: </dt> <dd> {{ $product['sales_count'] }} </dd>
                </dl>
            </p>
        </div>
    </div>
    @endforeach

</div>
@endsection