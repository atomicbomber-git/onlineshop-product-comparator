<template>
    <div>
        <div class="alert alert-info" v-if="is_loading">
            <strong>
                <i class="fa fa-spinner fa-spin fa-fw"></i>
                Melakukan pencarian...
            </strong>
        </div>

        <div v-for="product in products" :key="product.id" class="card mb-4 mr-3 d-inline-block" style="width: 20rem;">
            <img class="card-img-top" :src="product.img_url" alt="Card image cap">
            <div class="card-body">
                <h5 class="card-title"> </h5>
                <div class="card-text">
                    <h5 class="card-title"> {{ product.short_name }} </h5>
                    <h6 class="card-subtitle mb-2 text-muted"> {{ product.source }} </h6>
                    <div class="row mb-3">
                        <div class="col">
                            <dt> Harga: </dt> <dd> {{ product.price }} </dd>
                            <dt> Terjual: </dt> <dd> {{ product.sales }} </dd>
                        </div>
                        <div class="col">
                            <dt> Rating: </dt>
                            <dd>
                                <star-rating star-size="20" :rating="product.rating" :read-only="true"> </star-rating>
                            </dd>
                        </div>
                    </div>
                    <a :href="product.url" class="btn btn-primary"> Detail </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            // Load from all
            this.is_loading = true

            axios.get('/recommendation/search/all', { params: { keyword: keyword } })
                .then(response => {
                    this.products = Object.keys(response.data).map(key => { return { ...response.data[key], id: key } }).sort((a, b) => b.sales - a.sales)
                    this.is_loading = false
                })
                .catch(error => {
                    alert(error)
                    this.is_loading = false
                })
        },

        data() {
            return {
                is_loading: false,
                products: []
            }
        },

        computed: {
            keyword() {
                return window.keyword
            }
        }
    }
</script>
