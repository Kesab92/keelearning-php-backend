<template>
  <div>
    <v-snackbar v-model="success" :top="true" color="success">{{ message }}</v-snackbar>
    <v-snackbar v-model="error" :top="true" color="error">Fehler: Eine Kategorie kann nur dann gelöscht werden, wenn diese keine Seiten enthält.</v-snackbar>
    <v-dialog v-model="active" width="500">
      <v-list-tile slot="activator">
        <a href="#" v-if="!category">
          <v-list-tile-content>
            <v-list-tile-title>Neue Kategorie</v-list-tile-title>
          </v-list-tile-content>
        </a>
        <v-list-tile-action v-else>
          <v-btn fab dark small color="primary">
            <v-icon dark>edit</v-icon>
          </v-btn>
        </v-list-tile-action>
      </v-list-tile>
      <v-card>
        <form @submit.prevent="save">
          <v-card-title class="headline grey lighten-2" primary-title>Neue Kategorie anlegen</v-card-title>
          <v-card-text>
            <v-text-field
              label="Name"
              v-model="name"
              required
            />
          </v-card-text>
          <v-divider></v-divider>

          <v-card-actions>
            <v-btn
              :loading="loading"
              :disabled="loading"
              color="error"
              v-if="category"
              @click.prevent="remove">
              Kategorie löschen
            </v-btn>
            <v-spacer></v-spacer>
            <v-btn
              @click="active = false"
              :loading="loading"
              :disabled="loading">
              Abbrechen
            </v-btn>
            <v-btn
              type="submit"
              color="primary"
              :loading="loading"
              :disabled="loading">
              Speichern
            </v-btn>
          </v-card-actions>
        </form>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
  export default {
    props: {
      category: {
        type: Object,
        required: false
      }
    },
    data() {
      return {
        name: null,
        error: false,
        success: false,
        active: false,
        message: null,
        loading: false
      }
    },
    created() {
      if (this.category) {
        this.name = this.category.name
      }
    },
    methods: {
      save() {
        if (this.name == null) {
          return
        }

        this.category == null ? this.store() : this.update()
      },
      store() {
        this.loading = true
        axios.post('/backend/api/v1/helpdesk/knowledge/categories', { name: this.name }).then(response => {
          if (response.data.success) {
            this.showMessageAndUpdate(true, 'Die Kategorie wurde erfolgreich gespeichert.')
          }
          this.loading = false
        })
      },
      update() {
        this.loading = true
        axios.post('/backend/api/v1/helpdesk/knowledge/categories/' + this.category.id, { name: this.name }).then(response => {
          if (response.data.success) {
            this.showMessageAndUpdate(true, 'Die Kategorie wurde erfolgreich aktualisiert.')
          }
          this.loading = false
        })
      },
      remove() {
        this.loading = true
        axios.post('/backend/api/v1/helpdesk/knowledge/categories/' + this.category.id + '/remove').then(response => {
          if (response.data.success) {
            this.showMessageAndUpdate(true, 'Die Kategorie wurde erfolgreich gelöscht.')
          } else if (response.data.error === 'MOVE_PAGES')  {
            this.error = true
          }
          this.loading = false
        })
      },
      showMessageAndUpdate(success, message) {
        this.message = message
        this.success = success
        this.$emit('update')
        this.active = false
      }
    }
  }
</script>