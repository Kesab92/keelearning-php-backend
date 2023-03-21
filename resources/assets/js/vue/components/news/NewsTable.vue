<template>
  <div>
    <v-data-table
      :headers="headers"
      :items="news"
      :loading="isLoading"
      :pagination.sync="pagination"
      :rows-per-page-items="[50, 100, 200]"
      :total-items="newsCount"
      class="elevation-1"
      item-key="id">
      <tr
        @click="editNews(props.item.id)"
        class="clickable"
        slot="items"
        slot-scope="props">
        <td class="pa-2 pr-0">
          <img
            v-if="props.item.cover_image_url"
            class="s-news__coverImage"
            :src="props.item.cover_image_url"/>
          <img
            v-else
            class="s-news__coverImage"
            src="/img/no-connection.svg"
            style="object-fit: contain"/>
        </td>
        <td>
          {{ props.item.title }}
        </td>
        <td>
          <v-chip
            :key="`${props.item.id}-${tag.id}`"
            disabled
            small
            v-for="tag in props.item.tags">
            {{ tag.label }}
          </v-chip>
        </td>
        <td>
          {{ props.item.likes }}
        </td>
        <td>
          {{ props.item.viewcount_total }}
        </td>
        <td>
          <span class="d-block">{{ props.item.published_at | dateTime }}</span>
          <v-chip
            v-if="isVisible(props.item)"
            disabled
            small
            text-color="white"
            color="green">
            Sichtbar
          </v-chip>
          <v-chip
            v-if="isNotYetVisible(props.item)"
            disabled
            small
            text-color="white"
            color="orange">
            Noch nicht sichtbar
          </v-chip>
          <v-chip
            v-if="isNoLongerVisible(props.item)"
            disabled
            small
            color="gray">
            Nicht mehr sichtbar
          </v-chip>
        </td>
        <td>
          {{ props.item.id }}
        </td>
      </tr>
    </v-data-table>
  </div>
</template>

<script>
import moment from 'moment'
import {mapGetters} from "vuex";

export default {
  data() {
    return {
      headers: [
        {
          text: "",
          value: "image",
          width: "110px",
          sortable: false,
        },
        {
          text: "Titel",
          value: "title",
          sortable: false,
        },
        {
          text: "Benutzergruppen",
          value: "tags",
          sortable: false,
        },
        {
          text: "Likes",
          value: "likes",
          sortable: false,
        },
        {
          text: "Aufrufe",
          value: "viewcount_total",
          sortable: false,
        },
        {
          text: "Veröffentlicht am",
          value: "published_at",
          width: "280px",
        },
        {
          text: "ID",
          value: "id",
          width: "90px",
        },
      ],
    }
  },
  watch: {
    pagination: {
      handler() {
        this.loadData()
      },
      deep: true,
    },
  },
  computed: {
    ...mapGetters({
      newsCount: 'news/newsCount',
      news: 'news/news',
      isLoading: 'news/listIsLoading'
    }),
    pagination: {
      get() {
        return this.$store.state.news.pagination
      },
      set(data) {
        this.$store.commit('news/setPagination', data)
      },
    },
  },
  methods: {
    editNews(newsId) {
      this.$router.push({
        name: 'news.edit.general',
        params: {
          newsId: newsId,
        },
      })
    },
    loadData() {
      this.$store.dispatch('news/loadNews')
    },
    isVisible(newsEntry) {
      const publishedAt = moment(newsEntry.published_at)
      const activeUntil = moment(newsEntry.active_until)
      const today = moment().startOf('day')
      if (newsEntry.published_at !== null && today.isSameOrAfter(publishedAt) && (newsEntry.active_until === null || today.isSameOrBefore(activeUntil)) ) {
        return true
      }
      return false
    },
    isNotYetVisible(newsEntry) {
      const publishedAt = moment(newsEntry.published_at)
      const today = moment().startOf('day')
      if (newsEntry.published_at === null || today.isBefore(publishedAt)) {
        return true
      }
      return false
    },
    isNoLongerVisible(newsEntry) {
      const publishedAt = moment(newsEntry.published_at)
      const activeUntil = moment(newsEntry.active_until)
      const today = moment().startOf('day')
      if (newsEntry.published_at !== null && newsEntry.active_until !== null && today.isSameOrAfter(publishedAt) && today.isAfter(activeUntil)) {
        return true
      }
      return false
    },
  },
}
</script>

<style scoped>
#app .s-news__coverImage {
  width: 110px;
  display: block;
  height: 100%;
  min-height: 70px;
  max-height: 150px;
  object-fit: cover;
}
</style>
