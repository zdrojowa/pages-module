<template>
  <div>
      <b-row>
        <b-col md="10"></b-col>
        <b-col md="2">
          <b-button block variant="primary" @click="save">Zapisz</b-button>
        </b-col>
      </b-row>
      <b-row>
        <b-col>
          <b-form-group
              label="Tytuł"
          >
            <b-form-input
                v-model.lazy="title"
                type="text"
            ></b-form-input>
          </b-form-group>
        </b-col>
        <b-col>
          <b-form-group
              label="Etykieta"
          >
            <b-form-input
                v-model.lazy="label"
                type="text"
            ></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>
      <b-row>
        <b-col md="10">
          <b-form-group
              label="Opis"
          >
            <b-form-textarea
                v-model.lazy="description"
                rows="3"
                max-rows="6"
            ></b-form-textarea>
          </b-form-group>
        </b-col>
        <b-col md="2">
          <icon-selector
              :id="field"
              @icon-selected="selectIcon"
              :route="iconRoute"
          ></icon-selector>
        </b-col>
      </b-row>
      <b-row>
        <draggable class="list-group w-100" ghost-class="ghost" :list="items" handle=".handle">
          <div class="list-group-item" v-for="(item, index) in items" :key="index">
            <b-row>
              <b-col lg="2">
                  <b-button type="button" variant="primary" block class="handle">
                      <b-icon-arrows-move></b-icon-arrows-move>
                  </b-button>
                  <b-button type="button" @click="remove(index)" variant="danger" block>
                      <b-icon-trash></b-icon-trash>
                  </b-button>
              </b-col>
              <b-col lg="2">
                <div class="column-flex">
                  <icon-selector
                      :id="field + index"
                      @icon-selected="changeIcon(index, $event)"
                      :route="iconRoute"
                  ></icon-selector>
                  <icon-view
                      :id="item.icon"
                      :route="iconRoute"
                      :media-route="mediaRoute"
                  ></icon-view>
                </div>
              </b-col>
              <b-col lg="8">
                <b-form-group
                    label="Tytuł"
                >
                  <b-form-input
                      v-model.lazy="item.title"
                      type="text"
                  ></b-form-input>
                </b-form-group>
                <b-form-group
                    label="Etykieta"
                >
                  <b-form-input
                      v-model.lazy="item.label"
                      type="text"
                  ></b-form-input>
                </b-form-group>
                <b-form-group
                    label="Opis"
                >
                  <b-form-textarea
                      v-model.lazy="item.description"
                      rows="3"
                      max-rows="6"
                  ></b-form-textarea>
                </b-form-group>
              </b-col>
            </b-row>
            <b-row>
              <b-col lg="2">
                <b-form-checkbox v-model="item.is_page" switch>
                  Strona
                </b-form-checkbox>
              </b-col>
              <b-col lg="10">
                  <page-selector
                      v-if="item.is_page"
                      :pages="pages"
                      :route="pageRoute"
                      :page="item.page"
                      @select="changePage(index, $event)"
                  ></page-selector>
                  <b-row v-else>
                      <b-col lg="4">
                          <file-view-selector
                              label="Media"
                              :id="field + index"
                              extensions="jpg,jpeg,png,3gp,3g2,asf,wmv,avi,divx,evo,f4v,flv,mp4,mpg,mpeg"
                              @media-changed="changeMedia(index, $event)"
                              :route="mediaSearchRoute"
                              :media-route="mediaRoute"
                              :show="false"
                              :link="true"
                              :file="item.media"
                          ></file-view-selector>
                      </b-col>
                      <b-col lg="8">
                          <b-form-group
                              label="Link"
                          >
                              <label>Text</label>
                              <b-form-input type="text" v-model="item.link.text"></b-form-input>
                              <label>Url</label>
                              <b-form-input type="text" v-model="item.link.url"></b-form-input>
                          </b-form-group>
                      </b-col>
                  </b-row>
              </b-col>
            </b-row>
          </div>
        </draggable>
      </b-row>
  </div>
</template>

<script>
    import draggable from 'vuedraggable'
    import MultiSelect from 'vue-multiselect'
    import PageSelector from "./PageSelector";
    import IconSelector from './../../../../../../../vendor/zdrojowa/icon-module/resources/assets/js/components/IconSelector'
    import IconView from './../../../../../../../vendor/zdrojowa/icon-module/resources/assets/js/components/IconView'
    import FileViewSelector from './../../../../../../../vendor/zdrojowa/media-module/resources/assets/js/components/FileViewSelector'
    export default {
        props : ['field', 'route', 'mediaSearchRoute', 'mediaRoute', 'iconRoute', 'pageRoute', 'csrf', 'page'],

        components: {
            'multiselect': MultiSelect,
            draggable,
            PageSelector,
            IconSelector,
            IconView,
            FileViewSelector
        },

        data: function () {
            return {
                title: '',
                label: '',
                description: '',
                items: [],
                pages: []
            }
        },

        created: function() {
          this.getPages()
          if (this.field in this.page && this.page[this.field].items) {
            this.title       = this.page[this.field].title
            this.label       = this.page[this.field].label
            this.description = this.page[this.field].description
            this.items       = this.page[this.field].items
          }
        },

        methods: {
          getPages(query) {
            let route = this.pageRoute + '?lang=' + this.page.lang + '&select=id,name'
            if (this.page) {
                route += '&qid=' + this.page.id
            }
            axios.get(route)
            .then(res => {
                this.pages = res.data
            }).catch(err => {
                console.log(err)
            })
          },

          selectIcon(icon) {
            this.items.push({
              icon: icon,
              title: '',
              label: '',
              description: '',
              is_page: true,
              page: null,
              media: null,
              link: {link: null, url: null}
            })
          },

          changeIcon(index, $event) {
            this.items[index].icon = $event
          },

          remove(index) {
            this.items.splice(index, 1)
          },

          changeMedia(index, $event) {
              if ($event === null) {
                  this.items[index].media = null
              } else {
                  this.items[index].media = {id: $event.file._id, type: $event.type}
              }
          },

          changePage(index, $event) {
            this.items[index].page = $event.id
          },

          save() {
            let formData = new FormData()
            formData.append('_method', 'PUT')
            formData.append(this.field, JSON.stringify({
              title: this.title,
              label: this.label,
              description: this.description,
              items: this.items
            }))

            axios.post(this.route, formData, {
              headers: {
                'X-CSRF-TOKEN': this.csrf
              }
            })
            .then(res => {
              this.$bvToast.toast(this.field + ' is updated', {
                title: this.field,
                variant: 'success',
                solid: true
              })
            }).catch(err => {
              this.$bvToast.toast('Error: ' + err.message, {
                title: this.field,
                variant: 'danger',
                solid: true
              })
            });
          },
        }
    }
</script>