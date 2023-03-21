<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
    <form @submit.prevent="archive">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Archivieren
        </v-card-title>
        <v-card-text>
          <p>Möchten Sie den bestehenden Kurs archivieren?</p>
          <p>Der Kurs wird somit automatisch für alle aktiven Teilnehmer beendet. Die Statistiken bleiben bestehen.</p>
          <p>Derzeitige Teilnehmer: {{ course.participationUserCount }} </p>
        </v-card-text>
        <v-card-actions>
          <v-btn
            @click="closeModal"
            flat>
            Abbrechen
          </v-btn>
          <v-spacer />
          <v-btn
            :loading="isSaving"
            :disabled="isSaving"
            color="primary"
            type="submit"
            flat>
            Kurs archivieren
          </v-btn>
        </v-card-actions>
      </v-card>
    </form>
  </v-dialog>
</template>


<script>

export default {
  props: ['value', 'course'],
  data() {
    return {
      isSaving: false,
    }
  },
  methods: {
    async archive() {
      if (this.isSaving) {
        return
      }

      this.isSaving = true
      await this.$store.dispatch("courses/archiveCourse", {
        id: this.course.id,
      })
      this.isSaving = false
      this.dialog = false
    },
    closeModal() {
      this.dialog = false
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
}
</script>
