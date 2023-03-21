<template>
  <v-tooltip
    bottom>
    <v-btn
      slot="activator"
      :color="reminderExists ? 'info' : null"
      :disabled="isReadonly"
      @click="$emit('switchReminder', days, type)"
    >
      {{ days }}
    </v-btn>
    <span v-if="reminderExists">
      Deaktivieren
    </span>
    <span v-else>
      Aktivieren
    </span>
  </v-tooltip>
</template>

<script>
import {mapGetters} from "vuex"

export default {
  props: ['days', 'type'],
  computed: {
    ...mapGetters({
      courseReminders: 'courses/courseReminders',
      myRights: 'app/myRights',
    }),
    isReadonly() {
      return !this.myRights['courses-edit']
    },
    reminderExists() {
      return this.courseReminders.some((reminder) => {
        return reminder.days_offset === this.days && reminder.type === this.type
      })
    },
  },
}
</script>
