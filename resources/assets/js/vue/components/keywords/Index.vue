<template>
  <div>
    <ModuleIntro />
    <KeywordTabs />
    <AddKeywordModal v-model="keywordModalOpen" />
    <v-layout row>
      <v-btn
        color="primary"
        @click="keywordModalOpen = true">
        <v-icon
          dark
          left>add
        </v-icon>
        Neues Schlagwort
      </v-btn>
    </v-layout>
    <v-card class="mt-2 mb-4">
      <v-card-title primary-title>
        <v-layout row>
          <v-spacer></v-spacer>
          <v-flex xs4>
            <content-category-select
              class="mr-4"
              v-model="selectedCategories"
              :type="$constants.CONTENT_CATEGORIES.TYPE_KEYWORDS"
              multiple />
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
      <KeywordTable />
    </v-card>
    <KeywordSidebar />
  </div>
</template>

<script>
import {debounce} from "lodash"
import ModuleIntro from "./ModuleIntro"
import AddKeywordModal from "./AddKeywordModal"
import KeywordTable from "./KeywordTable"
import KeywordSidebar from "./KeywordSidebar"
import KeywordTabs from "./KeywordTabs"

export default {
  data() {
    return {
      keywordModalOpen: false,
    }
  },
  watch: {
    search: debounce(function () {
      this.loadData()
    }, 1000),
    selectedCategories() {
      this.loadData()
    },
  },
  computed: {
    search: {
      get() {
        return this.$store.state.keywords.search
      },
      set(data) {
        this.$store.commit('keywords/setSearch', data)
      },
    },
    selectedCategories: {
      get() {
        return this.$store.state.keywords.categories
      },
      set(data) {
        this.$store.commit('keywords/setCategories', data)
      },
    },
  },
  methods: {
    editKeyword(keywordId) {
      this.$router.push({
        name: 'keywords.edit.general',
        params: {
          keywordId,
        },
      })
    },
    loadData() {
      this.$store.dispatch('keywords/loadKeywords')
    },
  },
  components:{
    AddKeywordModal,
    KeywordTable,
    KeywordSidebar,
    KeywordTabs,
    ModuleIntro,
  }
}
</script>
