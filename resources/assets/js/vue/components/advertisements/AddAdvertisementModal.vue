<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
    <form @submit.prevent="createAdvertisement">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Neuen Banner erstellen
        </v-card-title>
        <v-card-text>
          Vergeben Sie zuerst einen internen Namen für den Banner. Dieser wird den Nutzern nicht angezeigt.
          <v-text-field
            v-model="name"
            autofocus
            label="Interner Name"
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
            Banner erstellen
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
    createAdvertisement() {
      if(this.isLoading) {
        return
      }
      this.isLoading = true

      axios.post('/backend/api/v1/advertisements', {
        name: this.name
      }).then(response => {
        this.$router.push({
          name: 'advertisements.edit.general',
          params: {
            advertisementId: response.data.advertisement.id,
          },
        })
        this.$store.dispatch('advertisements/loadAdvertisements')
        this.closeModal()
      }).catch(e => {
        console.log(e)
        alert('Der Banner konnte leider nicht erstellt werden')
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
