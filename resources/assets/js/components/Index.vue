<template>
  <div>
      <b-card class="mb-2" no-header>
          <b-row>
              <b-col lg="8">
                  <b-form-group
                      label="Filter"
                  >
                      <b-input-group>
                          <b-form-input
                              v-model="filter"
                              type="search"
                          ></b-form-input>

                          <b-input-group-append>
                              <b-button :disabled="!filter" @click="filter = ''">Wyczyść</b-button>
                          </b-input-group-append>
                      </b-input-group>
                  </b-form-group>
                  <b-form-group
                      v-if="langs"
                      label="Język"
                  >
                      <b-form-select v-model="lang" :options="langs" @change="changeLang"></b-form-select>
                  </b-form-group>
                  <b-form-group
                      v-if="parents"
                      label="Rodzic"
                  >
                      <b-form-select v-model="parent" :options="parents[lang]" @change="get"></b-form-select>
                  </b-form-group>
              </b-col>
              <b-col lg="4" class="my-1">
                  <b-form-group
                      label="Wyników na stronie"
                  >
                      <b-form-select
                          v-model="perPage"
                          :options="pageOptions"
                      ></b-form-select>
                  </b-form-group>
                  <b-pagination
                      v-model="page"
                      :total-rows="totalRows"
                      :per-page="perPage"
                      align="fill"
                  ></b-pagination>
              </b-col>
          </b-row>
          <b-table striped hover
               :responsive="true"
               :items="items"
               :fields="fields"
               :current-page="page"
               :per-page="perPage"
               :filter="filter"
               @filtered="onFiltered"
          >
              <template #cell(lang)="row" v-if="langs">
                <country-flag :country="getCountry(row.item.lang)" size='normal'/>
              </template>
              <template #cell(translations)="row" v-if="langs">
                <span
                    v-for="lang in langs"
                    v-if="lang.value !== row.item.lang"
                    :key="row.item.id + lang.value"
                >
                    <b-link
                        v-if="hasTranslation(row.item, lang.value)"
                        :href="edit(row.item.translations[lang.value])"
                        target="_blank"
                    >
                        <country-flag :country="getCountry(lang.value)" size='normal'/>
                    </b-link>
                    <b-button
                        v-else
                        @click="translate(row.item, lang.value)"
                    >
                        <country-flag :country="getCountry(lang.value)" size='normal' class="opacity"/>
                    </b-button>
                </span>
              </template>
              <template #cell(actions)="row">
                  <b-button :href="edit(row.item.id)" variant="primary">
                      <b-icon-pencil></b-icon-pencil>
                  </b-button>
                  <b-button @click="remove(row.item)" variant="danger">
                      <b-icon-trash></b-icon-trash>
                  </b-button>
              </template>
          </b-table>
      </b-card>
  </div>
</template>

<script>
    export default {
        props : ['route', 'removeRoute', 'editRoute', 'translationRoute', 'csrf', 'langs', 'parents', 'fields'],

        data() {
            return {
                items: [],
                filter: null,
                totalRows: 1,
                page: 1,
                perPage: 50,
                pageOptions: [10, 50, 100],
                lang: 'pl',
                parent: ''
            }
        },

        created: function() {
            this.get()
        },

        methods: {
            get() {
                let self = this
                let route = this.route
                if (this.langs) {
                  route += '?lang=' + this.lang
                }
                if (this.parent) {
                    route += '&parent=' + this.parent
                }
                axios.get(route)
                  .then(function(response) {
                      self.items     = response.data
                      self.totalRows = self.items.length
                      self.page      = 1
                      self.filter    = ''
                  }).catch(function(error) {
                    console.log(error)
                })
            },

            changeLang() {
                this.parent = ''
                this.get()
            },

            onFiltered(filteredItems) {
                this.totalRows = filteredItems.length
                this.page      = 1
            },

            remove(item) {
                let self = this
                this.$bvModal.msgBoxConfirm('Czy jesteś pewny?', {
                    title: 'Usunąć',
                    size: 'sm',
                    buttonSize: 'sm',
                    okVariant: 'danger',
                    okTitle: 'Tak',
                    cancelTitle: 'Nie',
                    footerClass: 'p-2',
                    hideHeaderClose: false,
                    centered: true
                })
                .then(value => {
                    if (value) {
                        let route = self.removeRoute.replace('id', item.id)
                        axios.delete(route, {
                            headers: {
                                'X-CSRF-TOKEN': self.csrf
                            }
                        })
                        .then(function(response) {
                            self.get()
                        }).catch(function(error) {
                            self.$bvToast.toast('Error: ' + error.message, {
                                title: 'Usunąć',
                                variant: 'danger',
                                solid: true
                            })
                        })
                    }
                })
                .catch(err => {
                    self.$bvToast.toast('Error: ' + err.message, {
                        title: 'Usunąć',
                        variant: 'danger',
                        solid: true
                    })
                })
            },

            edit(id) {
                return this.editRoute.replace('id', id)
            },

            getCountry(lang) {
                return lang === 'en' ? 'gb' : lang
            },

            hasTranslation(page, lang) {
                return page.translations && lang in page.translations
            },

            translate(page, lang) {
                axios.post(this.translationRoute.replace('id', page.id).replace('lang', lang), {
                    headers: {
                        'X-CSRF-TOKEN': this.csrf
                    }
                })
                .then(function(response) {
                    window.location = response.data.redirect
                }).catch(function(error) {
                    console.log(error)
                })
            }
        },
    }
</script>

