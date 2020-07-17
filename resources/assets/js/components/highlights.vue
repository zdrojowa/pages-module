<template>
    <div>
        <slot></slot>

        <b-nav align="right">
            <b-nav-item>
                <b-button type="button" variant="success" v-b-modal.modal>Dodaj</b-button>
            </b-nav-item>
            <b-nav-item>
                <b-button type="bytton" variant="primary" @click="save">Zapisz</b-button>
            </b-nav-item>
        </b-nav>

        <div class="row item-conteiner">
            <draggable class="list-group" ghost-class="ghost" :list="highlights">
                <div class="list-group-item" v-for="(element, index) in highlights" :key="element.id">
                    <div class="item gallery-item">
                        <div class="thumbnail-img">
                            <img :src="getIcon(element.id)" class="img-thumbnail">
                        </div>
                        <div class="gallery-form px-3">
                            <b-input-group prepend="Tytuł" class="mt-3">
                                <b-form-input v-model.lazy="element.title"></b-form-input>
                            </b-input-group>
                            <b-input-group prepend="Etykieta" class="mt-3">
                                <b-form-textarea v-model.lazy="element.label" rows="3"></b-form-textarea>
                            </b-input-group>
                            <b-input-group prepend="Opis" class="mt-3">
                                <b-form-textarea v-model.lazy="element.description" rows="3"></b-form-textarea>
                            </b-input-group>
                            <div class="form-group mt-3">
                                <label>Strona</label>
                                <multiselect v-model.lazy="element.page" track-by="id" label="name" placeholder="Zaczni pisać" :options="pages" :searchable="true">
                                </multiselect>
                            </div>
                        </div>
                        <div>
                            <button type="button" aria-label="Close" class="close" @click="remove(index)">×</button>
                        </div>
                    </div>
                </div>
            </draggable>
        </div>

        <b-modal id="modal" title="Dodawanie" hide-footer>
            <b-nav align="right">
                <b-nav-item>
                    <b-button type="button" variant="success" @click="add">Zapisz</b-button>
                </b-nav-item>
            </b-nav>
            <div class="row">

                <div class="col-md-12">
                    <div class="form-group">
                        <label>Ikonka</label>
                        <multiselect :options="options" track-by="id" label="name" placeholder="Wybierz ikonkę" v-model.lazy="icon"></multiselect>
                    </div>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>

    export default {
        name: 'highlights',
        props: {
            id: {
                required: true,
                type: String
            },
            lang: {
                required: true,
                type: String
            }
        },

        data() {
            return {
                element: {id: 0, title: '', label: '', description: '', page: ''},
                highlights: [],
                options: [],
                icon: {},
                pages: []
            };
        },

        created() {
            this.getPages();
            this.getIcons();
        },

        computed: {

            url: function () {
                return '/dashboard/pages/' + this.id;
            }
        },

        methods: {

            getPages(query) {
                axios.get('/api/page?lang=' + this.lang + '&select=id,name')
                    .then(res => {
                        this.pages = res.data;
                    }).catch(err => {
                    console.log(err)
                })
            },

            getIcons: function() {
                let self = this;

                axios.get('/api/pages-icons')
                    .then(res => {
                        self.options = res.data;
                        this.getHighlights();
                    }).catch(err => {
                    console.log(err)
                })
            },

            getHighlights() {
                let self = this;
                axios.get('/dashboard/pages/get?id=' + self.id)
                    .then(res => {
                        if (typeof res.data.highlights == 'undefined') {
                            self.highlights = [];
                        } else {
                            if (typeof res.data.highlights == 'string') {
                                self.highlights = JSON.parse(res.data.highlights);
                            } else {
                                res.data.highlights.forEach(item => {
                                    self.pages.forEach(i => {
                                        if (i.id === item.page) {
                                            let page = item;
                                            page.page = i;
                                            self.highlights.push(page);
                                        }
                                    });

                                });
                            }
                        }
                    }).catch(err => {
                        console.log(err)
                })
            },

            getIcon: function(id) {
                let url = '';
                this.options.forEach(item => {
                    if (item.id === id) {
                        url = item.url;
                    }
                });
                return url;
            },

            remove(index) {
                this.highlights.splice(index, 1);
            },

            add() {
                this.highlights.push({id: this.icon.id, title: '', label: '', description: '', page: ''});
            },

            save: function() {
                let self = this;

                let lights = [];
                this.highlights.forEach(item => {
                    lights.push({id: item.id, title: item.title, label: item.label, description: item.description, page: item.page.id});
                });

                let formData = new FormData();
                formData.append('_method', 'PUT');
                formData.append('highlights', JSON.stringify(lights));

                axios.post(this.url, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(res => {
                }).catch(err => {
                    console.log(err);
                });
            }
        }
    }
</script>
