<template>
  <v-dialog
    max-width="850"
    persistent
    v-model="dialog">
    <form @submit.prevent="createCourse">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          <v-btn
            v-show="selectedTemplateId !== null"
            @click="selectedTemplateId = null"
            icon>
            <v-icon>keyboard_backspace</v-icon>
          </v-btn>
          <template v-if="!createTemplate">
            Neuen Kurs erstellen
          </template>
          <template v-else>
            Neue Kurs-Vorlage erstellen
          </template>
        </v-card-title>
        <v-card-text>
          <SelectTemplate
            v-if="selectedTemplateId === null"
            v-model="selectedTemplateId"
            :createTemplate="createTemplate"
            :templates="templates" />
          <EditSettings
            v-else
            v-model="courseSettings"
            :createTemplate="createTemplate"
            :selectedTemplate="selectedTemplate" />
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
            :disabled="isLoading || !isReady"
            color="primary"
            type="submit"
            flat>
            <template v-if="createTemplate">
              Kurs-Vorlage erstellen
            </template>
            <template v-else>
              Kurs erstellen
            </template>
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
import {mapGetters} from 'vuex'
import EditSettings from './EditSettings'
import SelectTemplate from './SelectTemplate'

export default {
  props: {
    createTemplate: {
      default: false,
      type: Boolean,
    },
    value: {
      required: true,
      type: Boolean,
    },
  },
  data() {
    return {
      isLoading: false,
      courseSettings: {
        tags: [],
        title: null,
      },
      selectedTemplateId: null,
      snackbar: {
        active: false,
        type: null,
        message: null,
      },
    }
  },
  created() {
    this.$store.dispatch('courses/loadAllTemplates')
  },
  watch: {
    selectedTemplateId() {
      if (!this.selectedTemplateId) {
        this.courseSettings.title = ''
      } else {
        this.courseSettings.title = this.selectedTemplate.title
      }
    },
  },
  methods: {
    createCourse() {
      if(this.isLoading) {
        return
      }
      if(!this.courseSettings.tags.length && !this.isFullAdmin) {
        this.handleSnackbar('error', 'Bitte wählen Sie mindestens einen TAG')
        return
      }
      this.isLoading = true

      axios.post('/backend/api/v1/courses', {
        is_template: this.createTemplate,
        tags: this.courseSettings.tags,
        template: this.selectedTemplateId,
        title: this.courseSettings.title,
      }).then(response => {
        if (response.data.warnings.includes('MissingDefaultLanguage')) {
          alert('Nicht alle Inhalte sind in Ihrer Standardsprache verfügbar!')
        }
        this.$router.push({
          name: response.data.course.is_template ? 'courses.templates.edit.general' : 'courses.edit.general',
          params: {
            courseId: response.data.course.id,
          },
        })
        this.closeModal()
      }).catch(error => {
        if (error.response.data.message) {
          alert(error.response.data.message)
        } else {
          alert('Der neue Kurs konnte leider nicht erstellt werden')
        }
      }).finally(() => {
        this.isLoading = false
      })
    },
    closeModal() {
      this.dialog = false
      this.courseSettings = {
        tags: [],
        title: null,
      }
      this.selectedTemplateId = null
    },
    handleSnackbar(type, message) {
      this.snackbar.active = true
      this.snackbar.type = type
      this.snackbar.message = message
    },
  },
  computed: {
    ...mapGetters({
      templates: 'courses/allTemplates',
      isFullAdmin: 'app/isFullAdmin',
    }),
    dialog: {
      set(value) {
        this.$emit('input', value)
      },
      get() {
        return this.value
      }
    },
    isReady() {
      if (this.selectedTemplateId === null) {
        return false
      }
      if (!this.courseSettings.title) {
        return false
      }
      if (!this.isFullAdmin && !this.courseSettings.tags.length) {
        return false
      }
      return true
    },
    selectedTemplate() {
      if (!this.selectedTemplateId) {
        return null
      }
      const template = this.templates.local.find((template) => template.id == this.selectedTemplateId)
      if (template) {
        return template
      }
      return this.templates.global.find((template) => template.id == this.selectedTemplateId)
    },
  },
  components: {
    EditSettings,
    SelectTemplate,
  }
}
</script>
