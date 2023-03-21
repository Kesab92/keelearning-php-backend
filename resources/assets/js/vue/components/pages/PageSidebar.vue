<template>
  <details-sidebar
    :root-url="{
      name: 'pages.index',
    }"
    :drawer-open="typeof $route.params.pageId !== 'undefined'"
    data-action="pages/loadPage"
    :data-getter="(params) => $store.getters['pages/page'](params.pageId)"
    :data-params="{pageId: $route.params.pageId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: page, refresh }">
      <router-view
        :page="page"
        @refresh="refresh"
      />
    </template>
    <template v-slot:headerTitle="{ data: page }">
      {{ page.title }}
    </template>
    <template v-slot:headerExtension="{ data: page }">
      <div v-if="page.parent_id">
        Sub-Seite
      </div>
      <div v-else>
        Seite
      </div>
      Zuletzt bearbeitet: {{ page.updated_at | dateTime }}
    </template>
  </details-sidebar>
</template>

<script>
import {mapGetters} from "vuex";

export default {
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
    }),
    page: {
      get() {
        return this.$store.getters['pages/page'](this.$route.params.pageId);
      },
    },
  },
  methods: {
    getLinks(page) {
      let tabs = [
        {
          label: 'Allgemein',
          to: {
            name: 'pages.edit.general',
            params: {
              pageId: page.id,
            },
          },
        }];
      if (this.appSettings.has_subpages == 1 && !this.page.hasSubPages) {
        tabs.push({
          label: 'Sub-Seite',
          to: {
            name: 'pages.edit.subpage',
            params: {
              pageId: page.id,
            },
          },
        })
      }
      tabs.push({
        label: 'Inhalt',
        to: {
          name: 'pages.edit.content',
          params: {
            pageId: page.id,
          },
        },
      })
      return tabs
    }
  }
}
</script>
