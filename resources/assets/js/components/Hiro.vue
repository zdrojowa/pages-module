<template>
  <div>
      <b-row>
        <b-col md="10"></b-col>
        <b-col md="2">
          <b-button block variant="primary" @click="save">Zapisz</b-button>
        </b-col>
      </b-row>
      <b-row>
          <b-col lg="6">
              <b-form-group
                  label="Video"
              >
                  <selector
                      id="video"
                      extensions="3gp,3g2,asf,wmv,avi,divx,evo,f4v,flv,mp4,mpg,mpeg"
                      @media-selected="selectVideo"
                      :route="mediaSearchRoute"
                      :media-route="mediaRoute"
                  ></selector>

                  <div class="grid" v-if="video">
                      <div class="column-flex">
                          <file-view
                              :file="video"
                              :route="mediaRoute"
                              :show="false"
                              type="default"
                              :link="true"
                          ></file-view>
                          <b-button type="button" @click="video = null" variant="danger">
                              <b-icon-trash></b-icon-trash>
                          </b-button>
                      </div>
                  </div>
              </b-form-group>
          </b-col>
          <b-col lg="6">
            <b-form-group
                label="Zdjęcia"
            >
              <selector
                  id="image"
                  extensions="jpg,jpeg,png"
                  @media-selected="selectImage"
                  :route="mediaSearchRoute"
                  :media-route="mediaRoute"
              ></selector>

              <div class="grid" v-if="images.length > 0">
                <div v-for="(file ,i) in images" :key="i + file" class="column-flex">
                  <file-view
                      :file="file.file"
                      :route="mediaRoute"
                      :show="false"
                      :type="file.type"
                      :link="true"
                  ></file-view>
                  <b-button type="button" @click="removeImage(i)" variant="danger">
                    <b-icon-trash></b-icon-trash>
                  </b-button>
                </div>
              </div>
            </b-form-group>
          </b-col>
      </b-row>
  </div>
</template>

<script>
    import Selector from './../../../../../../../vendor/zdrojowa/media-module/resources/assets/js/components/Selector'
    import FileView from './../../../../../../../vendor/zdrojowa/media-module/resources/assets/js/components/FileView'
    export default {
        props : ['route', 'mediaSearchRoute', 'mediaRoute', 'csrf', 'page'],

        components: {
            Selector,
            FileView
        },

        data: function () {
            return {
                video: null,
                images: []
            }
        },

        created: function() {
          if (this.page.hiro) {
            this.getFiles()
          }
        },

        methods: {

            getFiles() {
              if (this.page.hiro.video) {
                  this.getFile(this.page.hiro.video)
                  .then(response => {
                    if (response.data && 0 in response.data) {
                      this.video = response.data[0]
                    }
                  })
              }
              if (this.page.hiro.images) {
                this.page.hiro.images.forEach(image => {
                  this.getFile(image.id)
                  .then(response => {
                    if (response.data && 0 in response.data && (image.type === 'default' || image.type in response.data[0].types)) {
                      this.images.push({file: response.data[0], type: image.type})
                    }
                  })
                })
              }
            },

            getFile(id) {
              return axios.get(this.mediaSearchRoute + '?search=' + id)
              .catch(err => {
                console.log(err)
              })
            },

            selectVideo(file) {
                this.video = file.file
            },

            selectImage(file) {
              this.images.push(file)
            },

            removeImage(position) {
              this.images.splice(position, 1)
            },

            save() {
              let formData = new FormData()
              formData.append('_method', 'PUT')
              formData.append('video', this.video ? this.video._id : '')
              formData.append('images', JSON.stringify(this.images))

              axios.post(this.route, formData, {
                headers: {
                  'X-CSRF-TOKEN': this.csrf
                }
              })
              .then(res => {
                this.$bvToast.toast('Hiro is updated', {
                  title: 'Hiro',
                  variant: 'success',
                  solid: true
                })
              }).catch(err => {
                this.$bvToast.toast('Error: ' + err.message, {
                  title: 'Hiro',
                  variant: 'danger',
                  solid: true
                })
              });
            },
        }
    }
</script>