<template>
    <div>
        <slot></slot>
        <input v-if="_id" type="hidden" name="translation" :value="_id">

        <b-nav align="right">
            <b-nav-text v-if="_id">
                <a :href="obj.permalink" target="_blank" class="btn btn-info">
                    <i class="mdi mdi-open-in-new"></i> Podgląd
                </a>
            </b-nav-text>
            <b-nav-item>
                <b-button type="button" variant="primary" @click="validate">Zapisz</b-button>
            </b-nav-item>
        </b-nav>

        <div class="row">

            <div class="col-lg-4">
                <div class="form-group">
                    <label>Status</label>
                    <multiselect v-model.lazy="obj.status" :options="statuses" track-by="id" label="name" placeholder="Wybierz status"></multiselect>
                </div>

                <div class="form-group">
                    <label>Parent</label>
                    <multiselect v-model.lazy="obj.parent" track-by="id" label="name" placeholder="Zaczni pisać" :options="parents" :searchable="true" @search-change="getParents">
                        <template slot="tag" slot-scope="{ option, remove }"><span class="custom__tag"><span>{{ option.name }}</span><span class="custom__remove" @click="remove(option)">❌</span></span></template>
                    </multiselect>
                </div>

                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" :class="getInputClass('slug')" name="slug" v-model.lazy="slug">
                    <small v-if="hasError('slug')" class="error mt-2 text-danger">{{ errors.slug[0] }}</small>
                </div>

                <div class="form-group">
                    <label>Język</label>
                    <multiselect v-model.lazy="obj.lang" :options="langs" track-by="key" label="name" placeholder="Wybierz język"></multiselect>
                </div>

                <div class="form-group">
                    <label>Typ</label>
                    <multiselect v-model.lazy="obj.type" track-by="template" label="name" placeholder="Wybierz typ" :options="types"></multiselect>
                </div>

                <div v-if="hasModel" class="form-group">
                    <label>{{ obj.type.text }}</label>
                    <multiselect v-model.lazy="obj.object" track-by="id" label="name" placeholder="Zaczni pisać" :options="objects" :searchable="true" @search-change="getObjects">
                        <template slot="tag" slot-scope="{ option, remove }"><span class="custom__tag"><span>{{ option.name }}</span><span class="custom__remove" @click="remove(option)">❌</span></span></template>
                    </multiselect>
                    <small v-if="hasError('object')" class="error mt-2 text-danger">{{ errors.object[0] }}</small>
                </div>

                <div v-if="isPageTypeIndex" class="form-group">
                    <label></label>
                    <b-form-checkbox v-model.lazy="obj.index_display" switch>
                        Wyświetl na stronie indeksowej
                    </b-form-checkbox>
                </div>

                <div class="form-group">
                    <label>Tagi</label>
                    <b-form-tags v-model="obj.tags" tag-variant="success"></b-form-tags>
                </div>

                <div class="form-group">
                    <label>Prioritet</label>
                    <b-input-group>
                        <b-input-group-prepend>
                            <b-button @click="obj.priority = 0"><b-icon-slash-circle></b-icon-slash-circle></b-button>
                        </b-input-group-prepend>
                        <b-form-rating v-model="obj.priority" color="#ff8800" stars="10"></b-form-rating>
                    </b-input-group>
                </div>

                <div class="form-group">
                    <label>Image</label>
                    <media-selector extensions="jpg,jpeg,png" @media-selected="selectImage"></media-selector>
                    <div v-if="obj.image" class="img-thumbnail">
                        <img :src="obj.image"/>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="form-group">
                  <label>Nazwa</label>
                  <input type="text" :class="getInputClass('name')" name="name" placeholder="Wpisz nazwę" v-model.lazy="obj.name">
                  <small v-if="hasError('name')" class="error mt-2 text-danger">{{ errors.name[0] }}</small>
                </div>

                <div class="form-group">
                    <label>Tytuł</label>
                    <input type="text" :class="getInputClass('title')" name="title" placeholder="Wpisz tytuł" v-model.lazy="obj.title">
                    <small v-if="hasError('title')" class="error mt-2 text-danger">{{ errors.title[0] }}</small>
                </div>

                <div class="form-group">
                    <label>Lead</label>
                    <textarea rows="7" class="form-control" name="lead" placeholder="Wpisz lead" v-model.lazy="obj.lead"/>
                </div>

                <div class="form-group">
                    <label>Content</label>
                    <ckeditor :editor="editor" v-model="obj.content" :config="editorConfig"></ckeditor>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
    import MyCustomUploadAdapterPlugin from './UploadAdapterPlugin';

    export default {
        name: 'editor',
        props : ['_id', 'lang'],

        data() {
            return {
                editor: ClassicEditor,
                editorConfig: {
                    extraPlugins: [ MyCustomUploadAdapterPlugin ]
                },
                statuses: [],
                defaultParent: {id: 0, name: 'Nie ma'},
                parents: [],
                langs: [],
                mainLang: 'pl',
                types: [],
                objects: [],
                obj: {
                    id: 0,
                    name: '',
                    title: '',
                    lead: '',
                    content: '',
                    permalink: '/',
                    status: {id: 'draft', name: 'DRAFT'},
                    image: '',
                    parent: this.defaultParent,
                    lang: {key: 'pl', name: 'Polski'},
                    type: null,
                    object: null,
                    tags: [],
                    priority: 0
                },
                errors: {
                    name: {},
                    title: {},
                    slug: {},
                    object: {}
                },
                slug: ''
            };
        },

        created() {
            this.getStatuses();
        },

        computed: {
            url() {
                return this.obj.id ? ('/dashboard/pages/' + this.obj.id) : '/dashboard/pages/store'
            },

            hasModel() {
                return this.obj.type != null && this.obj.type.table_name
            },

            isPageTypeIndex() {
                return this.obj && this.obj.type && ['hotels.wellness', 'hotel', 'offer', 'kitchen', 'hotels.conference'].includes(this.obj.type.template)
            }
        },

        methods: {
            getObjects(query) {
                axios.get('/dashboard/pages/getObjects?table=' + this.obj.type.table_name + '&query=' + query)
                .then(res => {
                    this.objects = [];
                    res.data.forEach(item => {
                        this.objects.push(item)
                    });

                    this.obj.object = this.getItem(this.objects, 'id', this.obj.object)
                }).catch(err => {
                    console.log(err)
                })
            },

            selectImage: function(url) {
                this.obj.image = url
                this.$forceUpdate()
            },

            hasError: function(key) {
                return this.errors[key].length > 0
            },

            getInputClass: function(key) {
                let className = 'form-control '
                if (this.hasError(key)) {
                    className += 'is-invalid'
                } else {
                    if (this.obj[key]) {
                        className += 'is-valid'
                    }
                }
                return className
            },

            getTypes: function() {
                axios.get('/dashboard/pages-types/get')
                    .then(res => {
                        this.types = res.data
                        this.getPage()
                    }).catch(err => {
                    console.log(err)
                })
            },

            getMainLang: function() {
                axios.get('/dashboard/settings/getByKey/lang')
                .then(res => {
                    this.mainLang = res.data.value
                    this.obj.lang = this.getItem(this.langs, 'key', this.mainLang)
                    this.getTypes()
                }).catch(err => {
                    console.log(err)
                    this.getTypes()
                });
            },

            getLangs: function() {
                axios.get('/dashboard/languages/get')
                .then(res => {
                    this.langs = res.data
                    this.getMainLang()
                }).catch(err => {
                    console.log(err)
                })
            },

            getParents: function(query) {
                axios.get('/dashboard/pages/get?query=' + query + '&qid=' + this.obj.id + '&lang=' + this.obj.lang.key)
                .then(res => {
                    this.parents = []
                    this.parents.push(this.defaultParent)

                    res.data.forEach(item => {
                        this.parents.push(item)
                    });

                    this.obj.parent = this.getItem(this.parents, 'id', this.obj.parent)
                }).catch(err => {
                    console.log(err)
                })
            },

            getStatuses: function() {
                let self = this;

                axios.get('/dashboard/pages/statuses')
                .then(res => {
                    self.statuses = res.data
                    this.getLangs()
                }).catch(err => {
                    console.log(err)
                })
            },

            MyCustomUploadAdapterPlugin ( editor ) {
                editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
                    return new MyUploadAdapter( loader )
                };
            },

            getItem: function(arr, key, val) {

                let item = val

                arr.forEach(it => {
                    if (it[key] === val) {
                        item = it
                    }
                });

                return item
            },

            getPage: function() {
                let self = this
                if (self._id) {
                    axios.get('/dashboard/pages/get?id=' + self._id)
                    .then(res => {
                        self.obj = res.data

                        if (typeof self.obj.lead === 'undefined') {
                            self.obj.lead = ''
                        }

                        if (typeof self.obj.title === 'undefined') {
                          self.obj.title = ''
                        }

                        if (typeof self.obj.index_display === 'undefined') {
                            self.obj.index_display = true
                        }

                        self.obj.status = self.getItem(self.statuses, 'id', self.obj.status)
                        self.obj.type   = self.getItem(self.types, 'template', self.obj.type)

                        self.slug = self.getSlug()

                        if (self.lang !== self.obj.lang) {
                            self.obj.lang   = self.lang
                            self.obj.id     = 0
                            self.obj.parent = this.defaultParent
                        }
                        self.obj.lang = self.getItem(self.langs, 'key', self.obj.lang)

                        if (self.hasModel && self.obj.object != null) {
                            self.getObjects(self.obj.object)
                        }

                        self.getParents(self.obj.parent)

                    }).catch(err => {
                        console.log(err)
                    })
                } else {
                    this.parents    = [this.defaultParent]
                    this.obj.parent = this.defaultParent
                }
            },

            validate: function(e) {
                e.preventDefault()
                if (this.obj.name) {
                    let formData = new FormData()
                    formData.append('_method', this.obj.id ? 'PUT' : 'POST')
                    formData.append('obj', JSON.stringify(this.obj))
                    formData.append('translation', this._id)

                    axios.post(this.url, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    })
                    .then(res => {
                        window.location = res.data.redirect
                    }).catch(err => {
                        console.log(err);
                    });
                } else {
                    this.errors.name = ['To pole jest wymagane']
                    return false
                }
            },

            sanitize: function(str) {
                return str.toLowerCase()
                    .replace(/ę/gi, 'e')
                    .replace(/ą/gi, 'a')
                    .replace(/ó/gi, 'o')
                    .replace(/ł/gi, 'l')
                    .replace(/ś/gi, 's')
                    .replace(/ż|ź/gi, 'z')
                    .replace(/ć/gi, 'c')
                    .replace(/ń/gi, 'n')
                    .replace(/\s*$/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/[^a-z0-9-]/gi, '')
            },

            getSlug: function() {
                let index = this.obj.permalink.lastIndexOf('/')
                let slug  = this.obj.permalink.substr(index + 1, this.obj.permalink.length - index)
                if (slug === this.obj.lang) {
                    slug = ''
                }
                return slug
            },

            setPermalink: function() {
                this.obj.permalink = ''
                if (this.mainLang !== this.obj.lang.key) {
                    this.obj.permalink = '/' + this.obj.lang.key
                }
                if (this.obj.parent.id) {
                    this.obj.permalink = this.obj.parent.permalink
                }
                if (this.slug !== '' || this.obj.permalink === '') {
                    this.obj.permalink += '/' + this.slug
                }
                this.obj.permalink = this.obj.permalink.replace(/\/*/, '/')
            },

            checkPermalinkUnique: function() {
                axios.get('/dashboard/pages/check/' + this.obj.id + '?permalink=' + this.obj.permalink)
                .then(res => {
                    if (res.data) {
                        this.errors.slug = []
                    } else {
                        this.errors.slug = ['To pole musi być unikalne. Slug z takim rodzicem już istnieje. Musisz wymyślieć inny slug, albo wybrać innego rodzica']
                    }
                }).catch(err => {
                    console.log(err)
                })
            }
        },

        watch: {
            'obj.title'() {
                if (!this.obj.title) {
                    this.errors.title = ['To pole jest wymagane']
                } else {
                    if (this.slug === '' && !this.obj.id) {
                        this.slug = this.sanitize(this.obj.title)
                    }
                }
            },

            'obj.parent'() {
                this.setPermalink()
                this.checkPermalinkUnique()
            },

            'slug'() {
                this.setPermalink()
                this.checkPermalinkUnique()
            },

            hasModel() {
                if (this.hasModel) {
                    if(!this.obj.object) {
                        this.errors.object = ['To pole jest wymagane']
                    } else {
                        this.errors.object = []
                    }
                } else {
                    this.errors.object = []
                }
            }
        }
    }
</script>
