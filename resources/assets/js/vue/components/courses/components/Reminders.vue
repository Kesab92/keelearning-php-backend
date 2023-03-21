<template>
  <div>
    <h4 class="mb-2">Eskalationsmanagement</h4>
    <v-alert
      v-if="(courseData.duration_type === this.$constants.COURSES.DURATION_TYPES.FIXED && !courseData.available_until) && !courseData.is_template"
      class="my-4"
      color="info"
      icon="info"
      :value="true">
      Wenn Sie dem Kurs ein End-Datum geben, können Sie hier die Benachrichtigungen einstellen, die an User und Admins versendet werden.
    </v-alert>
    <v-layout v-else>
      <v-flex xs6 class="mr-1">
        <v-layout row wrap>
          <p class="grey--text d-inline-block">
            Benutzer automatisch vor Fristende benachrichtigen (Tage).
            <v-btn
              href="/mails?edit=CourseReminder"
              target="_blank"
              flat
              icon
              color="black"
              class="mx-0">
              <v-icon>settings</v-icon>
            </v-btn>
          </p>
        </v-layout>
        <v-layout v-if="!isLoading" row wrap>
          <div
            v-for="days in userReminderDays"
            :key="`user-reminders-${days}`"
          >
            <DefaultReminder
              v-if="$constants.REMINDERS.DEFAULT_COURSE_REMINDER_DAYS.includes(days)"
              :days="days"
              :type="$constants.REMINDERS.TYPE_USER_COURSE_NOTIFICATION"
              @switchReminder="switchReminder"/>
            <CustomReminder
              v-else
              :reminder="customUserReminders.find(reminder => reminder.days_offset === days)"
              :course-id="courseData.id"
              @delete="deleteReminder"/>
          </div>
          <v-btn
            outline
            :disabled="isReadonly"
            @click="userReminderModalOpen = true"
          >
            <v-icon>add</v-icon>
          </v-btn>
          <AddReminderModal
            v-model="userReminderModalOpen"
            :course="courseData"
            :type="$constants.REMINDERS.TYPE_USER_COURSE_NOTIFICATION"
            @store="storeReminder"
          />
        </v-layout>
        <v-progress-circular
          v-else
          indeterminate
          color="primary"/>
      </v-flex>
      <v-flex
          v-show="courseData.duration_type === $constants.COURSES.DURATION_TYPES.FIXED"
          xs6 class="ml-1">
        <p class="grey--text">
          Verantwortliche Administratoren und externe Personen automatisch vor Fristende benachrichtigen (Tage).
        </p>
        <v-layout
          v-if="!isLoading"
          row
          wrap>
          <div
            v-for="days in adminReminderDays"
            :key="`admin-reminders-${days}`"
          >
            <DefaultReminder
              v-if="$constants.REMINDERS.DEFAULT_COURSE_REMINDER_DAYS.includes(days)"
              :days="days"
              :type="$constants.REMINDERS.TYPE_ADMIN_COURSE_NOTIFICATION"
              @switchReminder="switchReminder"/>
            <CustomReminder
              v-else
              :reminder="customAdminReminders.find(reminder => reminder.days_offset === days)"
              :course-id="courseData.id"
              @delete="deleteReminder"/>
          </div>
          <v-btn
            outline
            :disabled="isReadonly"
            @click="openAdminReminderModal"
          >
            <v-icon>add</v-icon>
          </v-btn>
          <AddReminderModal
            v-model="adminReminderModalOpen"
            :course="courseData"
            :type="$constants.REMINDERS.TYPE_ADMIN_COURSE_NOTIFICATION"
            :emails="emails"
            @store="storeReminder"
          />
          <v-flex xs12>
            <v-text-field
              v-model="emails"
              label="Weitere Empfänger (kommagetrennte E-Mail-Adressen)"
              hide-details
              class="mt-2"
              :disabled="isReadonly"
              outline />
          </v-flex>
        </v-layout>
        <v-progress-circular
          v-else
          indeterminate
          color="primary"/>
      </v-flex>
    </v-layout>
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import helpers from "../../../logic/helpers"
import AddReminderModal from "./AddReminderModal"
import DefaultReminder from "./DefaultReminder"
import CustomReminder from "./CustomReminder"

export default {
  props: ["course"],
  data() {
    return {
      isLoading: false,
      userReminderModalOpen: false,
      adminReminderModalOpen: false,
      courseData: null,
      emails: null,
    }
  },
  async created() {
    this.isLoading = true
    await this.$store.dispatch("courses/loadReminders", {courseId: this.course.id})
    this.emails = this.reminderEmails
    this.isLoading = false
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
      courseReminders: 'courses/courseReminders',
    }),
    isReadonly() {
      return !this.myRights['courses-edit']
    },
    customUserReminders() {
      return this.courseReminders.filter(reminder => {
        return reminder.type === this.$constants.REMINDERS.TYPE_USER_COURSE_NOTIFICATION && !this.$constants.REMINDERS.DEFAULT_COURSE_REMINDER_DAYS.includes(reminder.days_offset)
      })
    },
    userReminderDays() {
      let days = [...this.$constants.REMINDERS.DEFAULT_COURSE_REMINDER_DAYS]
      this.customUserReminders.forEach(reminder => {
        days.push(reminder.days_offset)
      })
      return days.sort((a, b) => b - a)
    },
    customAdminReminders() {
      return this.courseReminders.filter(reminder => {
        return reminder.type === this.$constants.REMINDERS.TYPE_ADMIN_COURSE_NOTIFICATION && !this.$constants.REMINDERS.DEFAULT_COURSE_REMINDER_DAYS.includes(reminder.days_offset)
      })
    },
    adminReminderDays() {
      let days = [...this.$constants.REMINDERS.DEFAULT_COURSE_REMINDER_DAYS]
      this.customAdminReminders.forEach(reminder => {
        days.push(reminder.days_offset)
      })
      return days.sort((a, b) => b - a)
    },
    reminderEmails() {
      let emails = ''
      const reminder = this.courseReminders.find(reminder => {
        return reminder.type === this.$constants.REMINDERS.TYPE_ADMIN_COURSE_NOTIFICATION
      })

      if(!reminder) {
        return ''
      }

      emails = reminder.metadata.filter(meta => meta.key === 'email').map(meta => meta.value).join(', ')

      return emails
    }
  },
  watch: {
    course: {
      handler() {
        this.courseData = JSON.parse(JSON.stringify(this.course))
      },
      deep: true,
      immediate: true,
    },
    emails: {
      handler(emails) {
        this.$store.commit('courses/setReminderEmails', emails)
      },
      immediate: true,
    },
  },
  methods: {
    switchReminder(days, type) {
      const existingReminder = this.courseReminders.find((reminder) => {
        return reminder.days_offset === days && reminder.type === type
      })
      if(existingReminder) {
        this.deleteReminder(existingReminder)
      } else {
        this.storeReminder(days, type)
      }
    },
    storeReminder(days, type) {
      let dataToSave = {
        courseId: this.courseData.id,
        days_offset: days,
        type: type,
      }
      if (type === this.$constants.REMINDERS.TYPE_ADMIN_COURSE_NOTIFICATION) {
        if(helpers.getFirstInvalidMail(this.emails.split(',').map(email => email.trim()))) {
          alert('Ungültige E-Mail')
          return
        }
        dataToSave.emails = this.emails
      }

      this.$store.dispatch("courses/storeReminder", dataToSave)
    },
    deleteReminder(reminder) {
      this.$store.dispatch("courses/deleteReminder", {
        courseId: this.courseData.id,
        reminderId: reminder.id,
      })
    },
    openAdminReminderModal() {
      this.adminReminderModalOpen = true
    },
  },
  components: {
    AddReminderModal,
    DefaultReminder,
    CustomReminder,
  },
}
</script>
