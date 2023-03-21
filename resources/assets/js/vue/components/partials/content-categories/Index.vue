<template>
  <div>
    <AddContentCategoryModal
      v-if="!readOnly"
      v-model="categoryModalOpen"
      :edit-route-name="editRouteName"
      :type="type"/>
    <v-layout row>
      <v-btn
        color="primary"
        :disabled="readOnly"
        @click="categoryModalOpen = true">
        <v-icon
          dark
          left>add
        </v-icon>
        Neue Kategorie
      </v-btn>
    </v-layout>
    <v-card class="mt-2 mb-4">
      <v-card-title primary-title>
        <v-layout row>
          <v-flex xs4>
            <v-text-field
              v-model="search"
              append-icon="search"
              clearable
              placeholder="Name / ID"
              single-line/>
          </v-flex>
        </v-layout>
      </v-card-title>
      <v-data-table
        :headers="headers"
        :items="categories"
        :loading="isLoading"
        :pagination="pagination"
        :rows-per-page-items="[1000]"
        :total-items="categories.length"
        class="elevation-1"
        item-key="id">
        <tr
          slot="items"
          slot-scope="props"
          class="clickable"
          @click="editCategory(props.item.id)">
          <td>
            {{ props.item.name }}
          </td>
          <td>
            {{ props.item.content_category_relations_count }}
          </td>
          <td>
            {{ props.item.id }}
          </td>
        </tr>
        <template slot="no-data">
          <v-alert
            v-if="!isLoading && categories.length === 0"
            :value="true"
            type="info">
            Es wurden keine Kategorien gefunden.
          </v-alert>
        </template>
      </v-data-table>
    </v-card>
    <ContentCategorySidebar
      :type="type"
      :read-only="readOnly"
      :root-route-name="this.rootRouteName"
      :edit-route-name="this.editRouteName" />
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import {debounce} from "lodash"
import AddContentCategoryModal from "./AddContentCategoryModal"
import ContentCategorySidebar from "./ContentCategorySidebar"

export default {
  props: {
    type: {
      type: String,
      required: true,
    },
    editRouteName: {
      type: String,
      required: true,
    },
    rootRouteName: {
      type: String,
      required: true,
    },
    readOnly: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data() {
    let relationsLabel = 'Verknüpfungen'
    if(this.type === 'courses') {
      relationsLabel = 'Kurse'
    }
    return {
      categoryModalOpen: false,
      headers: [
        {
          text: "Name",
          value: "title",
          width: "200px",
          sortable: false,
        },
        {
          text: relationsLabel,
          value: "content_category_relations_count",
          sortable: false,
        },
        {
          text: "ID",
          value: "id",
          width: "200px",
          sortable: true,
        },
      ],
    }
  },
  created() {
    this.loadData()
  },
  computed: {
    ...mapGetters({
      getCategories: "contentCategories/getCategories",
      isLoading: "contentCategories/listIsLoading",
    }),
    search: {
      get() {
        return this.$store.state.contentCategories.search
      },
      set(data) {
        this.$store.commit("contentCategories/setSearch", data)
      },
    },
    pagination: {
      get() {
        return this.$store.state.contentCategories.pagination
      },
      set(data) {
        this.$store.commit("contentCategories/setPagination", data)
      },
    },
    categories() {
      let categories = this.getCategories(this.type)
      if (typeof categories === "undefined" || categories === null) {
        return []
      }
      return categories
    },
  },
  watch: {
    pagination: {
      handler() {
        this.loadData()
      },
      deep: true,
    },
    search: debounce(function () {
      this.loadData()
    }, 500),
  },
  methods: {
    loadData() {
      this.$store.dispatch("contentCategories/updateCategories", this.type)
    },
    editCategory(categoryId) {
      this.$router.push({
        name: this.editRouteName,
        params: {
          categoryId,
        },
      })
    },
  },
  components: {
    ContentCategorySidebar,
    AddContentCategoryModal,
  },
}
</script>
