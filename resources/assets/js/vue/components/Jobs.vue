<template>
  <div>
    <h1 class="headline">Hintergrundprozesse</h1>
    <v-data-table
      :headers="headers"
      :items="jobs"
      :pagination.sync="pagination"
      :rows-per-page-items="[ 20, 100, 500, { 'text': '$vuetify.dataIterator.rowsPerPageAll', 'value': -1 } ]"
      class="elevation-1"
    >
      <template
        slot="items"
        slot-scope="props">
        <td>{{ humanName(props.item.name) }}</td>
        <td>{{ humanDate(props.item.payload.pushedAt) }}</td>
        <td>{{ humanDate(props.item.reserved_at) }}</td>
        <td>{{ humanDate(props.item.completed_at) }}</td>
        <td class="text-xs-right">
          <v-icon v-if="props.item.status === 'pending'">pause</v-icon>
          <v-progress-circular
            indeterminate
            v-if="props.item.status === 'reserved'"
            color="primary"
          />
          <v-icon
            v-if="props.item.status === 'completed'"
            color="green">check</v-icon>
        </td>
      </template>
    </v-data-table>
  </div>
</template>

<script>
import { format, parse } from 'date-fns'

  export default {
    data() {
      return {
        headers: [
          {
            text: 'Name',
            align: 'left',
            sortable: false,
            value: 'name'
          },
          {
            text: 'Erstellt',
            align: 'left',
            value: 'payload.pushedAt'
          },
          {
            text: 'Gestartet',
            align: 'left',
            value: 'reserved_at'
          },
          {
            text: 'Abgeschlossen',
            align: 'left',
            value: 'completed_at'
          },
          {
            text: 'Status',
            align: 'right',
            sortable: false,
            value: 'status'
          },
        ],
        pagination: {
          descending: true,
          rowsPerPage: 100,
          sortBy: 'payload.pushedAt'
        },
        jobs: []
      }
    },
    created() {
      this.fetchData()
    },
    methods: {
      fetchData() {
        axios.get("/backend/api/v1/jobs").then(response => {
          this.jobs = response.data.jobs
          window.setTimeout(this.fetchData, 3000)
        })
      },
      humanName(name) {
        let labels = {
          'App\\Jobs\\CompetitionStartNotification': 'Gewinnspiel Start',
          'App\\Jobs\\LearningMaterialsPublished': 'Mediathek Veröffentlichung',
          'App\\Jobs\\NewsPublished': 'News Veröffentlichung',
          'App\\Jobs\\SendDeepstreamEvent': 'Live Update',
          'App\\Jobs\\SendMobileNotification': 'Push Benachrichtigung',
        }
        if(typeof labels[name] === 'undefined') {
          return 'Hintergrundprozess'
        }
        return labels[name]
      },
      humanDate(date) {
        return format(parse(date, 'yyyy-MM-dd HH:mm:ss', new Date()), 'dd.MM.yyyy')
      }
    }
  }
</script>

<style lang="scss" scoped>
  #app {
    .headline {
      padding: 10px 0;
    }
  }
</style>
