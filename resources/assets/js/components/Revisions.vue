<template>
  <b-card no-header>
    <b-row>
        <b-col lg="4" class="my-1">
            <b-form-group
                label="Wyników na stronie"
            >
                <b-form-select
                    v-model="limit"
                    :options="pageOptions"
                ></b-form-select>
            </b-form-group>
            <b-pagination
                v-model="page"
                :total-rows="totalRows"
                :per-page="limit"
                align="fill"
            ></b-pagination>
        </b-col>
    </b-row>
    <b-table
        striped
        hover
        :responsive="true"
        :items="items"
        :fields="fields"
        :current-page="page"
        :per-page="limit"
    >
      <template #cell(created_at)="row">
          {{ row.item.created_at | moment('LL hh:mm:ss') }}
      </template>
      <template #cell(user)="row">
        {{ users[row.item.user_id] }}
      </template>
      <template #cell(actions)="row">
        <b-button @click="update(row.item.id)" variant="primary">
          <b-icon-clock-history></b-icon-clock-history>
        </b-button>
        <b-button @click="remove(row.item.id)" variant="danger">
          <b-icon-trash></b-icon-trash>
        </b-button>
      </template>
    </b-table>
  </b-card>
</template>

<script>
export default {
  props: ['table', 'id', 'route', 'removeRoute', 'updateRoute', 'csrf'],

  data() {
    return {
      items: [],
      users: {},
      limit: 10,
      page: 1,
      totalRows: 1,
      pageOptions: [10, 50, 100],
      fields: [
        {
          key: 'created_at',
          sortable: true,
          label: 'Data'
        },
        {
          key: 'action',
          sortable: true,
          label: 'Akcja'
        },
        {
          key: 'user',
          sortable: true,
          label: 'Użytkownik'
        },
        {
          key: 'actions',
          label: ''
        }
      ],
    }
  },

  mounted() {
    this.get()
  },

  methods: {

    get() {
      let self = this
      axios.get(this.route + '?table=' + this.table + '&contentId=' + this.id)
      .then(function(response) {
        self.items     = response.data.revisions
        self.users     = response.data.users
        self.totalRows = self.items.length
        self.page      = 1
      }).catch(function(error) {
        console.log(error)
      })
    },

    remove(id) {
      let self = this
      this.$bvModal.msgBoxConfirm('Czy jesteś pewny?', {
        title: 'Usunąć wersję',
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
          let route = self.removeRoute.replace('id', id)
          axios.delete(route, {
            headers: {
              'X-CSRF-TOKEN': self.csrf
            }
          })
          .then(function(response) {
            self.get()
          }).catch(function(error) {
            self.$bvToast.toast('Error: ' + error.message, {
              title: 'History',
              variant: 'danger',
              solid: true
            })
          })
        }
      })
      .catch(err => {
        console.log(err)
      })
    },

    update(id) {
      let self = this
      this.$bvModal.msgBoxConfirm('Czy jesteś pewny?', {
          title: 'Wrócić do wersji',
          size: 'sm',
          buttonSize: 'sm',
          okVariant: 'warning',
          okTitle: 'Tak',
          cancelTitle: 'Nie',
          footerClass: 'p-2',
          hideHeaderClose: false,
          centered: true
      })
      .then(value => {
          if (value) {
              let route = self.updateRoute.replace('id', id)
              axios.post(route, {
                  headers: {
                      'X-CSRF-TOKEN': self.csrf
                  }
              })
              .then(function(response) {
                  window.location = response.data.redirect
              }).catch(function(error) {
                  self.$bvToast.toast('Error: ' + error.message, {
                      title: 'History',
                      variant: 'danger',
                      solid: true
                  })
              })
          }
      })
      .catch(err => {
          console.log(err)
      })
    },
  }
}
</script>