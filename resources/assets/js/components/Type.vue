<template>
    <b-card no-header>
      <b-row>
        <b-col md="10"></b-col>
        <b-col md="2">
          <b-button block variant="primary" @click="save">Zapisz</b-button>
        </b-col>
      </b-row>
      <b-row>
          <b-col lg="4">
              <b-form-group
                  label="Nazwa"
                  description="Służy do indentyfikacji w panelu administracyjnym. A także pokazuje się przy edytowaniu strony w Module Stron (Typ)"
              >
                  <b-form-input type="text" v-model="name" :state="nameState"></b-form-input>
              </b-form-group>
          </b-col>

          <b-col lg="4">
              <b-form-group
                  label="Szabłon"
                  description="To jest nazwa szablonu. Szablon z taką nazwą musi być w resources->views"
              >
                  <b-form-input type="text" v-model="template" :state="templateState"></b-form-input>
              </b-form-group>
          </b-col>

          <b-col lg="4">
              <b-form-group
                  label="Tabela"
                  description="To nazwa tabeli z bazy danych. W tej tabeli będziemy szukac objektu przy edytowaniu strony"
              >
                  <b-form-input type="text" v-model="table" :state="tableState"></b-form-input>
              </b-form-group>
          </b-col>
      </b-row>
      <b-row>
          <b-col>
              <b-form-group
                  label="Legenda"
                  description="Ta legenda będzie się pokazywała przy edytowaniu strony. To jest tekst dla użytkownika panelu administracyjnego. Napisz tak żeby użytkownik zrozumiał co muśi wybrać"
              >
                  <b-form-input type="text" v-model="text"></b-form-input>
              </b-form-group>
          </b-col>
      </b-row>
    </b-card>
</template>

<script>
    export default {
        props : ['route', 'checkRoute', 'csrf', 'type'],

        data() {
            return {
                name: '',
                template: '',
                table: '',
                text: '',
                nameState: null,
                templateState: null,
                tableState: true,
            }
        },

        created() {
            if (this.type) {
                this.name     = this.type.name
                this.template = this.type.template
                this.table    = this.type.table
                this.text     = this.type.text
            }
        },

        methods: {
            check(key) {
                let route = this.checkRoute + '?' + key + '=' + this[key]
                if (this.type) {
                    route += '&id=' + this.type.id
                }
                axios.get(route)
                .then(res => {
                    this[key + 'State'] = res.data
                }).catch(err => {
                    console.log(err)
                })
            },

            save() {
                if (this.nameState && this.templateState && this.tableState) {
                  let formData = new FormData()
                  formData.append('_method', this.type ? 'PUT' : 'POST')
                  formData.append('name', this.name)
                  formData.append('template', this.template)
                  formData.append('table', this.table)
                  formData.append('text', this.text)

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
            name() {
                if (!this.name) {
                    this.nameState = false
                } else {
                    this.check('name')
                }
            },

            template() {
                if (!this.template) {
                    this.templateState = false
                } else {
                    this.check('template')
                }
            },

            table() {
                if (!this.table) {
                    this.tableState = true
                } else {
                    this.check('table')
                }
            }
        }
    }
</script>