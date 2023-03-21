<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
    <form @submit.prevent="markCommentAsHarmless">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Kommentar als harmlos markieren
        </v-card-title>
        <v-card-text>
          Wenn dieser Kommentar nicht gegen die Richtlinien verstößt, markieren Sie ihn als harmlos.
          <v-textarea
            v-model="statusExplanation"
            autofocus
            label="Begründung"
            hide-details
            required
            class="mb-3 mt-2"
            box />
        </v-card-text>
        <v-card-actions>
          <v-btn
            :loading="isLoading"
            :disabled="isLoading"
            type="submit"
            color="primary"
            class="white--text">
            Als harmlos markieren
          </v-btn>
          <v-spacer />
          <v-btn
            color="secondary"
            @click="closeModal"
            flat>
            Abbrechen
          </v-btn>
        </v-card-actions>
      </v-card>
    </form>
  </v-dialog>
</template>
<script>

export default {
  props: ['value', 'comment'],
  data() {
    return {
      isLoading: false,
      statusExplanation: null,
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
    markCommentAsHarmless() {
      if(this.isLoading) {
        return
      }

      this.isLoading = true

      let data = {
        status_explanation: this.statusExplanation,
      }

      axios.post(`/backend/api/v1/comments/${this.comment.id}/mark-as-harmless`, data).then(() => {
        // TODO: success notification
        this.$emit('marked')
        this.closeModal()
      }).catch(e => {
        alert('Der Kommentar konnte leider nicht als harmlos markiert werden.')
      }).finally(() => {
        this.isLoading = false
      })
    },
    closeModal() {
      this.dialog = false
    },
  },
}
</script>
