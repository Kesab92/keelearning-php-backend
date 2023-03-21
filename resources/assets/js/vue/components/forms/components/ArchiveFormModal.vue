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
          Formular archivieren
        </v-card-title>
        <v-card-text>
          <p>Möchten Sie dieses Formular archivieren?</p>
          <p>
            Das Formular kann daraufhin nicht mehr in neue Inhalte eingebettet werden. Die Statistiken bleiben erhalten.
          </p>
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
            Formular archivieren
          </v-btn>
        </v-card-actions>
      </v-card>
    </form>
  </v-dialog>
</template>


<script>

export default {
  props: ['value', 'form'],
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
      await this.$store.dispatch("forms/archiveForm", {
        id: this.form.id,
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
