<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
    <form @submit.prevent="save">
    <v-card>
      <v-card-title
        class="headline grey lighten-2"
        primary-title>
        Speichern & benachrichtigen
      </v-card-title>
      <v-card-text>
        <v-progress-circular
          v-if="loading"
          indeterminate
          color="primary"
        />
        <template v-else>
          <p>Der Kurs wird sichtbar geschaltet.</p>
          <v-divider class="my-4"/>
          <p>Benachrichtigungen</p>
          <toggle
            v-model="courseData.send_new_course_notification"
            label="E-Mail zum Kursbeginn an mögliche Teilnehmer versenden" />
          <template v-if="courseData.send_new_course_notification">
            <v-divider class="my-4"/>
            Es werden:
            <ul class="mb-4" >
              <li>{{ count }} Teilnehmer benachrichtigt</li>
            </ul>
          </template>
          <v-divider />
        </template>
      </v-card-text>
      <v-card-actions>
        <v-btn
          @click="closeModal"
          flat>
          Abbrechen
        </v-btn>
        <v-spacer />
        <v-btn
          color="primary"
          :disabled="loading"
          type="submit">
          Speichern
        </v-btn>
      </v-card-actions>
    </v-card>
    </form>
  </v-dialog>
</template>

<script>
import Toggle from "../../partials/global/Toggle"
export default {
  props: ['value', 'course'],
  data() {
    return {
      courseData: null,
      count: null,
      loading: false,
    }
  },
  watch: {
    course: {
      handler() {
        if(!this.course) {
          return
        }
        this.courseData = JSON.parse(JSON.stringify(this.course))
      },
      immediate: true,
      deep: true,
    },
    dialog: {
      handler() {
        if (this.dialog) {
          this.loadUserCount()
        }
      },
    },
  },
  computed: {
    dialog: {
      set(value) {
        this.$emit('input', value)
      },
      get() {
        return this.value
      }
    }
  },
  methods: {
    save() {
      this.dialog = false
      if(this.courseData.send_new_course_notification !== this.course.send_new_course_notification) {
        this.$emit('updateNewCourseNotification', this.courseData.send_new_course_notification)
      }
      this.$emit('confirm')
    },
    closeModal() {
      this.dialog = false
    },
    loadUserCount() {
      this.loading = true
      axios.get(`/backend/api/v1/courses/${this.courseData.id}/users-to-notify`, {
        params: {
          tags: this.courseData.tags,
          hasIndividualAttendees: this.courseData.has_individual_attendees,
          individualAttendees: this.courseData.individualAttendees,
        }
      }).then(response => {
        this.count = response.data.count
        this.loading = false
      })
    },
  },
  components:{
    Toggle,
  }
}
</script>
