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
                <div class="list-group-item" v-for="(element, index) in highlights" :key="index">
                    <div class="item gallery-item">
                        <div v-if="element.id" class="thumbnail-img">
                            <img :src="getIcon(element.id)" class="img-thumbnail">
                        </div>
                        <div v-else>
                            Bez ikonki
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
                            <b-form-checkbox v-model="element.isPage" switch>
                                Strona
                            </b-form-checkbox>
                            <div v-if="element.isPage" class="form-group mt-3">
                                <label>Strona</label>
                                <multiselect v-model.lazy="element.page" track-by="id" label="name" placeholder="Zaczni pisać" :options="pages" :searchable="true">
                                </multiselect>
                            </div>
                            <div v-else class="form-group mt-3">
                                <div v-if="element.image" class="gallery-item">
                                    <div class="thumbnail-img">
                                        <img :src="element.image" class="img-thumbnail">
                                    </div>
                                    <div class="gallery-form px-3">
                                        <b-form-group label="Link" class="mt-3">
                                            <div class="row">
                                                <b-input-group prepend="Text" class="mt-3 col-sm-6">
                                                    <b-form-input v-model.lazy="element.link.text"></b-form-input>
                                                </b-input-group>
                                                <b-input-group prepend="Url" class="mt-3 col-sm-6">
                                                    <b-form-input v-model.lazy="element.link.url"></b-form-input>
                                                </b-input-group>
                                            </div>
                                        </b-form-group>
                                    </div>
                                    <div>
                                        <button type="button" aria-label="Close" class="close" @click="removeImage(index)">×</button>
                                    </div>
                                </div>
                                <media-selector v-else extensions="jpg,jpeg,png" @media-selected-event="addImage" :data="index.toString()"></media-selector>
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
                element: {id: 0, title: '', label: '', description: '', isPage: true, page: '', image: '', link: {}},
                highlights: [],
                options: [{
                    name: 'Bez ikonki',
                    id: 0
                }],
                icon: {},
                pages: []
            };
        },

        created() {
            this.getPages()
            this.getIcons()
        },

        computed: {

            url: function () {
                return '/dashboard/pages/' + this.id
            }
        },

        methods: {

            getPages(query) {
                axios.get('/api/page?lang=' + this.lang + '&select=id,name')
                    .then(res => {
                        this.pages = res.data
                    }).catch(err => {
                    console.log(err)
                })
            },

            getIcons: function() {
                let self = this;

                axios.get('/api/icons')
                    .then(res => {
                        res.data.forEach(item => {
                            self.options.push(item)
                        })
                        this.getHighlights()
                    }).catch(err => {
                    console.log(err)
                })
            },

            getHighlights() {
                let self = this;
                axios.get('/dashboard/pages/get?id=' + self.id)
                    .then(res => {
                        if (typeof res.data.highlights == 'undefined') {
                            self.highlights = []
                        } else {
                            if (typeof res.data.highlights == 'string') {
                                self.highlights = JSON.parse(res.data.highlights)
                            } else {
                                res.data.highlights.forEach(item => {
                                    if (item.isPage) {
                                        self.pages.forEach(i => {
                                            if (i.id === item.page) {
                                                let page = item
                                                page.page = i
                                                self.highlights.push(page)
                                            }
                                        });
                                    } else {
                                        self.highlights.push(item)
                                    }
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
                        url = item.url
                    }
                });
                return url;
            },

            remove(index) {
                this.highlights.splice(index, 1)
            },

            removeImage(index) {
                this.highlights[index].image = ''
                this.$forceUpdate();
            },

            add() {
                this.highlights.push({id: this.icon.id, title: '', label: '', description: '', isPage: true, page: '', image: '', link: {}})
            },

            addImage(url, index) {
                this.highlights[index].page  = ''
                this.highlights[index].image = url
                this.highlights[index].link  = {text: '', url: ''}
                this.$forceUpdate()
            },

            save: function() {
                let self = this;

                let lights = [];
                this.highlights.forEach(item => {
                    lights.push({
                        id: item.id,
                        title: item.title,
                        label: item.label,
                        description: item.description,
                        isPage: item.isPage,
                        page: item.isPage ? item.page.id : '',
                        image: item.image,
                        link: item.link
                    });
                });

                let formData = new FormData();
                formData.append('_method', 'PUT')
                formData.append('highlights', JSON.stringify(lights))

                axios.post(this.url, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(res => {
                    this.$bvToast.toast('Highlights zaktualizowane', {
                        title: `Highlights`,
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
