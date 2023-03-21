<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
    <form @submit.prevent="createNews">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Neue News erstellen
        </v-card-title>
        <v-card-text>
          Vergeben Sie zuerst einen Titel für die News.
          <v-text-field
            v-model="title"
            autofocus
            label="Titel"
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
            :disabled="isLoading"
            color="primary"
            type="submit"
            flat>
            News erstellen
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
import {mapGetters} from "vuex";
import TagSelect from "../partials/global/TagSelect"

export default {
  props: ['value'],
  data() {
    return {
      isLoading: false,
      title: null,
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
    createNews() {
      if(this.isLoading) {
        return
      }
      if(!this.tags.length && !this.isFullAdmin) {
        this.handleSnackbar('error', 'Bitte wählen Sie mindestens einen TAG')
        return
      }
      this.isLoading = true

      axios.post('/backend/api/v1/news', {
        title: this.title,
        tags: this.tags,
      }).then(response => {
        this.$router.push({
          name: 'news.edit.general',
          params: {
            newsId: response.data.news.id,
          },
        })
        this.$store.dispatch('news/loadNews')
        this.closeModal()
      }).catch(e => {
        console.log(e)
        alert('Die News konnte leider nicht erstellt werden')
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
