<template>
  <div>
    <ModuleIntro>
      <template v-slot:title>
        TAGs
      </template>
      <template v-slot:description>
        Über Benutzergruppen/TAGs kann der Zugriff auf Inhalten beschränkt werden.
      </template>
      <template v-slot:links>
        <v-btn
          flat
          color="primary"
          small
          href="https://helpdesk.keelearning.de/de/articles/4233247-uber-tags"
          target="_blank"
        >
          <v-icon
            small
            class="mr-1">
            help
          </v-icon>
          Anleitung öffnen
        </v-btn>
      </template>
    </ModuleIntro>
    <TagTabs />
    <AddTagModal v-model="tagModalOpen" />
    <v-layout row>
      <v-btn
        color="primary"
        @click="tagModalOpen = true">
        <v-icon
          dark
          left>add
        </v-icon>
        Neuer TAG
      </v-btn>
    </v-layout>
    <v-card class="mt-2 mb-4">
      <v-card-title primary-title>
        <v-layout row>
          <v-flex xs4>
            <content-category-select
              class="mr-4"
              v-model="selectedCategories"
              :type="$constants.CONTENT_CATEGORIES.TYPE_TAGS"
              multiple />
          </v-flex>
          <v-flex xs4>

            <v-select
              v-model="filter"
              :items="filters"
              class="mr-4"
              single-line
              hide-details
              label="Filter"
              item-value="value"
              item-text="name" />
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
      <TagTable />
    </v-card>
    <TagSidebar />
  </div>
</template>

<script>
import {debounce} from "lodash"
import {mapGetters} from "vuex"
import ModuleIntro from "../partials/global/ModuleIntro"
import AddTagModal from "./AddTagModal"
import TagTable from "./TagTable"
import TagSidebar from "./TagSidebar"
import TagTabs from "./TagTabs"

export default {
  data() {
    return {
      tagModalOpen: false,
    }
  },
  watch: {
    search: debounce(function () {
      this.loadData()
    }, 1000),
    filter() {
      this.loadData()
    },
    selectedCategories() {
      this.loadData()
    },
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
    }),
    filter: {
      get() {
        return this.$store.state.tags.filter
      },
      set(data) {
        this.$store.commit('tags/setFilter', data)
      },
    },
    search: {
      get() {
        return this.$store.state.tags.search
      },
      set(data) {
        this.$store.commit('tags/setSearch', data)
      },
    },
    selectedCategories: {
      get() {
        return this.$store.state.tags.contentcategories
      },
      set(data) {
        this.$store.commit('tags/setContentCategories', data)
      },
    },
    filters() {
      let filters = [
        this.$constants.TAGS.FILTERS.FILTER_ALL,
        this.$constants.TAGS.FILTERS.FILTER_NONE,
      ]

      let moduleMappings = {
        module_courses: this.$constants.TAGS.FILTERS.FILTER_COURSE,
        module_tests: this.$constants.TAGS.FILTERS.FILTER_TEST,
        module_learningmaterials: this.$constants.TAGS.FILTERS.FILTER_LEARNINGMATERIAL,
        module_quiz: this.$constants.TAGS.FILTERS.FILTER_QUIZ,
        module_powerlearning: this.$constants.TAGS.FILTERS.FILTER_POWERLEARNING,
        module_index_cards: this.$constants.TAGS.FILTERS.FILTER_INDEX_CARD,
        module_advertisements: this.$constants.TAGS.FILTERS.FILTER_ADVERTISEMENT,
        module_news: this.$constants.TAGS.FILTERS.FILTER_NEWS,
        module_webinars: this.$constants.TAGS.FILTERS.FILTER_WEBINAR,
        has_subpages: this.$constants.TAGS.FILTERS.FILTER_PAGE,
      }

      Object.keys(moduleMappings).forEach(module => {
        if(this.appSettings[module] == 1) {
          filters.push(moduleMappings[module])
        }
      })


      return filters
    }
  },
  methods: {
    editTag(tagId) {
      this.$router.push({
        name: 'tags.edit.general',
        params: {
          tagId,
        },
      })
    },
    loadData() {
      this.$store.dispatch('tags/loadTags')
    },
  },
  components:{
    AddTagModal,
    TagTable,
    TagSidebar,
    ModuleIntro,
    TagTabs,
  }
}
</script>

<style scoped>

</style>
