<template>
    <div>
        <slot></slot>

        <b-nav align="right">
            <b-nav-item>
                <b-button type="bytton" variant="primary" @click="save">Zapisz</b-button>
            </b-nav-item>
        </b-nav>

        <div class="row">

            <div class="form-group col-sm-12">
                <label>Meta description</label>
                <textarea rows="10" class="form-control" name="meta_description" placeholder="Wpisz meta description" v-model.lazy="meta_description"/>
            </div>
        </div>
    </div>
</template>

<script>

    export default {
        name: 'seo',
        props: {
            id: {
                required: true,
                type: String
            }
        },

        data() {
            return {
                meta_description: ''
            };
        },

        created() {
            this.getSeo();
        },

        computed: {

            url: function () {
                return '/dashboard/pages/' + this.id;
            }
        },

        methods: {

            getSeo() {
                let self = this;
                axios.get('/dashboard/pages/get?id=' + self.id)
                    .then(res => {
                        if (res.data.meta_description == null) {
                            self.meta_description = '';
                        } else {
                            self.meta_description = res.data.meta_description;
                        }
                    }).catch(err => {
                        console.log(err)
                })
            },

            save: function() {
                let self = this;

                let formData = new FormData();
                formData.append('_method', 'PUT');
                formData.append('meta_description', this.meta_description);

                axios.post(this.url, formData).then(res => {
                    this.$bvToast.toast('SEO zaktualizowane', {
                        title: `Pakiety`,
                        variant: 'success',
                        solid: true
                    })
                }).catch(err => {
                    this.$bvToast.toast(err, {
                        title: `Błąd`,
                        variant: 'danger',
                        solid: true
                    })
                });
            }
        }
    }
</script>
