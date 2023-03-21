<template>
  <v-data-table
    :headers="headers"
    :items="events"
    :loading="isLoading"
    :pagination.sync="pagination"
    :rows-per-page-items="[20, 50, 100]"
    :total-items="eventCount"
  >
    <template v-slot:items="props">
      <td class="text-xs-left">
        {{ parseDate(props.item.date) }}
      </td>
      <td class="text-xs-left">
        {{ props.item.title }}
      </td>
      <td
        v-if="showNames"
        class="text-xs-left">
        {{ props.item.user }}
      </td>
      <td class="text-xs-center">
        <template v-if="parseDuration(props.item.duration)">
          {{ parseDuration(props.item.duration) }}
        </template>
        <span
          v-else
          class="grey--text"
        >
          n/a
        </span>
      </td>
      <td class="text-xs-center cyan--text">
        <template v-if="parsePercentage(props.item.score)">
          {{ parsePercentage(props.item.score) }}
        </template>
        <span
          v-else
          class="grey--text"
        >
          n/a
        </span>
      </td>
      <td
        :class="parseStatusClass(props.item.status)"
        class="text-xs-right"
      >
        {{ parseStatus(props.item.status) }}
      </td>
    </template>
  </v-data-table>
</template>

<script>
import {mapGetters} from 'vuex'
import moment from 'moment'

let cancelTokenSource

const verbs = {
  'http://adlnet.gov/expapi/verbs/passed': {
    title: 'Bestanden',
    class: 'green--text',
  },
  'http://adlnet.gov/expapi/verbs/failed': {
    title: 'Nicht Bestanden',
    class: 'red--text',
  },
  'http://adlnet.gov/expapi/verbs/completed': {
    title: 'Abgeschlossen',
    class: 'cyan--text',
  },
  'started': { // generic dummy verb to catch our self-defined starting event
    title: 'Angefangen',
    class: 'cyan--text',
  },
}

const headers = [
  {
    text: 'Datum',
    value: 'date',
    sortable: true,
    align: 'left',
  },
  {
    text: 'Titel',
    value: 'title',
    sortable: false,
    align: 'left',
  },
  {
    text: 'Benutzer',
    value: 'user',
    sortable: false,
    align: 'left',
  },
  {
    text: 'Dauer',
    value: 'duration',
    sortable: false,
    align: 'center',
  },
  {
    text: 'Ergebnis',
    value: 'score',
    sortable: true,
    align: 'center',
  },
  {
    text: 'Status',
    value: 'status',
    sortable: true,
    align: 'right',
  },
]

export default {
  props: {
    learningmaterials: {
      type: Array,
      default: null,
    },
    search: {
      type: String,
      default: null,
    },
    courseId: {
      type: Number,
      default: null,
    }
  },
  data() {
    return {
      events: [],
      eventCount: null,
      isLoading: true,
      pagination: {
        descending: true,
        sortBy: 'date',
      },
    }
  },
  created() {
    this.loadEvents()
  },
  watch: {
    pagination: {
      handler() {
        this.loadEvents()
      },
      deep: true,
    },
    search() {
      this.loadEvents()
    },
    learningmaterials() {
      this.loadEvents()
    },
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
      showPersonalData: 'app/showPersonalData',
    }),
    headers() {
      if (!this.showNames) {
        return headers.filter((header) => header.value != 'user')
      }
      return headers
    },
    showNames() {
      if (this.courseId) {
        return this.showPersonalData('courses')
      }
      return this.showPersonalData('learningmaterials')
    },
  },
  methods: {
    loadEvents() {
      if (cancelTokenSource) {
        cancelTokenSource.cancel()
      }
      const params = {
        descending: this.pagination.descending ? 1 : 0,
        page: this.pagination.page,
        rows: this.pagination.rowsPerPage,
        sortBy: this.pagination.sortBy,
        search: this.search,
        courseId: this.courseId,
        learningmaterials: this.learningmaterials,
      }
      this.isLoading = true
      cancelTokenSource = axios.CancelToken.source()
      axios.get('/backend/api/v1/wbt/events', {
        params,
        cancelToken: cancelTokenSource.token,
      }).then(response => {
        if(response instanceof axios.Cancel) {
          return
        }

        this.eventCount = response.data.eventcount
        this.events = response.data.events
        this.isLoading = false
      }).catch(function (thrown) {
        if (!axios.isCancel(thrown)) {
          // TODO: handle error
        }
      })
    },
    parseDate(iso8601) {
      return moment(iso8601).format('DD.MM.YYYY HH:mm')
    },
    parseDuration(iso8601) {
      if (!iso8601) {
        return null
      }
      let duration = moment.duration(iso8601)
      if(duration.asSeconds() < 1) {
        // 0 second durations are basically non existent, because they mean that the WBT sent some weird data
        // or data that's not relevant for us.
        // This fix was implemented, because a (rise 360) wbt sent us the time that
        // the WBT took to show the first slide, which was ~500-800ms.
        return null
      }
      const seconds = ('0' + duration.seconds()).slice(-2)
      const minutes = ('0' + duration.minutes()).slice(-2)
      const hours = ('0' + duration.hours()).slice(-2)
      return `${hours}:${minutes}:${seconds}`
    },
    parsePercentage(percentage) {
      if (percentage === null) {
        return null
      }
      return Math.round(percentage * 10000) / 100 + '%'
    },
    parseStatus(status) {
      if (verbs[status]) {
        return verbs[status].title
      }
      return status
    },
    parseStatusClass(status) {
      if (verbs[status]) {
        return verbs[status].class
      }
      return null
    },
  }
}
</script>
