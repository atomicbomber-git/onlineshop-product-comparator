<template>
    <div>
        <div class="alert alert-info">
            
        </div>

        <div v-for="product in products" :key="product.id" class="card mb-4 mr-3 d-inline-block" style="width: 20rem;">
            <img class="card-img-top" :src="product.img_url" alt="Card image cap">
            <div class="card-body">
                <h5 class="card-title"> </h5>
                <div class="card-text">
                    <dl>
                        <dt> Sumber: </dt> <dd> <strong> {{ product.source }} </strong> </dd>
                        <dt> Harga: </dt> <dd> {{ product.price }} </dd>
                        <dt> Terjual: </dt> <dd> {{ product.sales }} </dd>
                        <dt> Rating: </dt> <dd> {{ product.rating }} </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            // Load from Shopee
            axios.get('http://opc.com/recommendation/search/shopee', { params: { keyword: "komputer" } })
                .then(response => {
                    this.products = response.data
                })
                .catch(error => {
                    alert(error)
                })
        },

        data() {
            return {
                loading_infos: {
                    'shopee': false,
                    'bukalapak': false
                },
                products: []
            }
        }
    }
</script>
