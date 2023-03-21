<template>
  <div>
    <v-snackbar
      :top="true"
      color="error"
      v-model="errorResponse"
    >
      {{ message }}
    </v-snackbar>
    <div v-if="recordings !== null">
      <v-list
        v-if="recordings.length > 0"
        two-line
      >
        <WebinarRecordingEntry
          v-for="recording in recordings"
          :recording="recording"
          :key="recording.id"
          :webinar-id="webinarId"
          @update="loadData"
        />
      </v-list>
      <v-alert
        v-else
        type="info"
        :value="true"
      >
        Zu diesem Webinar gibt es noch keine Aufnahmen.
      </v-alert>
    </div>
  </div>
</template>

<script>
  import WebinarRecordingEntry from './WebinarRecordingEntry'

  export default {
    props: ['webinarId'],
    data: () => ({
      errorResponse: false,
      isLoading: false,
      message: null,
      recordings: null,
    }),
    created() {
      this.loadData()
    },
    methods: {
      loadData() {
        if (this.isLoading) {
          return
        }
        this.isLoading = true
        axios.get(`/backend/api/v1/webinars/${this.webinarId}/recordings`).then(response => {
          if (response.data.success) {
            this.recordings = response.data.recordings
          } else {
            this.message = response.data.error
            this.errorResponse = true
          }
          this.isLoading = false
        }).catch(() => {
          this.isLoading = false
          this.message = 'Fehler beim Laden der Webinar-Daten'
          this.errorResponse = true
        })
      },
    },
    components: {
      WebinarRecordingEntry,
    },
  }
</script>
