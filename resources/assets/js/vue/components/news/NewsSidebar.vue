<template>
  <details-sidebar
    :root-url="{ name: rootUrl }"
    :drawer-open="typeof $route.params.newsId !== 'undefined'"
    data-action="news/loadNewsEntry"
    :data-getter="(params) => $store.getters['news/newsEntry'](params.newsId)"
    :data-params="{newsId: $route.params.newsId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: newsEntry, refresh }">
      <router-view
        :news-entry="newsEntry"
        @refresh="refresh"
      />
    </template>
    <template v-slot:headerTitle="{ data: newsEntry }">
      {{ newsEntry.title }}
    </template>
    <template v-slot:headerExtension="{ data: newsEntry }">
      Typ: News<br>
      Erstellt am {{ newsEntry.created_at | date }}<br>
      <v-tooltip bottom>
        <template slot="activator">
          <span>
            <v-icon small>visibility</v-icon>
            {{ newsEntry.viewcount_total }}
          </span>
        </template>
        <span>Aufrufe</span>
      </v-tooltip>
      <v-tooltip bottom>
        <template slot="activator">
          <span>
            <v-icon class="ml-2" small>favorite</v-icon>
            {{ newsEntry.likes }}
          </span>
        </template>
        <span>Likes</span>
      </v-tooltip>
    </template>

  </details-sidebar>
</template>

<script>
import {mapGetters} from 'vuex'

export default {
  props: {
    rootUrl: {
      default: 'news.index',
      required: false,
      type: String,
    },
    routePrefix: {
      default: '',
      required: false,
      type: String,
    },
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
      myRights: 'app/myRights',
    }),
  },
  methods: {
    getLinks(newsEntry) {
      let tabs = [
        {
          label: 'Allgemein',
          to: {
            name: `${this.routePrefix}news.edit.general`,
            params: {
              newsId: newsEntry.id,
            },
          },
        },
        {
          label: 'Design',
          to: {
            name: `${this.routePrefix}news.edit.design`,
            params: {
              newsId: newsEntry.id,
            },
          },
        }]
      if(this.appSettings.module_comments == 1 && this.myRights['comments-personaldata']) {
        tabs.push({
          label: 'Kommentare',
          to: {
            name: `${this.routePrefix}news.edit.comments`,
            params: {
              newsId: newsEntry.id,
            },
          },
        })
      }
      return tabs
    }
  }
}
</script>
