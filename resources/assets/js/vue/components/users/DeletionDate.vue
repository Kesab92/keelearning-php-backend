<template>
  <div class="s-deletionDate">
    <v-menu
      ref="menu"
      v-model="menu"
      transition="scale-transition"
      offset-y
      nudge-right="34px"
      nudge-top="20px"
      full-width
      min-width="290px"
    >
      <v-text-field
        slot="activator"
        v-model="expiresAtFormatted"
        label="Automatisches Löschdatum"
        placeholder="Kein Löschdatum"
        prepend-icon="event"
        width="300px"
        readonly
        outline
        @click:clear="userData.expires_at = null"
        clearable
      />
      <v-date-picker
        v-model="userData.expires_at"
        no-title
        reactive
        first-day-of-week="1"
        locale="de-DE"
        scrollable />
    </v-menu>
  </div>
</template>

<script>
import moment from 'moment'

export default {
  props: ['value'],
  data() {
    return {
      menu: false,
      modal: false,
    }
  },
  computed: {
    userData: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      },
    },
    expiresAtFormatted() {
      if(!this.userData.expires_at) {
        return this.userData.expires_at
      }
      return moment(this.userData.expires_at).format('DD.MM.YYYY')
    }
  },
}
</script>

<style lang="scss" scoped>
#app {
  .s-deletionDate {
    width: 325px;
  }
}
</style>
