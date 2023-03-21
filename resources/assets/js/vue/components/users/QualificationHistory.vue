<template>
  <div class="px-4">
    <v-layout row
    class="my-3">
      <v-flex shrink>
        <v-select
          :items="filters"
          class="mr-4"
          clearable
          label="Filter"
          v-model="filter"/>
      </v-flex>
      <v-spacer />
      <v-flex shrink>
        <v-btn
          :href="exportLink"
          target="_blank"
          :disabled="!exportLink"
          color="primary"
          slot="activator">
          <v-icon
            dark
            left>cloud_download
          </v-icon>
          Ergebnisse exportieren
        </v-btn>
      </v-flex>
    </v-layout>
    <v-data-table
      :headers="headers"
      :items="qualificationHistories"
      :rows-per-page-items="[50, 100, 200]"
      :total-items="qualificationHistories.length"
      class="elevation-1">
      <tr
        @click="openItem(props.item)"
        :class="{
          'clickable': canOpen(props.item),
        }"
        slot="items"
        slot-scope="props">
        <td>
          {{ props.item.title }}
          <div
            class="grey--text"
            v-if="props.item.is_mandatory">
            Pflichtinhalt
          </div>
        </td>
        <td>
          {{ getType(props.item) }}
        </td>
        <td>
          {{ getStatus(props.item) }}
        </td>
        <td>
          {{ props.item.date | dateTime }}
        </td>
        <td v-if="canSeeCertificates(props.item)">
          <v-btn
            v-for="certificateLink in props.item.certificateLinks"
            :key="certificateLink"
            tag="a"
            :href="certificateLink"
            target="_blank"
            @click.stop>Zertifikat</v-btn>
        </td>
      </tr>
      <template slot="no-data">
        <v-alert
          v-show="!user.qualificationHistories || user.qualificationHistories.length === 0"
          :value="true"
          type="info">
          Dieser Benutzer hat noch keinen Test oder Kurs begonnen.
        </v-alert>
      </template>

    </v-data-table>
  </div>
</template>

<script>
import {mapGetters} from 'vuex'

export default {
  props: ["user"],
  data() {
    return {
      headers: [
        {
          text: "Titel",
          value: "title",
          sortable: false,
        },
        {
          text: "Typ",
          value: "type",
          sortable: false,
        },
        {
          text: "Status",
          value: "status",
          sortable: false,
        },
        {
          text: "Datum",
          value: "date",
          sortable: false,
        },
        {
          text: "Zertifikat",
          value: "certificate",
          sortable: false,
        },
      ],
      filters: [
        {
          text: "Nur bestandene Inhalte",
          value: "only_passed",
        },
        {
          text: "Nur Pflichtinhalte",
          value: "only_mandatory",
        },
      ],
      filter: null,
    }
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
      showPersonalData: 'app/showPersonalData',
    }),
    qualificationHistories() {
      if(!this.user.qualificationHistories) {
        return []
      }
      const qualificationHistories = this.user.qualificationHistories

      switch(this.filter) {
        case 'only_passed':
          return qualificationHistories.filter(qualificationHistory => qualificationHistory.status)
        case 'only_mandatory':
          return qualificationHistories.filter(qualificationHistory => qualificationHistory.is_mandatory)
      }

      return qualificationHistories
    },
    exportLink() {
      if(!this.user) {
        return null
      }
      return `/users/${this.user.id}/qualification-history`
    },
  },
  methods: {
    canOpen(item) {
      return this.myRights[`${item.type}s-edit`] || this.myRights[`${item.type}s-view`]
    },
    openItem(item) {
      if (!this.canOpen(item)) {
        return
      }
      switch (item.type) {
        case 'course':
          window.location.href = `/course-statistics/${item.id}#/course-statistics`
          break
        case 'test':
          window.location.href = `/tests/${item.id}/results`
          break
      }
    },
    canSeeCertificates(item) {
      switch (item.type) {
        case 'course':
          return this.showPersonalData('courses')
        case 'test':
          return this.showPersonalData('tests')
        default:
          return false
      }
    },
    getType(item) {
      switch (item.type) {
        case 'course':
          return 'Kurs'
        case 'test':
          return 'Test'
        default:
          return null
      }
    },
    getStatus(item) {
      switch (item.status) {
        case 1:
          return 'bestanden'
        case 0:
          return 'durchgefallen'
        default:
          if(!item.date) {
            return 'nicht begonnen'
          }
          return 'begonnen'
      }
    },
  },
}
</script>
