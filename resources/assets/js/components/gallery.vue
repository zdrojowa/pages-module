<template>
    <div>
        <slot></slot>

        <b-nav align="right">
            <b-nav-item>
                <media-selector extensions="jpg,jpeg,png" @media-selected="add"></media-selector>
            </b-nav-item>
            <b-nav-item>
                <b-button type="bytton" variant="primary" @click="save">Zapisz</b-button>
            </b-nav-item>
        </b-nav>

        <div class="row item-conteiner">
            <draggable class="list-group" ghost-class="ghost" :list="gallery" handle=".handle">
                <div class="list-group-item" v-for="(element, index) in gallery" :key="index + element.url">
                    <div class="item gallery-item">
                        <b-icon-arrows-move class="handle"></b-icon-arrows-move>
                        <div class="thumbnail-img">
                            <media-selector extensions="jpg,jpeg,png" @media-selected="change(index, $event)"></media-selector>
                            <img :src="element.url" class="img-thumbnail">
                        </div>
                        <div class="gallery-form px-3">
                            <b-input-group prepend="Tytuł" class="mt-3">
                                <b-form-input v-model.lazy="element.title"></b-form-input>
                            </b-input-group>
                            <b-input-group prepend="Opis" class="mt-3">
                                <b-form-textarea v-model.lazy="element.description" rows="3"></b-form-textarea>
                            </b-input-group>
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
                            <button type="button" aria-label="Close" class="close" @click="remove(index)">×</button>
                        </div>
                    </div>
                </div>
            </draggable>
        </div>
    </div>
</template>

<script>

    export default {
        name: 'gallery',
        props: {
            id: {
                required: true,
                type: String
            }
        },

        data() {
            return {
                element: {url: '', title: '', description: '', link: {text: '', url: ''}},
                gallery: []
            };
        },

        created() {
            this.getGallery()
        },

        computed: {

            url: function () {
                return '/dashboard/pages/' + this.id
            }
        },

        methods: {

            getGallery() {
                let self = this;
                axios.get('/dashboard/pages/get?id=' + self.id)
                .then(res => {
                    if (typeof res.data.gallery == 'undefined') {
                        self.gallery = []
                    } else {
                       self.gallery = res.data.gallery
                    }
                }).catch(err => {
                    console.log(err)
                })
            },

            remove(index) {
                this.gallery.splice(index, 1)
            },

            add(url) {
                this.gallery.push({url: url, title: '', description: '', link: {text: '', url: ''}})
            },

            change: function(index, $event) {
              this.gallery[index].url = $event
            },

            save: function() {
                let self = this

                let formData = new FormData()
                formData.append('_method', 'PUT')
                formData.append('gallery', JSON.stringify(this.gallery))

                axios.post(this.url, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(res => {
                    this.$bvToast.toast('Galeria zaktualizowana', {
                        title: `Galeria`,
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
