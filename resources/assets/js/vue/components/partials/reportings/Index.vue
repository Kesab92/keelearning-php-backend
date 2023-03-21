<template>
  <div>
    <AddReportingModal
      v-model="reportingModalOpen"
      :edit-route-name="editRouteName"
      :type="type"/>
    <v-layout row>
      <v-btn
        color="primary"
        @click="reportingModalOpen = true">
        <v-icon
          dark
          left>add
        </v-icon>
        Neues Reporting
      </v-btn>
    </v-layout>
    <v-card class="mt-2 mb-4">
      <v-data-table
        :headers="headers"
        :items="reportings"
        :loading="isLoading"
        :pagination="pagination"
        :rows-per-page-items="[1000]"
        :total-items="reportings.length"
        class="elevation-1"
        item-key="id">
        <tr
          slot="items"
          slot-scope="props"
          class="clickable"
          @click="editReporting(props.item.id)">
          <td>
            {{ props.item.emails.join(', ') }}
          </td>
          <td>
            <v-chip
              :key="`${props.item.id}-${tag.id}`"
              disabled
              small
              v-for="tag in props.item.tags">
              {{ tag.label }}
            </v-chip>
          </td>
          <td v-if="type === $constants.REPORTINGS.TYPE_QUIZ">
            {{ props.item.categories.map(c => c.name).join(', ') }}
          </td>
        </tr>
        <template slot="no-data">
          <v-alert
            v-if="!isLoading && reportings.length === 0"
            :value="true"
            type="info">
            Es wurden keine Reportings gefunden.
          </v-alert>
        </template>
      </v-data-table>
    </v-card>
    <ReportingSidebar
      :root-route-name="this.rootRouteName"
      :edit-route-name="this.editRouteName" />
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import AddReportingModal from "./AddReportingModal";
import ReportingSidebar from "./ReportingSidebar";

export default {
  props: {
    type: {
      type: Number,
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
  },
  data() {
    return {
      reportingModalOpen: false,
    }
  },
  created() {
    this.loadData()
  },
  computed: {
    ...mapGetters({
      reportings: "reportings/reportings",
      isLoading: "reportings/listIsLoading",
    }),
    headers() {
      let headers = [
        {
          text: "E-Mail Adressen",
          value: "emails",
          sortable: false,
        },
        {
          text: "Benutzer-TAGs",
          value: "tags",
          sortable: false,
        },
      ]

      if (this.type === this.$constants.REPORTINGS.TYPE_QUIZ) {
        headers.push({
          text: "Kategorien",
          value: "categories",
          sortable: false,
        })
      }

      return headers
    },
    pagination: {
      get() {
        return this.$store.state.reportings.pagination
      },
      set(data) {
        this.$store.commit("reportings/setPagination", data)
      },
    },
  },
  watch: {
    pagination: {
      handler() {
        this.loadData()
      },
      deep: true,
    },
  },
  methods: {
    loadData() {
      this.$store.dispatch("reportings/updateReportings", this.type)
    },
    editReporting(reportingId) {
      this.$router.push({
        name: this.editRouteName,
        params: {
          reportingId,
        },
      })
    },
  },
  components: {
    ReportingSidebar,
    AddReportingModal,
  },
}
</script>
