<template>
  <div>
      <b-row>
        <b-col md="10"></b-col>
        <b-col md="2">
          <b-button block variant="primary" @click="save">Zapisz</b-button>
        </b-col>
      </b-row>
      <b-row>
          <b-col lg="4">
              <b-form-group
                  label="Status"
              >
                  <b-form-select v-model="status" :options="statuses"></b-form-select>
              </b-form-group>

              <b-form-group
                  label="Język"
              >
                  <b-form-select v-model="lang" :options="languages"></b-form-select>
              </b-form-group>

              <b-form-group
                  label="Slug"
                  description="Musi być unikalny"
              >
                  <b-input-group>
                      <b-form-input
                          v-model.lazy="slug"
                          type="text"
                          :state="slugState"
                      ></b-form-input>
                  </b-input-group>
              </b-form-group>

              <b-form-group
                  label="Rodzic"
              >
                <multiselect
                    v-model.lazy="parent"
                    track-by="id"
                    label="name"
                    placeholder="Zaczni pisać"
                    :options="parents"
                    :searchable="true"
                    @search-change="getParents"
                >
                  <template slot="tag" slot-scope="{ option, remove }">
                    <span class="custom__tag"><span>{{ option.name }}</span><span class="custom__remove" @click="remove(option)">❌</span></span>
                  </template>
                </multiselect>
              </b-form-group>

              <b-form-group
                  label="Typ"
              >
                  <b-form-select
                      v-model="type"
                      :options="typeOptions"
                      :state="typeState"
                  ></b-form-select>
              </b-form-group>

              <b-form-group v-if="hasObject"
                  :label="type.text"
              >
                  <multiselect
                      v-model.lazy="object"
                      track-by="id"
                      label="name"
                      placeholder="Zaczni pisać"
                      :options="objects"
                      :searchable="true"
                      @search-change="getObjects"
                  >
                      <template slot="tag" slot-scope="{ option, remove }">
                          <span class="custom__tag"><span>{{ option.name }}</span><span class="custom__remove" @click="remove(option)">❌</span></span>
                      </template>
                  </multiselect>
              </b-form-group>

              <b-form-group
                  label="Tagi"
              >
                  <b-form-tags v-model="tags" tag-variant="success"></b-form-tags>
              </b-form-group>

              <b-form-group
                  label="Prioritet"
              >
                  <b-input-group>
                      <b-input-group-prepend>
                          <b-button @click="priority = 0"><b-icon-slash-circle></b-icon-slash-circle></b-button>
                      </b-input-group-prepend>
                      <b-form-rating v-model="priority" color="#ff8800" stars="10"></b-form-rating>
                  </b-input-group>
              </b-form-group>

              <file-view-selector
                  label="Zdjęcie"
                  id="page-file-view-selector"
                  extensions="jpg,jpeg,png"
                  :route="mediaSearchRoute"
                  :media-route="mediaRoute"
                  @media-changed="changeMedia"
                  :show="false"
                  :link="false"
                  :file="image"
              ></file-view-selector>
          </b-col>
          <b-col lg="8">
              <b-form-group
                  label="Nazwa"
              >
                  <b-form-input
                      v-model.lazy="name"
                      type="text"
                      :state="name.length > 0"
                  ></b-form-input>
              </b-form-group>

              <b-form-group
                  label="Tytuł"
              >
                  <b-form-input
                      v-model.lazy="title"
                      type="text"
                      :state="title.length > 0"
                      debounce="500"
                  ></b-form-input>
              </b-form-group>

              <b-form-group
                  label="Lead"
              >
                  <b-form-textarea
                      v-model.lazy="lead"
                      rows="3"
                      max-rows="6"
                  ></b-form-textarea>
              </b-form-group>

              <b-form-group
                  label="Content"
                  description="Jeśli chcesz dodać obrazek naciśni 'Toggle Editor' i wpisz: <figure class='image'><img src='Tu wpisz link do obrazka z Media'></figure>"
              >
                  <b-button @click="toggle = !toggle">Toggle Editor</b-button>
                  <ckeditor v-if="toggle" :editor="editor" v-model.lazy="content" :config="editorConfig"></ckeditor>
                  <b-form-textarea
                      v-else
                      v-model="content"
                      rows="10"
                  ></b-form-textarea>
              </b-form-group>
          </b-col>
      </b-row>
  </div>
</template>

<script>
    import FileViewSelector from './../../../../../../../vendor/zdrojowa/media-module/resources/assets/js/components/FileViewSelector'
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic'
    import MultiSelect from 'vue-multiselect'
    export default {
        props:{
            page: Object,
            statuses: Array,
            types: Array,
            languages: Array,
            csrf: String,
            route: String,
            mediaSearchRoute: String,
            mediaRoute: String,
            checkRoute: String,
            pageRoute: String,
            objectRoute: String
        },

        components: {
            FileViewSelector,
            'multiselect': MultiSelect
        },

        data() {
            return {
                editor: ClassicEditor,
                editorConfig: {
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'bold',
                            'italic',
                            'link',
                            'bulletedList',
                            'numberedList',
                            'indent',
                            'outdent',
                            '|',
                            'mediaEmbed',
                            'blockQuote',
                            'insertTable',
                            'undo',
                            'redo'
                        ]
                    }
                },
                toggle: true,
                defaultText: 'Nie ma',
                parents: [],
                typeOptions: [],
                objects: [],
                slug: '',
                status: 'public',
                lang: 'pl',
                parent: {id: 0, name: 'Nie ma'},
                type: {},
                object: null,
                tags: [],
                priority: 0,
                image: null,
                name: '',
                title: '',
                lead: '',
                content: '',
                slugState: null,
                objectState: null,
                typeState: false,
            }
        },

        created() {
            this.types.forEach(type => {
                this.typeOptions.push({
                    value: {template: type.template, table: type.table, text: type.text},
                    text: type.name
                })
            })

            if (this.page) {
                this.status   = this.page.status
                this.name     = this.page.name
                this.title    = this.page.title
                this.lead     = this.page.lead
                this.lang     = this.page.lang
                this.slug     = this.getSlug()
                this.tags     = this.page.tags
                this.priority = this.page.priority
                this.content  = this.page.content
                this.image    = this.page.image
                this.typeOptions.forEach(type => {
                    if (type.value.template === this.page.type) {
                        this.type = type.value
                    }
                })
                this.getParents(this.page.parent)
                this.getObjects(this.page.object)
                this.slugState = true
            }
        },

        computed: {
            hasObject() {
                return this.type && this.type.table
            },
        },

        methods: {
            sanitize(str) {
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

            getSlug() {
                let index = this.page.permalink.lastIndexOf('/')
                let slug  = this.page.permalink.substr(index + 1, this.page.permalink.length - index)
                if (slug === this.lang) {
                    slug = ''
                }
                return slug
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

            getParents(query) {
                let route = this.pageRoute + '?query=' + query + '&lang=' + this.lang + '&select=id,name'
                if (this.page) {
                    route += '&qid=' + this.page.id
                }
                axios.get(route)
                .then(res => {
                    this.parents = res.data
                    this.parents.push({id: 0, name: this.defaultText})

                    if (this.parent.id === 0 && this.page.parent) {
                        res.data.forEach(item => {
                            if (item.id === this.page.parent) {
                                this.parent = item
                            }
                        });
                    }
                }).catch(err => {
                    console.log(err)
                })
            },

            getObjects(query) {
                axios.get(this.objectRoute + '?table=' + this.type.table + '&query=' + query)
                .then(res => {
                    this.objects = res.data;

                    if (!this.object && this.page.object) {
                        res.data.forEach(item => {
                            if (item.id === this.page.object) {
                                this.object = item
                            }
                        });
                    }
                }).catch(err => {
                    console.log(err)
                })
            },

            changeMedia(file) {
                if (file === null) {
                    this.image = null
                } else {
                    this.image = {id: file.file._id, type: file.type}
                }
            },

            check: function() {
                let route = this.checkRoute + '?slug=' + this.slug + '&lang=' + this.lang
                if (this.parent) {
                    route += '&parent=' + this.parent.id
                }
                if (this.page) {
                    route += '&id=' + this.page.id
                }
                axios.get(route)
                .then(res => {
                    this.slugState = res.data
                }).catch(err => {
                    console.log(err)
                })
            },

            save() {
                if (this.name.length && this.title.length && this.slugState && this.typeState && this.objectState) {
                  let formData = new FormData()
                  formData.append('_method', this.page ? 'PUT' : 'POST')
                  formData.append('name', this.name)
                  formData.append('title', this.title)
                  formData.append('status', this.status)
                  formData.append('lang', this.lang)
                  formData.append('slug', this.slug)
                  formData.append('parent', this.parent.id)
                  formData.append('type', this.type.template)
                  formData.append('tags', JSON.stringify(this.tags))
                  formData.append('priority', this.priority)
                  formData.append('image', JSON.stringify(this.image))
                  formData.append('lead', this.lead)
                  formData.append('content', this.content)
                  formData.append('object', this.hasObject ? this.object.id : null)

                  axios.post(this.route, formData, {
                    headers: {
                      'X-CSRF-TOKEN': this.csrf
                    }
                  })
                  .then(res => {
                    window.location = res.data.redirect
                  }).catch(err => {
                    console.log(err);
                  });
                }
            },
        },

        watch: {
            title() {
                if (this.title && this.slug === '' && !this.page) {
                    this.slug = this.sanitize(this.title)
                }
            },

            parent() {
                this.check()
            },

            lang() {
                this.check()
            },

            slug() {
                this.check()
            },

            type() {
                this.typeState   = true
                this.objectState = !this.hasObject
                this.object      = null
                if (this.hasObject) {
                    this.getObjects()
                }
            },

            object() {
                if (!this.hasObject) {
                    this.objectState = true
                } else {
                    this.objectState = !!this.object
                }
            }
        }
    }
</script>