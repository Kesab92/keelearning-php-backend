<template>
  <div>
    <ModuleIntro/>
    <AddNewsModal v-model="newsModalOpen"/>
    <v-layout row>
      <v-btn
        color="primary"
        @click="newsModalOpen = true">
        <v-icon
          dark
          left>add
        </v-icon>
        Neue News
      </v-btn>
    </v-layout>
    <v-card class="mt-2 mb-4">
      <v-card-title primary-title>
        <v-layout row>
          <v-flex xs4>
            <v-select
              :items="filters"
              class="mr-4"
              clearable
              label="Filter"
              v-model="filter"/>
          </v-flex>
          <v-flex xs4>
            <tag-select
              class="mr-4"
              v-model="selectedTags"
              :extend-items="getTagItems"
              limitToTagRights
              multiple/>
          </v-flex>
          <v-flex xs4>
            <v-text-field
              append-icon="search"
              clearable
              placeholder="Name / ID"
              single-line
              v-model="search"/>
          </v-flex>
        </v-layout>
      </v-card-title>
      <NewsTable/>
    </v-card>
    <NewsSidebar/>
  </div>
</template>

<script>
import ModuleIntro from "./ModuleIntro"
import NewsTable from "./NewsTable"
import AddNewsModal from "./AddNewsModal"
import NewsSidebar from "./NewsSidebar"
import {debounce} from "lodash"
import TagSelect from "../partials/global/TagSelect"
import constants from "../../logic/constants"

export default {
  data() {
    return {
      newsModalOpen: false,
      filters: [
        {
          text: "Sichtbar / in Bearbeitung",
          value: constants.NEWS.FILTER_ACTIVE,
        },
        {
          text: "Sichtbar",
          value: constants.NEWS.FILTER_VISIBLE,
        },
        {
          text: "Abgelaufen",
          value: constants.NEWS.FILTER_EXPIRED,
        },
      ],
    }
  },
  watch: {
    search: debounce(function () {
      this.loadData()
    }, 1000),
    filter() {
      this.loadData()
    },
    selectedTags() {
      this.loadData()
    },
  },
  computed: {
    filter: {
      get() {
        return this.$store.state.news.filter
      },
      set(data) {
        this.$store.commit('news/setFilter', data)
      },
    },
    search: {
      get() {
        return this.$store.state.news.search
      },
      set(data) {
        this.$store.commit('news/setSearch', data)
      },
    },
    selectedTags: {
      get() {
        return this.$store.state.news.tags
      },
      set(data) {
        this.$store.commit('news/setTags', data)
      },
    },
  },
  methods: {
    getTagItems(items) {
      return [
        {
          label: "News ohne TAG",
          id: -1,
        },
      ].concat(items)
    },
    loadData() {
      this.$store.dispatch('news/loadNews')
    },
  },
  components: {
    ModuleIntro,
    NewsTable,
    TagSelect,
    AddNewsModal,
    NewsSidebar,
  }
}
</script>
