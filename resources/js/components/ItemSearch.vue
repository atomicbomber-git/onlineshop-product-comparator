<template>
    <div>
        <div class="alert alert-info" v-for="(source, key) in loading_sources" :key="key">
            <strong>
                <i class="fa fa-spinner fa-spin fa-fw"></i>
                Melakukan pencarian di {{ source.name }}...
            </strong>
        </div>

        <div v-for="product in sorted_products" :key="product.id" class="card mb-4 mr-3 d-inline-block" style="width: 20rem;">
            <img class="card-img-top" :src="product.img_url" style="height: 200px; width: 200px; object-fit: cover" alt="Card image cap">
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
                                <star-rating :star-size="20" :rating="product.rating" :read-only="true"> </star-rating>
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

            Object.keys(this.used_sources).forEach(key => {
                this.used_sources[key].is_loading = true

                axios.get(`/recommendation/search/${key}`, { params: { keyword: keyword } })
                    .then(response => {
                        this.products = [...this.products, ...response.data] 
                        this.used_sources[key].is_loading = false
                    })
                    .catch(error => {
                        alert(error)
                        this.used_sources[key].is_loading = false
                    })
            })
        },

        data() {
            return {
                
                sources: {
                    'bukalapak': { 'name': 'Bukalapak', 'is_loading': false, 'used': window.bukalapak },
                    'elevenia': { 'name': 'Elevenia', 'is_loading': false, 'used': window.elevenia },
                    'jdid': { 'name': 'JD.id', 'is_loading': false, 'used': window.jdid }
                },

                products: []
            }
        },

        computed: {
            keyword() {
                return window.keyword
            },

            used_sources() {
                return _.pickBy(this.sources, source => source.used)
            },

            loading_sources() {
                return _.filter(this.sources, source => source.is_loading)
            },

            sorted_products() {
                return this.products.sort((a, b) => b.sales - a.sales)
            }
        }
    }
</script>
