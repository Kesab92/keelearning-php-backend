<template>
  <div>
    <ModuleIntro>
      <template v-slot:title>
        Termine
      </template>
      <template v-slot:description>
        Erstellen und verwalten Sie online und offline Termine.
      </template>
      <template v-slot:links>
        <v-btn
          flat
          color="primary"
          small
          href="https://helpdesk.keelearning.de/de/articles/5945912-uber-termine"
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
    <AddAppointmentModal v-model="appointmentModalOpen"/>
    <v-layout row>
      <v-btn
        v-if="myRights['appointments-edit']"
        color="primary"
        @click="appointmentModalOpen = true">
        <v-icon
          dark
          left>add
        </v-icon>
        Neuer Termin
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
              placeholder="Name / Datum"
              single-line
              v-model="search"/>
          </v-flex>
        </v-layout>
      </v-card-title>
      <AppointmentTable />
    </v-card>
    <AppointmentSidebar />
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import {debounce} from "lodash"
import AppointmentTable from "./AppointmentTable"
import TagSelect from "../partials/global/TagSelect"
import constants from "../../logic/constants"
import ModuleIntro from "../partials/global/ModuleIntro"
import AppointmentSidebar from "./AppointmentSidebar"
import AddAppointmentModal from "./AddAppointmentModal"
import tableConfig from "../../mixins/tableConfig"

export default {
  mixins: [
    tableConfig,
  ],
  data() {
    return {
      appointmentModalOpen: false,
      filters: [
        {
          text: "Sichtbar / in Bearbeitung",
          value: constants.APPOINTMENTS.FILTER_ACTIVE,
        },
        {
          text: "Abgelaufen",
          value: constants.APPOINTMENTS.FILTER_EXPIRED,
        },
        {
          text: "Ohne Teilnehmer",
          value: constants.APPOINTMENTS.FILTER_WITHOUT_PARTICIPANTS,
        },
        {
          text: "Sichtbar / Ohne Teilnehmer",
          value: constants.APPOINTMENTS.FILTER_ACTIVE_WITHOUT_PARTICIPANTS,
        },
      ],
    }
  },
  watch: {
    $route() {
      if (this.$route.name === 'appointments.index') {
        this.restoreConfig()
        this.loadData()
      }
    },
    filter() {
      this.storeConfig()
    },
    search: debounce(function () {
      this.storeConfig()
    }, 1000),
    selectedTags() {
      this.storeConfig()
    },
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    search: {
      get() {
        return this.$store.state.appointments.search
      },
      set(data) {
        this.$store.commit('appointments/setSearch', data)
      },
    },
    selectedTags: {
      get() {
        return this.$store.state.appointments.tags
      },
      set(data) {
        this.$store.commit('appointments/setTags', data)
      },
    },
    filter: {
      get() {
        return this.$store.state.appointments.filter
      },
      set(data) {
        this.$store.commit('appointments/setFilter', data)
      },
    },
  },
  methods: {
    getCurrentTableConfig() {
      const config = {
        filter: this.filter,
      }
      if (this.search) {
        config.search = this.search
      }
      if (this.selectedTags.length) {
        config.selectedTags = this.selectedTags
      }
      return config
    },
    getBaseRoute() {
      return {
        name: 'appointments.index',
      }
    },
    getTagItems(items) {
      return [
        {
          label: "Termine ohne TAG",
          id: -1,
        },
      ].concat(items)
    },
    loadData() {
      this.$store.dispatch('appointments/loadAppointments')
    },
  },
  components:{
    AddAppointmentModal,
    AppointmentSidebar,
    ModuleIntro,
    TagSelect,
    AppointmentTable,
  }
}
</script>
