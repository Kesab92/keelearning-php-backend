<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
    <form @submit.prevent="createPage">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Neue Seite erstellen
        </v-card-title>
        <v-card-text>
          Vergeben Sie zuerst einen Titel für die Seite.
          <v-text-field
            v-model="title"
            autofocus
            label="Titel"
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
            Seite erstellen
          </v-btn>
        </v-card-actions>
      </v-card>
    </form>
    <v-snackbar
      :color="snackbar.type"
      :top="true"
      v-model="snackbar.active"
    >
      {{ snackbar.message }}
    </v-snackbar>
  </v-dialog>
</template>
<script>
import TagSelect from "../partials/global/TagSelect"
import {mapGetters} from "vuex";

export default {
  props: ['value'],
  data() {
    return {
      isLoading: false,
      title: null,
      snackbar: {
        active: false,
        type: null,
        message: null,
      },
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
    createPage() {
      if(this.isLoading) {
        return
      }
      this.isLoading = true

      axios.post('/backend/api/v1/pages', {
        title: this.title,
      }).then(response => {
        this.$router.push({
          name: 'pages.edit.general',
          params: {
            pageId: response.data.page.id,
          },
        })
        this.$store.dispatch('pages/loadPages')
        this.closeModal()
      }).catch(e => {
        console.log(e)
        alert('Die Seite konnte leider nicht erstellt werden')
      }).finally(() => {
        this.isLoading = false
      })
    },
    closeModal() {
      this.dialog = false
    },
    handleSnackbar(type, message) {
      this.snackbar.active = true
      this.snackbar.type = type
      this.snackbar.message = message
    },
  },
  components: {
    TagSelect,
  },
}
</script>
