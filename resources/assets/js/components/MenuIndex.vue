<template>
  <index
      :route="route"
      :remove-route="removeRoute"
      :edit-route="editRoute"
      :translation-route="translationRoute"
      :csrf="csrf"
      :langs="langs"
      :fields="fields"
  ></index>
</template>

<script>
    import Index from './Index'
    export default {
        props : ['route', 'removeRoute', 'editRoute', 'translationRoute', 'csrf', 'langs'],

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
                        key: 'lang',
                        sortable: true,
                        label: 'Język',
                    },
                    {
                        key: 'translations',
                        sortable: true,
                        label: 'Tłumaczenia',
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

