<template>
  <div>
    <ModuleIntro />
    <AddAdvertisementModal v-model="advertisementModalOpen" />
    <v-layout row>
      <v-btn
        color="primary"
        @click="advertisementModalOpen = true">
        <v-icon
          dark
          left>add
        </v-icon>
        Neuer Banner
      </v-btn>
    </v-layout>
    <v-card class="mt-2 mb-4">
      <v-card-title primary-title>
        <v-layout row>
          <v-flex xs4>
            <tag-select
              class="mr-4"
              v-model="selectedTags"
              :extend-items="getTagItems"
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
      <AdvertisementTable />
    </v-card>
    <AdvertisementSidebar />
  </div>
</template>

<script>
import {debounce} from "lodash"
import TagSelect from "../partials/global/TagSelect"
import ModuleIntro from "./ModuleIntro"
import AddAdvertisementModal from "./AddAdvertisementModal"
import AdvertisementSidebar from "./AdvertisementSidebar"
import AdvertisementTable from "./AdvertisementTable"

export default {
  data() {
    return {
      advertisementModalOpen: false,
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
        return this.$store.state.advertisements.search
      },
      set(data) {
        this.$store.commit('advertisements/setSearch', data)
      },
    },
    selectedTags: {
      get() {
        return this.$store.state.advertisements.tags
      },
      set(data) {
        console.log(data)
        this.$store.commit('advertisements/setTags', data)
      },
    },
  },
  methods: {
    getTagItems(items) {
      return [
        {
          label: "Banner ohne TAG",
          id: -1,
        },
      ].concat(items)
    },
    editAdvertisement(advertisementId) {
      this.$router.push({
        name: 'advertisements.edit.general',
        params: {
          advertisementId,
        },
      })
    },
    loadData() {
      this.$store.dispatch('advertisements/loadAdvertisements')
    },
  },
  components: {
    AdvertisementSidebar,
    AddAdvertisementModal,
    ModuleIntro,
    TagSelect,
    AdvertisementTable,
  }
}
</script>
