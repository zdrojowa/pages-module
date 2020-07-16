<template>
    <div>
        <slot></slot>

        <b-nav align="right">
            <b-nav-item>
                <b-button type="button" variant="primary" @click="save">Zapisz</b-button>
            </b-nav-item>
        </b-nav>

        <div class="row">

            <div class="col-md-6">
                <div class="form-group">
                    <label>Nazwa</label>
                    <input type="text" :class="getInputClass('name')" name="name" placeholder="Wpisz nazwe" v-model.lazy="name">
                    <small v-if="hasError('name')" class="error mt-2 text-danger">{{ errors.name[0] }}</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <b-form-group label="Ikonka">
                        <media-selector extensions="svg" @media-selected="select"></media-selector>
                    </b-form-group>

                    <b-img v-if="url" thumbnail fluid :src="url"></b-img>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    export default {
        name: 'icon',
        props : ['_id'],

        data() {
            return {
                id: 0,
                url: '',
                name: '',
                errors: {
                    name: {}
                }
            };
        },

        created() {
            this.getIcon();
        },

        computed: {

            urlTo() {
                return this.id ? ('/dashboard/pages-icons/' + this.id) : '/dashboard/pages-icons/store';
            }
        },

        methods: {
            select: function(url) {
                this.url = url;
            },

            hasError: function(key) {
                return this.errors[key].length > 0;
            },

            getInputClass: function(key) {
                let className = 'form-control ';
                if (this.hasError(key)) {
                    className += 'is-invalid';
                } else {
                    if (this[key]) {
                        className += 'is-valid';
                    }
                }
                return className;
            },

            getIcon: function() {
                let self = this;
                if (self._id) {
                    axios.get('/api/pages-icons?id=' + self._id)
                        .then(res => {
                            self.id    = res.data.id;
                            self.name  = res.data.name;
                            self.url   = res.data.url;
                        }).catch(err => {
                        console.log(err)
                    })
                }
            },

            validate: function(e) {
                if (this.name) {
                    this.errors.name = {};
                    return true;
                } else {
                    this.errors.name = ['To pole jest wymagane'];
                }
                return false;
            },

            save: function(e) {
                e.preventDefault();

                if (this.validate) {
                    let formData = new FormData();
                    formData.append('_method', this.id ? 'PUT' : 'POST');
                    formData.append('name', this.name);
                    formData.append('url', this.url);

                    axios.post(this.urlTo, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    })
                        .then(res => {
                            window.location = res.data.redirect;
                        }).catch(err => {
                        console.log(err);
                    });
                } else {
                    return false;
                }
            }
        },

        watch: {
            name() {
                this.validate();
            }
        }
    }
</script>
