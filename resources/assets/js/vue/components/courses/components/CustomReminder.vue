<template>
  <v-tooltip
    bottom>
    <v-btn
      slot="activator"
      color="info"
      :disabled="isReadonly"
      @click="$emit('delete', reminder)">
      {{ reminder.days_offset }}
    </v-btn>
    <span>
      Entfernen
    </span>
  </v-tooltip>
</template>

<script>
import {mapGetters} from "vuex"

export default {
  props: ['reminder', 'courseId'],
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    isReadonly() {
      return !this.myRights['courses-edit']
    },
  },
}
</script>
