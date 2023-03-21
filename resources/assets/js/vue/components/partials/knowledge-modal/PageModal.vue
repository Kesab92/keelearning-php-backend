<template>
  <div>
    <v-snackbar
      v-model="success"
      :top="true"
      color="success">Die Seite wurde erfolgreich angelegt.</v-snackbar>
    <v-snackbar
      v-model="error"
      :top="true"
      color="error">Es ist ein Fehler aufgetreten. Bitte füllen Sie alle Felder korrekt aus.</v-snackbar>
    <v-dialog
      v-model="active"
      width="60%"
      content-class="no-margin">
      <slot slot="activator" />

      <v-card>
        <form @submit.prevent="storePage">
          <v-card-title
            class="headline grey lighten-2"
            primary-title>Neue Seite anlegen</v-card-title>
          <v-card-text>
            <v-text-field
              label="Titel"
              v-model="title"
              required
            />

            <v-select
              v-if="type !== 'faq'"
              label="Kategorie"
              v-model="category"
              :items="items"
              required
            />

            <Editor
              v-if="showEditor"
              ref="editor"
              v-model="content"
              :init="tinyMCEEditorOptions"
            />
          </v-card-text>
          <v-divider/>

          <v-card-actions>
            <v-spacer/>
            <v-btn @click="active = false">Abbrechen</v-btn>
            <v-btn
              type="submit"
              color="primary">Speichern</v-btn>
          </v-card-actions>
        </form>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
  import Editor from '@tinymce/tinymce-vue'
  import TinyMCEOptions from '../../../logic/tinyMCEOptions'

  export default {
    props: {
      categories: {
        type: Array,
        required: false,
      },
      type: {
        type: String,
        required: true
      }
    },
    components: {
      Editor
    },
    data() {
      return {
        active: false,
        success: false,
        category: null,
        content: null,
        title: null,
        error: null,
        showEditor: false,
      }
    },
    watch: {
      active () {
        // Seems like a tinymce bug: We need to reinitialize the editor when the modal is shown
        this.showEditor = false
        this.$nextTick(() => {
          this.showEditor = true
        })
      }
    },
    computed: {
      tinyMCEEditorOptions() {
        const options = TinyMCEOptions()
        options.plugins = ['link', 'lists']
        options.external_plugins = {
          link: '/js/plugins/link/plugin.js',
          lists: '/js/plugins/lists/plugin.js',
        }
        options.height = 800
        return options
      },
      items() {
        let items = []
        if (this.categories) {
          this.categories.forEach(item => {
            items.push({ text: item.name, value: item.id })
          })
        }
        return items
      }
    },
    methods: {
      storePage() {
        if (!this.content || this.content.length === 0) {
          this.error = true
          return
        }

        let data = {
          title: this.title,
          category: this.category,
          content: this.content,
          type: this.type
        }
        axios.post('/backend/api/v1/helpdesk/knowledge/pages', data).then(response => {
          if (response.data.success) {
            this.success = true
            this.active = false
            this.title = null
            this.category = null
            this.content = null
            this.$emit('update')
          }
        }).catch(error => {
          this.error = true
        })
      }
    }
  }
</script>

<style lang="scss">
  #app {
    .dialog.no-margin {
      margin-top: 10px;
    }

    .dialog.dialog:not(.dialog--fullscreen) {
      max-height: 100%;
    }
  }
</style>
