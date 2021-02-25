<template>
  <index
      :route="route"
      :remove-route="removeRoute"
      :edit-route="editRoute"
      :csrf="csrf"
      :fields="fields"
  ></index>
</template>

<script>
    import Index from './Index'
    export default {
        props : ['route', 'removeRoute', 'editRoute', 'csrf'],

        components: {
          Index
        },

        data() {
            return {
                fields: [
                    {
                        key: 'name',
                        sortable: true,
                        variant: 'info',
                        label: 'Nazwa'
                    },
                    {
                        key: 'template',
                        sortable: true,
                        label: 'Szabłon'
                    },
                    {
                        key: 'table',
                        sortable: true,
                        label: 'Tabela'
                    },
                    {
                        key: 'actions',
                        label: ''
                    }
                ]
            }
        }
    }
</script>

