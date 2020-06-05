<template>
    <form method="POST" :action="url" @submit="validate" enctype="multipart/form-data">

        <slot></slot>

        <div class="card mb-2">

            <div class="card-header clearfix">
                <h4 v-if="_id" class="card-title float-left"><i class="mdi mdi-pencil"></i> Edytowanie sekcji</h4>
                <h4 v-else class="card-title float-left"><i class="mdi mdi-plus"></i> Dodawanie nowej sekcji</h4>
                <div class="float-right">
                    <button type="submit" class="btn btn-primary">Zapisz</button>
                </div>
            </div>

            <div class="card-body">

                <div class="row">
                    <div class="form-group col-md-6">
                        <label title="Służy do indentyfikacji sekcji a także pokazuje się przy edytowaniu strony w Module Stron - rozdział Sekcji">
                            Nazwa <i class="mdi mdi-help-circle"></i>
                        </label>
                        <input type="text" :class="getInputClass('name')" name="name" v-model.lazy="name">
                        <small v-if="hasError('name')" class="error mt-2 text-danger">{{ errors.name[0] }}</small>
                    </div>

                    <div class="form-group col-md-6">
                        <label title="To jest nazwa szablonu. Szablon z taką nazwą musi być w resources->views">
                            Szablon <i class="mdi mdi-help-circle"></i>
                        </label>
                        <input type="text" :class="getInputClass('template')" name="template" v-model.lazy="template">
                        <small v-if="hasError('template')" class="error mt-2 text-danger">{{ errors.template[0] }}</small>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-12">
                        <b-form-checkbox v-model.lazy="is_gallery" name="check-button" switch>
                            Galeria
                        </b-form-checkbox>
                    </div>
                </div>

                <div v-if="!is_gallery" class="row">

                    <div class="form-group col-md-6">
                        <label title="Typ stron które będą pokazywały się w Sekcji">
                            Typ <i class="mdi mdi-help-circle"></i>
                        </label>
                        <multiselect v-model.lazy="type" track-by="template" label="name" placeholder="Wybierz typ" :options="types"></multiselect>
                    </div>

                    <div class="form-group col-md-6">
                        <label title="Iłość stron w Sekcji">
                            Iłość <i class="mdi mdi-help-circle"></i>
                        </label>
                        <input type="text" class="form-control" name="count" v-model.lazy="count">
                    </div>
                </div>

            </div>
        </div>
    </form>
</template>

<script>

    export default {
        name: 'edit-section',
        props : ['_id'],

        data() {
            return {
                name: '',
                template: '',
                type: '',
                count: 0,
                is_gallery: false,
                errors: {
                    name: {},
                    template: {}
                },
                types: [],
            };
        },

        created() {
            this.getTypes();
        },

        computed: {

            url: function () {
                return this._id ? ('/dashboard/pages-sections/' + this._id) : '/dashboard/pages-sections/store';
            }
        },

        methods: {

            hasError(key) {
                return this.errors[key].length > 0;
            },

            getInputClass(key) {
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

            getItem: function(arr, key, val) {

                let item = val;

                arr.forEach(it => {
                    if (it[key] === val) {
                        item = it;
                    }
                });

                return item;
            },

            getTypes: function() {
                axios.get('/dashboard/pages-types/get')
                    .then(res => {
                        this.types = res.data;
                        this.type  = this.getItem(this.types, 'template', 'main');
                        this.getSection();
                    }).catch(err => {
                    console.log(err)
                })
            },

            getSection() {
                let self = this;
                if (self._id) {
                    axios.get('/dashboard/pages-sections/get?id=' + self._id)
                        .then(res => {
                            self.id         = res.data.id;
                            self.name       = res.data.name;
                            self.template   = res.data.template;
                            self.count      = res.data.count;
                            self.type       = self.getItem(self.types, 'template', res.data.type);
                            if (res.data.is_gallery != null) {
                                self.is_gallery = res.data.is_gallery === 'true';
                            }

                        }).catch(err => {
                            console.log(err)
                    })
                }
            },

            validate: function(e) {
                e.preventDefault();

                if (!this.name || !this.template) {
                    return false;
                } else {
                    let formData = new FormData();
                    formData.append('_method', this.id ? 'PUT' : 'POST');
                    formData.append('name', this.name);
                    formData.append('template', this.template);
                    formData.append('count', this.count);
                    formData.append('type', this.type.template);
                    formData.append('is_gallery', this.is_gallery);

                    axios.post(this.url, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    })
                        .then(res => {
                            window.location = res.data.redirect;
                        }).catch(err => {
                        console.log(err);
                    });
                }
            }
        },

        watch: {
            name() {
                if (!this.name) {
                    this.errors.name = ['To pole jest wymagane'];
                }
            },

            template() {
                if (!this.template) {
                    this.errors.template = ['To pole jest wymagane'];
                }
            }
        }
    }
</script>
