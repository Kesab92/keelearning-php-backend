<template>
  <v-dialog
    v-model="dialog"
    width="500"
  >
    <v-card>
      <v-card-title
        class="headline grey lighten-2"
        primary-title
      >
        Termin absagen
      </v-card-title>

      <v-card-text>
        <p>
          Den Termin '{{ appointment.name }}' absagen?
        </p>
        <p>
          Alle Teilnehmer werden automatisch über die Absage benachrichtigt.
        </p>
        <p>Teilnehmer: <span class="orange--text">{{ appointment.participant_count }}</span></p>
      </v-card-text>

      <v-divider/>

      <v-card-actions>
        <v-btn
          :loading="isSaving"
          :disabled="isSaving"
          color="red"
          class="white--text"
          @click="cancel"
        >
          Termin absagen
        </v-btn>
        <v-spacer/>
        <v-btn
          color="secondary"
          flat
          @click="closeModal">
          Abbrechen
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: ['value', 'appointment'],
  data() {
    return {
      isSaving: false,
    }
  },
  computed: {
    dialog: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      },
    },
  },
  methods: {
    async cancel() {
      if (this.isSaving) {
        return
      }

      this.isSaving = true
      await this.$store.dispatch("appointments/cancelAppointment", {
        id: this.appointment.id,
      })
      this.isSaving = false

      this.closeModal()
    },
    closeModal() {
      this.dialog = false
    },
  }
}
</script>
