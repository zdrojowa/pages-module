<template>
  <b-form-group
      label="Strona"
  >
      <multiselect
          v-model="item"
          track-by="id"
          label="name"
          placeholder="Zaczni pisać"
          :options="pages"
          :searchable="true"
          @select="select"
      >
      </multiselect>
  </b-form-group>
</template>

<script>
    import MultiSelect from 'vue-multiselect'
    export default {
        props : ['page', 'pages', 'route'],

        components: {
            'multiselect': MultiSelect
        },

        data: function () {
            return {
                item: null
            }
        },

        mounted: function() {
            if (this.page) {
                this.getPage(this.page)
                .then(response => {
                    if (response && response.data) {
                        this.item = response.data
                    }
                })
            }
        },

        methods: {
            getPage(id) {
                return axios.get(this.route + '?id=' + id + '&select=id,name')
                .catch(err => {
                    console.log(err)
                })
            },

            select(selectedOption, id) {
                this.$emit('select', selectedOption)
            }
        }
    }
</script>