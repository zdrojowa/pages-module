<template>
  <div>
      <b-card no-body>
        <b-tabs pills card>
          <b-tab title="Menu" active>
            <b-card-text>
                <b-row>
                    <b-col md="10"></b-col>
                    <b-col md="2">
                        <b-button block variant="primary" @click="save">Zapisz</b-button>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="6">
                        <b-form-group
                            label="Nazwa"
                        >
                            <b-form-input
                                v-model.lazy="name"
                                type="text"
                                :state="nameState"
                            ></b-form-input>
                        </b-form-group>
                    </b-col>
                    <b-col lg="6">
                        <b-form-group
                            label="Język"
                        >
                            <b-form-select v-model="lang" :options="languages"></b-form-select>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col md="10"></b-col>
                    <b-col md="2">
                        <b-button v-b-modal="'modal-menu-selector'" block variant="success">Dodaj</b-button>
                        <b-modal id="modal-menu-selector" title="Dodawanie" hide-footer>
                            <b-row>
                                <b-col>
                                    <b-form-group
                                        label="Typ"
                                    >
                                        <b-form-select
                                            v-model="element.type"
                                            :options="types"
                                        ></b-form-select>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                            <page-selector
                                v-if="element.type === 'page'"
                                :pages="pages"
                                :route="pageRoute"
                                :page="element.page"
                                @select="changePage"
                            ></page-selector>
                            <b-row v-else>
                                <b-col lg="6">
                                    <b-form-group
                                        label="Nazwa"
                                    >
                                        <b-form-input type="text" v-model="element.name"></b-form-input>
                                    </b-form-group>
                                </b-col>
                                <b-col lg="6">
                                    <b-form-group
                                        label="Url"
                                    >
                                        <b-form-input type="text" v-model="element.url"></b-form-input>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                            <b-row>
                                <b-button variant="primary" @click="add" block>Dodaj</b-button>
                            </b-row>
                        </b-modal>
                    </b-col>
                </b-row>
                <menu-structure
                    :list="structure"
                    :types="types"
                    :pages="pages"
                    :page-route="pageRoute"
                ></menu-structure>
            </b-card-text>
          </b-tab>
          <b-tab title="History" :disabled="!menu">
            <b-card-text>
                <revisions
                    v-if="menu"
                    table="menu"
                    :id="menu.id"
                    :route="revisionRoute"
                    :update-route="revisionUpdateRoute"
                    :remove-route="revisionRemoveRoute"
                    :csrf="csrf"
                ></revisions>
            </b-card-text>
          </b-tab>
        </b-tabs>
      </b-card>
  </div>
</template>

<script>
    import MenuStructure from './MenuStructure'
    import Revisions from "./Revisions";
    import PageSelector from "./PageSelector";
    export default {
        props: {
            menu: Object,
            languages: Array,
            types: Array,
            csrf: String,
            route: String,
            checkRoute: String,
            pageRoute: String,
            revisionRoute: String,
            revisionUpdateRoute: String,
            revisionRemoveRoute: String,
        },

        components: {
            MenuStructure,
            PageSelector,
            Revisions
        },

        data() {
            return {
                name: '',
                lang: 'pl',
                nameState: false,
                structure: [],
                pages: [],
                element: {type: 'page', page: null, name: '', url: '', items: []}
            }
        },

        computed: {

            realValue() {
                return this.value ? this.value : this.list;
            },

            dragOptions() {
                return {
                    animation: 0,
                    group: "description",
                    disabled: false,
                    ghostClass: "ghost"
                };
            }
        },

        created() {
            if (this.menu) {
                this.name = this.menu.name
                this.lang = this.menu.lang

                this.structure = this.menu.structure
                this.nameState = true
            }
            this.getPages()
        },

        methods: {
            getPages() {
                let route = this.pageRoute + '?lang=' + this.lang + '&select=id,name'
                axios.get(route)
                .then(res => {
                    this.pages = res.data
                }).catch(err => {
                    console.log(err)
                })
            },

            changePage(page) {
                this.element.page = page.id
                this.element.name = page.name
            },

            add() {
                this.structure.push(this.element)
                this.element = {type: 'page', page: null, name: '', url: '', items: []}
            },

            check: function() {
                let route = this.checkRoute + '?lang=' + this.lang + '&name=' + this.name
                if (this.menu) {
                    route += '&id=' + this.menu.id
                }
                axios.get(route)
                .then(res => {
                    this.nameState = res.data
                }).catch(err => {
                    console.log(err)
                })
            },

            save() {
                if (this.name.length && this.nameState) {
                    let formData = new FormData()
                    formData.append('_method', this.menu ? 'PUT' : 'POST')
                    formData.append('name', this.name)
                    formData.append('lang', this.lang)
                    formData.append('structure', JSON.stringify(this.structure))

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
            }
        },

        watch: {
            name() {
                if (this.name.length === 0) {
                    this.nameState = false
                } else {
                    this.check()
                }
            },

            lang() {
                this.getPages()
                if (this.name.length === 0) {
                    this.nameState = false
                } else {
                    this.check()
                }
            }
        }
    }
</script>

