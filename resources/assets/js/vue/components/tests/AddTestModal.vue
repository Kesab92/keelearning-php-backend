<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
    <form @submit.prevent="createTest">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Neuen Test erstellen
        </v-card-title>
        <v-card-text>
          Vergeben Sie zuerst einen Name für den Test.
          <v-text-field
            v-model="name"
            autofocus
            label="Name"
            hide-details
            required
            class="mb-3 mt-2"
            box />
          <tag-select
            v-if="!this.isFullAdmin"
            v-model="tags"
            color="blue-grey lighten-2"
            label="Sichtbar für folgende User"
            multiple
            outline
            placeholder="Alle"
            :limitToTagRights="true"
          />
          <v-select
            v-model="mode"
            color="blue-grey lighten-2"
            label="Typ"
            :items="modes"
            outline />
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
            Test erstellen
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
import constants from "../../logic/constants"
import {mapGetters} from "vuex";

export default {
  props: ['value'],
  data() {
    return {
      isLoading: false,
      name: null,
      mode: constants.TEST.MODE_QUESTIONS,
      tags: [],
      modes: [
        {
          text: 'Statisch',
          value: constants.TEST.MODE_QUESTIONS,
        },
        {
          text: 'Zufällige Fragen',
          value: constants.TEST.MODE_CATEGORIES,
        },
      ],
      snackbar: {
        active: false,
        type: null,
        message: null,
      },
    }
  },
  computed: {
    ...mapGetters({
      isFullAdmin: 'app/isFullAdmin',
    }),
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
    createTest() {
      if(this.isLoading) {
        return
      }
      if(!this.tags.length && !this.isFullAdmin) {
        this.handleSnackbar('error', 'Bitte wählen Sie mindestens einen TAG')
        return
      }
      this.isLoading = true

      axios.post('/backend/api/v1/tests', {
        name: this.name,
        tags: this.tags,
        mode: this.mode,
      }).then(response => {
        window.location.href = `/tests/${response.data.test.id}`
        this.closeModal()
      }).catch(e => {
        console.log(e)
        alert('Der Test konnte leider nicht erstellt werden')
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
