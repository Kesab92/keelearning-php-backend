<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
    <form @submit.prevent="createTag">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Neuen TAG erstellen
        </v-card-title>
        <v-card-text>
          <v-text-field
            v-model="label"
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
            TAG erstellen
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
      label: null,
    }
  },
  methods: {
    createTag() {
      if(this.isLoading) {
        return
      }
      this.isLoading = true

      axios.post('/backend/api/v1/tags', {
        label: this.label,
      }).then(response => {
        this.$router.push({
          name: 'tags.edit.general',
          params: {
            tagId: response.data.tag.id,
          },
        })
        this.$store.dispatch('tags/loadTags')
        this.closeModal()
      }).catch(e => {
        console.log(e)
        alert('Der TAG konnte leider nicht erstellt werden')
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
