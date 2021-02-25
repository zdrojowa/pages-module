<template>
  <div>
      <b-card no-body>
        <b-tabs pills card>
          <b-tab title="Strona" active>
            <b-card-text>
              <page
                  :route="route"
                  :media-search-route="mediaSearchRoute"
                  :media-route="mediaRoute"
                  :check-route="checkRoute"
                  :page-route="pageRoute"
                  :object-route="objectRoute"
                  :csrf="csrf"
                  :languages="languages"
                  :page="page"
                  :statuses="statuses"
                  :types="types"
              ></page>
            </b-card-text>
          </b-tab>
          <b-tab title="Hiro" :disabled="!page">
            <b-card-text>
              <hiro
                  v-if="page"
                  :route="route"
                  :media-search-route="mediaSearchRoute"
                  :media-route="mediaRoute"
                  :csrf="csrf"
                  :page="page"
              ></hiro>
            </b-card-text>
          </b-tab>
          <b-tab title="Galeria" :disabled="!page">
            <b-card-text>
              <gallery
                  v-if="page"
                  field="gallery"
                  :route="route"
                  :media-search-route="mediaSearchRoute"
                  :media-route="mediaRoute"
                  :icon-route="iconRoute"
                  :page-route="pageRoute"
                  :csrf="csrf"
                  :page="page"
              ></gallery>
            </b-card-text>
          </b-tab>
          <b-tab title="Highlights" :disabled="!page">
            <b-card-text>
              <gallery
                  v-if="page"
                  field="highlights"
                  :route="route"
                  :media-search-route="mediaSearchRoute"
                  :media-route="mediaRoute"
                  :icon-route="iconRoute"
                  :page-route="pageRoute"
                  :csrf="csrf"
                  :page="page"
              ></gallery>
            </b-card-text>
          </b-tab>
          <b-tab title="SEO" :disabled="!page">
            <b-card-text>
                <seo
                    v-if="page"
                    :route="route"
                    :csrf="csrf"
                    :page="page"
                ></seo>
            </b-card-text>
          </b-tab>
          <b-tab title="History" :disabled="!page">
            <b-card-text>
                <revisions
                    v-if="page"
                    table="pages"
                    :id="page.id"
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
    import Page from "./Page"
    import Hiro from "./Hiro"
    import Gallery from "./Gallery"
    import Seo from "./Seo"
    import Revisions from "./Revisions";
    export default {
        props: {
            page: Object,
            statuses: Array,
            types: Array,
            languages: Array,
            csrf: String,
            route: String,
            mediaSearchRoute: String,
            mediaRoute: String,
            checkRoute: String,
            pageRoute: String,
            objectRoute: String,
            iconRoute: String,
            revisionRoute: String,
            revisionUpdateRoute: String,
            revisionRemoveRoute: String,
        },

        components: {
            Page,
            Hiro,
            Gallery,
            Seo,
            Revisions
        }
    }
</script>

