<template>
  <v-list-tile
    tag="a"
    :href="recording.download_link"
    target="_blank"
  >
    <v-list-tile-content>
      <v-list-tile-title>
        {{ recording.creation_date | dateTime }}
        | {{ formatDuration(recording.duration) }}
      </v-list-tile-title>
      <v-list-tile-sub-title>{{ recording.title }}</v-list-tile-sub-title>
    </v-list-tile-content>
    <v-list-tile-action>
      <v-btn
        icon
        ripple
        :disabled="isDeleting"
        @click.prevent="deleteEntry"
      >
        <v-icon v-if="!isDeleting" color="grey">delete</v-icon>
        <v-progress-circular
          indeterminate
          v-if="isDeleting"
          color="grey"
        />
      </v-btn>
    </v-list-tile-action>
  </v-list-tile>
</template>

<script>
export default {
  props: [
    'recording',
    'webinarId',
  ],
  data() {
    return {
      isDeleting: false,
    }
  },
  methods: {
    deleteEntry() {
      if (this.isDeleting) {
        return
      }
      if (!confirm('Aufnahme unwiderruflich löschen?')) {
        return
      }
      this.isDeleting = true
      axios.post(`/backend/api/v1/webinars/${this.webinarId}/recordings/delete`, {
        recording_id: this.recording.id,
      }).then(() => {
        this.$emit('update')
      })
    },
    formatDuration(duration) {
      const minutes = Math.ceil(duration / 1000 / 60)
      let label = 'Minuten'
      if(minutes === 1) {
        label = 'Minute'
      }
      return minutes + ' ' + label
    },
  },
}
</script>
