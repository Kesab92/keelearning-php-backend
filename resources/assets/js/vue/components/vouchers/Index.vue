<template>
  <div>
    <ModuleIntro>
      <template v-slot:title>
        Vouchers
      </template>
      <template v-slot:description>
        Weist Benutzern zum Zeitpunkt der Code-Eingabe beliebige Benutzergruppen/TAGs zu.<br>
        Wird häufig zur Zugriffsbeschränkung auf der Registrierungsseite eingesetzt.
      </template>
      <template v-slot:links>
        <v-btn
          flat
          color="primary"
          small
          href="https://helpdesk.keelearning.de/de/articles/4233324-uber-voucher"
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
    <v-layout row>
      <AddVoucherModal
        :tag-groups="tagGroups"
        :tags-without-group="tagsWithoutGroup"
        :tags-required="tagsRequired"
        @update="loadData"
      />
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
          <v-spacer></v-spacer>
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
      <VoucherTable />
      <VoucherSidebar />
    </v-card>
  </div>
</template>

<script>
import ModuleIntro from "../partials/global/ModuleIntro"
import VoucherTable from "./VoucherTable"
import VoucherSidebar from "./VoucherSidebar"
import AddVoucherModal from "./AddVoucherModal"
import {mapGetters} from "vuex";
import {debounce} from "lodash";
import constants from "../../logic/constants";

export default {
  data() {
    return {
      message: null,
      filters: [
        {
          text: "Aktive Voucher",
          value: constants.VOUCHERS.FILTER_ACTIVE,
        },
        {
          text: "Archivierte Voucher",
          value: constants.VOUCHERS.FILTER_ARCHIVED,
        },
        {
          text: "Alle Voucher",
          value: constants.VOUCHERS.FILTER_ALL,
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
  },
  computed: {
    ...mapGetters({
      tagGroups: 'vouchers/tagGroups',
      tagsWithoutGroup: 'vouchers/tagsWithoutGroup',
      tagsRequired: 'vouchers/tagsRequired',
      isLoading: 'vouchers/listIsLoading',
    }),
    filter: {
      get() {
        return this.$store.state.vouchers.filter
      },
      set(data) {
        this.$store.commit('vouchers/setFilter', data)
      },
    },
    search: {
      get() {
        return this.$store.state.vouchers.search
      },
      set(data) {
        this.$store.commit('vouchers/setSearch', data)
      },
    },
  },
  methods: {
    loadData() {
      this.$store.dispatch('vouchers/loadVouchers')
    },
  },
  components:{
    VoucherTable,
    ModuleIntro,
    VoucherSidebar,
    AddVoucherModal,
  }
}
</script>
