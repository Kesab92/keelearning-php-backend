<template>
  <div>
    <v-data-table
      :headers="headers"
      :items="pages"
      :loading="isLoading"
      :rows-per-page-items="[50, 100, 200]"
      :total-items="pagesCount"
      class="elevation-1"
      item-key="id">
      <tr
        @click="editPage(props.item.id)"
        :class="{
          'grey lighten-1': props.item.parent_id,
          's-pages__row -shadow': props.item.hasSubpages === true,
        }"
        class="clickable "
        slot="items"
        slot-scope="props">
        <td>
          {{ props.item.title }}
        </td>
        <td v-if="appSettings.has_subpages == 1">
          <v-chip
            :key="`${props.item.id}-${tag.id}`"
            disabled
            small
            v-for="tag in props.item.tags">
            {{ tag.label }}
          </v-chip>
        </td>
        <td>
          <v-icon
            v-if="props.item.visible"
            color="success"
          >
            done
          </v-icon>
          <v-icon v-else>
            close
          </v-icon>
        </td>
        <td>
          <v-icon
            v-if="props.item.public"
            color="success"
          >
            done
          </v-icon>
          <v-icon v-else>
            close
          </v-icon>
        </td>
      </tr>
    </v-data-table>
  </div>
</template>

<script>
import {mapGetters} from "vuex";

export default {
  computed: {
    ...mapGetters({
      pagesCount: 'pages/pagesCount',
      pages: 'pages/pages',
      isLoading: 'pages/listIsLoading',
      appSettings: 'app/appSettings',
    }),
    headers() {
      let headers = [{
        text: "Titel",
        value: "title",
        sortable: false,
      }]

      if (this.appSettings.has_subpages == 1) {
        headers.push({
          text: "Benutzergruppen",
          value: "tags",
          sortable: false,
        })
      }
      headers.push({
          text: "Sichtbar",
          value: "visible",
          sortable: false,
        },
        {
          text: "Öffentlich",
          value: "public",
          sortable: false,
        })
      return headers
    },
  },
  created() {
    this.loadData()
  },
  methods: {
    editPage(pageId) {
      this.$router.push({
        name: 'pages.edit.general',
        params: {
          pageId: pageId,
        },
      })
    },
    loadData() {
      this.$store.dispatch('pages/loadPages')
    },
  },
}
</script>

<style lang="scss">
#app .s-pages__row {
  &.-shadow {
    transform: scale(1, 1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  }
}
</style>
