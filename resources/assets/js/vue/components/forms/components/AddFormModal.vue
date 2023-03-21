<template>
  <v-dialog
    max-width="600"
    persistent
    v-model="dialog">
    <v-form
      v-model="isValid"
      @submit.prevent="createForm">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Neues Formular erstellen
        </v-card-title>
        <v-card-text>
          <p>Wählen Sie einen Namen für das Formular</p>
          <v-text-field
            v-model.trim="title"
            autofocus
            label="Bezeichnung"
            required
            :rules="[
              $rules.required,
              $rules.minChars(3)
            ]"
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
            limitToTagRights
          />
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
            :disabled="isLoading || !isValid"
            color="primary"
            type="submit"
            flat>
            Formular erstellen
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
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
import {mapGetters} from "vuex"
import TagSelect from "../../partials/global/TagSelect"

export default {
  props: ['value'],
  data() {
    return {
      isValid: false,
      isLoading: false,
      title: '',
      tags: [],
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
    createForm() {
      if(this.isLoading) {
        return
      }
      if(!this.tags.length && !this.isFullAdmin) {
        this.handleSnackbar('error', 'Bitte wählen Sie mindestens einen TAG')
        return
      }

      this.isLoading = true

      axios.post('/backend/api/v1/forms', {
        title: this.title,
        tags: this.tags,
      }).then(response => {
        this.$router.push({
          name: 'forms.edit.general',
          params: {
            formId: response.data.form.id,
          },
        })
        this.$store.dispatch('forms/loadForms')
        this.closeModal()
      }).catch(e => {
        console.log(e)
        alert('Das Formular konnte leider nicht erstellt werden')
      }).finally(() => {
        this.isLoading = false
      })
    },
    closeModal() {
      this.title = ''
      this.tags = []

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
