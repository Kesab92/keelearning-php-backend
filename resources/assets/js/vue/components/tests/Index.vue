<template>
  <div>
    <AddTestModal v-model="testModalOpen"/>
    <v-layout row>
      <v-btn
        color="primary"
        @click="testModalOpen = true">
        <v-icon
          dark
          left>add
        </v-icon>
        Neuer Test
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
              :limitToTagRights="true"
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
      <TestsTable />
    </v-card>
  </div>
</template>

<script>
import constants from "../../logic/constants"
import {debounce} from "lodash"
import TestsTable from "./TestsTable"
import TagSelect from "../partials/global/TagSelect"
import AddTestModal from "./AddTestModal"

export default {

  data() {
    return {
      testModalOpen: false,
      filters: [
        {
          text: "Sichtbar",
          value: constants.TEST.FILTER_VISIBLE,
        },
        {
          text: "Abgelaufen",
          value: constants.TEST.FILTER_EXPIRED,
        },
        {
          text: "Archiviert",
          value: constants.TEST.FILTER_ARCHIVED,
        },
        {
          text: "Abgelaufen oder Archiviert",
          value: constants.TEST.FILTER_ARCHIVED_EXPIRED,
        },
      ]
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
        return this.$store.state.tests.filter
      },
      set(data) {
        this.$store.commit('tests/setFilter', data)
      },
    },
    search: {
      get() {
        return this.$store.state.tests.search
      },
      set(data) {
        this.$store.commit('tests/setSearch', data)
      },
    },
    selectedTags: {
      get() {
        return this.$store.state.tests.tags
      },
      set(data) {
        this.$store.commit('tests/setTags', data)
      },
    },
  },
  methods: {
    getTagItems(items) {
      return [
        {
          label: "Tests ohne TAG",
          id: -1,
        },
      ].concat(items)
    },
    loadData() {
      this.$store.dispatch('tests/loadTests')
    },
  },

  components: {
    TestsTable,
    TagSelect,
    AddTestModal,
  }
}
</script>
