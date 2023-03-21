<template>
  <div
    class="s-date"
  >
    <v-menu
      ref="menu"
      v-model="menu"
      :disabled="disabled"
      transition="scale-transition"
      offset-y
      nudge-right="34px"
      nudge-top="20px"
      full-width
      min-width="290px"
    >
      <v-text-field
        slot="activator"
        v-model="dateFormatted"
        :label="label"
        :placeholder="placeholder"
        :clearable="clearable"
        :disabled="disabled"
        append-icon="event"
        width="300px"
        readonly
        outline
        @click:clear="date = null"
      />
      <v-date-picker
        v-model="date"
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
  props: {
    value: {
      type: String|null,
      required: true,
    },
    label: {
      type: String,
      required: false,
      default: '',
    },
    placeholder: {
      type: String,
      required: false,
      default: '',
    },
    clearable: {
      type: Boolean,
      required: false,
      default: true,
    },
    disabled: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data() {
    return {
      menu: false,
      modal: false,
    }
  },
  computed: {
    date: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      },
    },
    dateFormatted() {
      if(!this.date) {
        return this.date
      }
      return moment(this.date).format('DD.MM.YYYY')
    }
  },
}
</script>

<style lang="scss" scoped>
#app {
  .s-date {
    width: 325px;
  }
}
</style>
