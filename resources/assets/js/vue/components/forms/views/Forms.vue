<template>
  <div>
    <ModuleIntro>
      <template v-slot:title>
        Formulare
      </template>
      <template v-slot:description>
        Erstellen und verwalten Sie online und offline Termine.
      </template>
      <template v-slot:links>
        <v-btn
          flat
          color="primary"
          small
          href="https://helpdesk.keelearning.de/de/articles/6272426-uber-formulare"
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
    <FormTabs />
    <AddFormModal
      v-if="myRights['forms-edit']"
      v-model="addFormModalOpen" />
    <v-btn
      :disabled="!myRights['forms-edit']"
      color="primary"
      @click="addFormModalOpen = true">
      <v-icon
        dark
        left>add
      </v-icon>
      Neues Formular
    </v-btn>
    <v-card class="mt-2 mb-4">
      <v-card-title primary-title>
        <v-layout row>
          <v-flex xs3>
            <v-select
              :items="filters"
              class="mr-4"
              clearable
              label="Filter"
              v-model="filter"/>
          </v-flex>
          <v-flex xs3>
            <tag-select
              class="mr-4"
              v-model="selectedTags"
              :extend-items="getTagItems"
              limitToTagRights
              multiple />
          </v-flex>
          <v-flex xs3>
            <content-category-select
              class="mr-4"
              v-model="selectedCategories"
              placeholder=""
              type="forms"
              hideCreation
              multiple />
          </v-flex>
          <v-flex xs3>
            <v-text-field
              append-icon="search"
              clearable
              placeholder="Name / ID"
              single-line
              v-model="search"/>
          </v-flex>
        </v-layout>
      </v-card-title>
      <FormTable />
    </v-card>
    <FormSidebar />
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import {debounce} from "lodash"
import TagSelect from "../../partials/global/TagSelect"
import ModuleIntro from "../../partials/global/ModuleIntro"
import FormTabs from "../components/FormTabs"
import FormTable from "../components/FormTable"
import AddFormModal from "../components/AddFormModal"
import FormSidebar from "../FormSidebar"
import constants from "../../../logic/constants"

export default {
  data() {
    return {
      addFormModalOpen: false,
      filters: [
        {
          text: "Sichtbar / in Bearbeitung",
          value: constants.FORMS.FILTERS.ACTIVE,
        },
        {
          text: "Archiviert",
          value: constants.FORMS.FILTERS.ARCHIVED,
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
    selectedCategories() {
      this.loadData()
    },
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    filter: {
      get() {
        return this.$store.state.forms.filter
      },
      set(data) {
        this.$store.commit('forms/setFilter', data)
      },
    },
    search: {
      get() {
        return this.$store.state.forms.search
      },
      set(data) {
        this.$store.commit('forms/setSearch', data)
      },
    },
    selectedTags: {
      get() {
        return this.$store.state.forms.tags
      },
      set(data) {
        this.$store.commit('forms/setTags', data)
      },
    },
    selectedCategories: {
      get() {
        return this.$store.state.forms.categories
      },
      set(data) {
        this.$store.commit('forms/setCategories', data)
      },
    },
  },
  methods: {
    getTagItems(items) {
      return [
        {
          label: "Formulare ohne TAG",
          id: -1,
        },
      ].concat(items)
    },
    loadData() {
      this.$store.dispatch('forms/loadForms')
    },
  },
  components: {
    AddFormModal,
    FormTabs,
    FormTable,
    ModuleIntro,
    TagSelect,
    FormSidebar,
  }
}
</script>

<style lang="scss">
.ui.segment.menu {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
}

</style>
