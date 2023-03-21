<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
    <form @submit.prevent="store">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Neuer Reminder
        </v-card-title>
        <v-card-text>
          <v-text-field
            v-model="days"
            autofocus
            label="Tage vor Kursablauf"
            type="number"
            min="1"
            step="1"
            hide-details
            required
            class="mb-3 mt-2"
            box />
        </v-card-text>
        <v-card-actions>
          <v-btn
            @click="closeModal"
            flat>
            Abbrechen
          </v-btn>
          <v-spacer />
          <v-btn
            :loading="isLoading"
            :disabled="isLoading"
            color="primary"
            type="submit"
            flat>
            Reminder erstellen
          </v-btn>
        </v-card-actions>
      </v-card>
    </form>
  </v-dialog>
</template>
<script>
export default {
  props: {
    value: {
      type: Boolean,
      required: true,
    },
    course: {
      type: Object,
      required: true,
    },
    type: {
      type: Number,
      required: true,
    },
    emails: {
      type: String,
      required: false,
      default: null,
    },
  },
  data() {
    return {
      isLoading: false,
      days: null,
    }
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
    async store() {
      this.$emit('store', this.days, this.type)
      this.dialog = false
      this.days = null
    },
    closeModal() {
      this.dialog = false
      this.days = null
    },
  },
}
</script>
