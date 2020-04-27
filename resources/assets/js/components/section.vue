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
                        <label title="Służy do indentyfikacji sekcji a także będzie w nazwie sekcji na stronie, pokazuje się przy edytowaniu strony w Module Stron - rozdział Sekcji">
                            Nazwa <i class="mdi mdi-help-circle"></i>
                        </label>
                        <input type="text" class="form-control" name="name" v-model.lazy="name">
                    </div>

                    <div class="form-group col-md-6">
                        <label title="Etykieta sekcji (label)">
                            Etykieta <i class="mdi mdi-help-circle"></i>
                        </label>
                        <input type="text" class="form-control" name="label" v-model.lazy="label">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Język</label>
                        <input type="text" class="form-control" name="lang" v-model.lazy="lang">
                    </div>

                    <div class="form-group col-md-6">
                        <label title="To jest nazwa szablonu. Szablon z taką nazwą musi być w resources->views">
                            Szablon <i class="mdi mdi-help-circle"></i>
                        </label>
                        <input type="text" class="form-control" name="template" v-model.lazy="template">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label title="Typ stron które będą pokazywały sie w Sekcji">
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
        name: 'type',
        props : ['_id'],

        data() {
            return {
                name: '',
                template: '',
                table: '',
                text: '',
                errors: {
                    name: {},
                    template: {},
                    table: {},
                }
            };
        },

        created() {
            this.getType();
        },

        computed: {

            url: function () {
                return this._id ? ('/dashboard/pages-types/' + this._id) : '/dashboard/pages-types/store';
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

            getType() {
                let self = this;
                if (self._id) {
                    axios.get('/dashboard/pages-types/get?id=' + self._id)
                        .then(res => {
                            self.id       = res.data.id;
                            self.name     = res.data.name;
                            self.template = res.data.template;
                            self.table    = res.data.table;
                            self.text     = res.data.text;

                        }).catch(err => {
                            console.log(err)
                    })
                }
            },

            validate: function(e) {

                e.preventDefault();

                if (!this.name) {
                    this.errors.name = ['To pole jest wymagane'];
                    return false;
                } else {
                    if (!this.template) {
                        this.errors.template = ['To pole jest wymagane'];
                        return false;
                    } else {
                        let formData = new FormData();
                        formData.append('_method', this._id ? 'PUT' : 'POST');
                        formData.append('name', this.name);
                        formData.append('template', this.template);
                        formData.append('table', this.table);
                        formData.append('text', this.text);

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

            check(key) {
                axios.get('/dashboard/pages-types/check/' + this._id + '?' + key + '=' + this[key])
                    .then(res => {
                        if (res.data) {
                            this.errors[key] = [];
                        } else {
                            if (key == 'table') {
                                this.errors[key] = ['Nie ma takiej tablicy'];
                            } else {
                                this.errors[key] = ['To pole musi być unikalne'];
                            }
                        }
                    }).catch(err => {
                        console.log(err)
                })
            }
        },

        watch: {
            name() {
                if (!this.name) {
                    this.errors.name = ['To pole jest wymagane'];
                } else {
                    this.check('name');
                }
            },

            template() {
                if (!this.template) {
                    this.errors.template = ['To pole jest wymagane'];
                } else {
                    this.check('template');
                }
            },

            table() {
                if (this.table) {
                    this.check('table');
                }
            }
        }
    }
</script>
