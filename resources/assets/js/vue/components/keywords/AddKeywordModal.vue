<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
    <form @submit.prevent="createKeyword">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Neues Schlagwort erstellen
        </v-card-title>
        <v-card-text>
          Der Name des Schlagwortes wird in der App hervorgehoben.
          <v-text-field
            v-model="name"
            autofocus
            label="Name"
            hide-details
            required
            class="mb-3"
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
            Schlagwort erstellen
          </v-btn>
        </v-card-actions>
      </v-card>
    </form>
  </v-dialog>
</template>
<script>

export default {
  props: ['value'],
  data() {
    return {
      isLoading: false,
      name: null,
    }
  },
  methods: {
    createKeyword() {
      if(this.isLoading) {
        return
      }
      this.isLoading = true

      axios.post('/backend/api/v1/keywords', {
        name: this.name,
      }).then(response => {
        this.$router.push({
          name: 'keywords.edit.general',
          params: {
            keywordId: response.data.keyword.id,
          },
        })
        this.$store.dispatch('keywords/loadKeywords')
        this.closeModal()
      }).catch(e => {
        console.log(e)
        alert('Das Schlagwort konnte leider nicht erstellt werden')
      }).finally(() => {
        this.isLoading = false
      })
    },
    closeModal() {
      this.name = null
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
