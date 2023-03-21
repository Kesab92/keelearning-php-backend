<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
    <form @submit.prevent="createContentCategory">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Neue Kategorie erstellen
        </v-card-title>
        <v-card-text>

          <v-text-field
            v-model="name"
            autofocus
            label="Name"
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
            Kategorie erstellen
          </v-btn>
        </v-card-actions>
      </v-card>
    </form>
  </v-dialog>
</template>


<script>
export default {
  props: {
    type: {
      type: String,
      required: true,
    },
    value: {
      type: Boolean,
      required: true,
    },
    editRouteName: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      isLoading: false,
      name: null,
    }
  },
  methods: {
    createContentCategory() {
      if(this.isLoading) {
        return
      }
      this.isLoading = true

      axios.post('/backend/api/v1/content-categories', {
        name: this.name,
        type: this.type,
      }).then(response => {
        this.$router.push({
          name: this.editRouteName,
          params: {
            categoryId: response.data.category.id,
          },
        })
        this.$store.dispatch('contentCategories/updateCategories', this.type)
        this.closeModal()
      }).catch(e => {
        console.log(e)
        alert('Die Kategorie konnte leider nicht erstellt werden')
      }).finally(() => {
        this.isLoading = false
      })
    },
    closeModal() {
      this.dialog = false
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
  }
}
</script>
