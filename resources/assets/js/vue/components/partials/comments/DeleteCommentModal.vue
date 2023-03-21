<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
    <form @submit.prevent="deleteComment">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Kommentar löschen
        </v-card-title>
        <v-card-text>
          Löschen Sie diesen Kommentar, wenn er gegen die Richtlinien verstößt.
          <v-textarea
            v-if="reasonIsRequired"
            v-model="statusExplanation"
            autofocus
            label="Begründung"
            hide-details
            :required="reasonIsRequired"
            class="mb-3 mt-2"
            box />
        </v-card-text>
        <v-card-actions>
          <v-btn
            :loading="isLoading"
            :disabled="isLoading"
            type="submit"
            color="red"
            class="white--text">
            Jetzt löschen
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
  props: ['value', 'comment', 'reasonIsRequired'],
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
    deleteComment() {
      if(this.isLoading) {
        return
      }

      this.isLoading = true
      let data = {}

      if(this.reasonIsRequired) {
        data.status_explanation = this.statusExplanation
      }

      axios.post('/backend/api/v1/comments/'+this.comment.id, data).then(() => {
        this.$emit('deleted')
        this.closeModal()
      }).catch(e => {
        alert('Der Kommentar konnte leider nicht gelöscht werden.')
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
