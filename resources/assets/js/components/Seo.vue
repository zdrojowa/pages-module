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
                  label="Meta description"
              >
                  <b-form-textarea
                      v-model.lazy="meta_description"
                      rows="3"
                      max-rows="6"
                  ></b-form-textarea>
              </b-form-group>
          </b-col>
      </b-row>
  </div>
</template>

<script>
    export default {
        props : ['route', 'csrf', 'page'],

        data() {
            return {
                meta_description: '',
            }
        },

        created() {
            if (this.page && this.page.meta_description) {
                this.meta_description = this.page.meta_description
            }
        },

        methods: {
            save() {
              let formData = new FormData()
              formData.append('_method', 'PUT')
              formData.append('meta_description', this.meta_description)

              axios.post(this.route, formData, {
                headers: {
                  'X-CSRF-TOKEN': this.csrf
                }
              })
              .then(res => {
                  this.$bvToast.toast('SEO zaktualizowane', {
                      title: 'SEO',
                      variant: 'success',
                      solid: true
                  })
              }).catch(err => {
                  this.$bvToast.toast('Error: ' + err.message, {
                      title: 'SEO',
                      variant: 'danger',
                      solid: true
                  })
              });
            },
        }
    }
</script>