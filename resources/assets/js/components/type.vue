<template>
    <form method="POST" :action="url" @submit="validate" enctype="multipart/form-data">

        <slot></slot>

        <div class="card mb-2">

            <div class="card-header clearfix">
                <h4 v-if="_id" class="card-title float-left"><i class="mdi mdi-pencil"></i> Edytowanie typu</h4>
                <h4 v-else class="card-title float-left"><i class="mdi mdi-plus"></i> Dodawanie nowego typu</h4>
                <div class="float-right">
                    <button type="submit" class="btn btn-primary">Zapisz</button>
                </div>
            </div>

            <div class="card-body">

                <div class="row">
                    <div class="form-group col-md-4">
                        <label title="Służy do indentyfikacji w panelu administracyjnym. A także pokazuje się przy edytowaniu strony w Module Stron (Typ)">
                            Nazwa <i class="mdi mdi-help-circle"></i>
                        </label>
                        <input type="text" :class="getInputClass('name')" name="name" v-model.lazy="name">
                        <small v-if="hasError('name')" class="error mt-2 text-danger">{{ errors.name[0] }}</small>
                    </div>

                    <div class="form-group col-md-4">
                        <label title="To jest nazwa szablonu. Szablon z taką nazwą musi być w resources->views">
                            Szablon <i class="mdi mdi-help-circle"></i>
                        </label>
                        <input type="text" :class="getInputClass('template')" name="template" v-model.lazy="template">
                        <small v-if="hasError('template')" class="error mt-2 text-danger">{{ errors.template[0] }}</small>
                    </div>

                    <div class="form-group col-md-4">
                        <label title="To nazwa tabeli z bazy danych. W tej tabeli będziemy szukac objektu przy edytowaniu strony">
                            Tabela <i class="mdi mdi-help-circle"></i>
                        </label>
                        <input type="text" :class="getInputClass('table')" name="table" v-model.lazy="table">
                        <small v-if="hasError('table')" class="error mt-2 text-danger">{{ errors.table[0] }}</small>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-12">
                        <label title="Ta legenda będzie się pokazywała przy edytowaniu strony. To jest tekst dla użytkownika panelu administracyjnego. Napisz tak żeby użytkownik zrozumiał co muśi wybrać">
                            Legenda <i class="mdi mdi-help-circle"></i>
                        </label>
                        <input type="text" class="form-control" name="text" v-model.lazy="text">
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
