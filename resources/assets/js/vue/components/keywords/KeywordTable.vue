<template>
  <div>
    <v-data-table
      :headers="headers"
      :items="keywords"
      :loading="isLoading"
      :pagination.sync="pagination"
      :rows-per-page-items="[50, 100, 200]"
      :total-items="keywordsCount"
      class="elevation-1"
      item-key="id">
      <tr
        @click="editKeyword(props.item.id)"
        class="clickable"
        slot="items"
        slot-scope="props">
        <td>
          {{ props.item.name }}
        </td>
        <td>
          <content-category-list
            :categories="props.item.categories"
            :type="$constants.CONTENT_CATEGORIES.TYPE_KEYWORDS" />
        </td>
        <td>
          <span class="d-block">{{ props.item.created_at | dateTime }}</span>
        </td>
        <td>
          {{ props.item.id }}
        </td>
      </tr>
    </v-data-table>
  </div>
</template>

<script>
import {mapGetters} from "vuex";

export default {
  data() {
    return {
      headers: [
        {
          text: "Schlagwort",
          value: "name",
          sortable: false,
        },
        {
          text: "Bereich",
          value: "categories",
          sortable: false,
        },
        {
          text: "Erstellt am",
          value: "created_at",
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
      keywordsCount: 'keywords/keywordsCount',
      keywords: 'keywords/keywords',
      isLoading: 'keywords/listIsLoading'
    }),
    pagination: {
      get() {
        return this.$store.state.keywords.pagination
      },
      set(data) {
        this.$store.commit('keywords/setPagination', data)
      },
    },
  },
  methods: {
    editKeyword(keywordId) {
      this.$router.push({
        name: 'keywords.edit.general',
        params: {
          keywordId: keywordId,
        },
      })
    },
    loadData() {
      this.$store.dispatch('keywords/loadKeywords')
    },
  },
}
</script>
