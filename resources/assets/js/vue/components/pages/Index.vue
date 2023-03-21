<template>
  <div>
    <ModuleIntro/>
    <AddPageModal v-model="pagesModalOpen"/>
    <v-layout row>
      <v-btn
        color="primary"
        @click="pagesModalOpen = true">
        <v-icon
          dark
          left>add
        </v-icon>
        Neue Seite
      </v-btn>
    </v-layout>
    <v-card class="mt-2 mb-4">
      <v-card-title primary-title>
        <v-layout row>
          <v-spacer />
          <v-flex xs4>
            <tag-select
              class="mr-4"
              v-model="selectedTags"
              :extend-items="getTagItems"
              :limitToTagRights="true"
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
      <PagesTable/>
    </v-card>
    <PageSidebar/>
  </div>
</template>

<script>
import ModuleIntro from "./ModuleIntro"
import PagesTable from "./PagesTable"
import AddPageModal from "./AddPageModal"
import PageSidebar from "./PageSidebar"
import TagSelect from "../partials/global/TagSelect"

import {debounce} from "lodash";

export default {
  data() {
    return {
      pagesModalOpen: false,
    }
  },
  watch: {
    search: debounce(function () {
      this.loadData()
    }, 1000),
    selectedTags() {
      this.loadData()
    },
  },
  computed: {
    search: {
      get() {
        return this.$store.state.pages.search
      },
      set(data) {
        this.$store.commit('pages/setSearch', data)
      },
    },
    selectedTags: {
      get() {
        return this.$store.state.pages.tags
      },
      set(data) {
        this.$store.commit('pages/setTags', data)
      },
    },
  },
  methods: {
    getTagItems(items) {
      return [
        {
          label: "Seiten ohne TAG",
          id: -1,
        },
      ].concat(items)
    },
    loadData() {
      this.$store.dispatch('pages/loadPages')
    },
  },
  components: {
    ModuleIntro,
    PagesTable,
    AddPageModal,
    PageSidebar,
    TagSelect,
  }
}
</script>
