<template>
  <div>
    <details-sidebar-toolbar>
      <template
        v-if="courseData"
        v-slot:default>
        <v-btn
          :loading="isSaving"
          :disabled="isReadonly || !isValid"
          color="primary"
          @click="save"
        >
          Speichern
        </v-btn>

        <v-switch
          v-model="courseData.visible"
          :disabled="isReadonly || !someContentsVisible"
          class="s-switch -dense ml-3"
          hide-details
          height="30"
          :label="courseData.is_template ? 'Aktiv' : 'Sichtbar'"
          @change="$emit('updateVisibility', courseData.visible)"
        />

        <v-spacer/>

        <v-menu offset-x offset-y>
          <v-btn
            slot="activator"
            flat
          >
            Aktionen
            <v-icon right>arrow_drop_down</v-icon>
          </v-btn>
          <v-list>
            <v-list-tile
              v-for="(action, index) in actions"
              :key="`course-action-${index}`"
              :disabled="isReadonly"
              @click="doAction(action)"
            >
              <v-list-tile-title>{{ action.title }}</v-list-tile-title>
            </v-list-tile>
          </v-list>
        </v-menu>
      </template>
    </details-sidebar-toolbar>
    <v-alert
      outline
      type="info"
      class="mb-4"
      :value="!someContentsVisible">
      Die Kurs-Sichtbarkeit kann erst auf aktiv gesetzt werden, wenn ein Kurs über einen sichtbaren Inhalt verfügt.
    </v-alert>
    <v-alert
      v-if="persistedCourseData"
      outline
      type="warning"
      class="mb-4"
      :value="noUsersAssigned">
      Dieser Kurs hat noch keine User zugewiesen.
    </v-alert>
    <v-alert
      outline
      type="info"
      color="grey"
      class="mb-4"
      :value="courseData.archived_at">
      Dieser Kurs ist archiviert und wird in der App nicht angezeigt.
    </v-alert>
    <DeleteDialog
      v-model="deleteDialogOpen"
      :deletion-url="`/backend/api/v1/courses/${courseData.id}`"
      :dependency-url="`/backend/api/v1/courses/${courseData.id}/delete-information`"
      :entry-name="courseData.name"
      :redirect-url="afterDeletionRedirectURL"
      :type-label="courseData.is_template ? 'Vorlage' : 'Kurs'"
      require-confirmation
      @deleted="handleCourseDeleted">
      <template
        v-if="!courseData.is_template"
        v-slot:append-message>
        <p class="mt-3">Tipp:</p>
        <v-alert
          outline
          type="info"
          class="mb-4"
          :value="true">
          Wenn Sie den Kurs archivieren, anstatt ihn zu löschen, bleiben die Statistiken erhalten.
        </v-alert>
      </template>
    </DeleteDialog>
    <ArchiveCourseModal
      v-model="archiveModalOpen"
      :course="courseData" />
    <SaveCourseModal
      v-model="saveModalOpen"
      :course="courseData"
      @confirm="$emit('save')"
      @updateNewCourseNotification="updateNewCourseNotification"
    />
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import {isFuture, isPast} from "date-fns"
import DeleteDialog from "../partials/global/DeleteDialog"
import ArchiveCourseModal from "./components/ArchiveCourseModal"
import SaveCourseModal from "./components/SaveCourseModal"

export default {
  props: {
    course: {
      type: Object,
      required: true,
    },
    isValid: {
      type: Boolean,
      required: false,
      default: true,
    },
  },
  data() {
    return {
      persistedCourseData: null,
      courseData: null,
      deleteDialogOpen: false,
      archiveModalOpen: false,
      saveModalOpen: false,
    }
  },
  watch: {
    course: {
      handler() {
        if(!this.course) {
          return
        }
        this.courseData = JSON.parse(JSON.stringify(this.course))
        if(!this.persistedCourseData) {
          this.persistedCourseData = this.courseData
        }
      },
      immediate: true,
      deep: true,
    },
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    isSaving: {
      get() {
        return this.$store.state.courses.isSaving
      },
      set(data) {
        this.$store.commit('courses/setIsSaving', data)
      },
    },
    actions() {
      let actions = [
        {
          name: 'preview',
          title: 'Vorschau',
        },
      ]

      if (!this.courseData.is_template) {
        if (this.courseData.archived_at) {
          actions.push({
            name: 'unarchive',
            title: 'Dearchivieren'
          })
        } else {
          actions.push({
            name: 'archive',
            title: 'Archivieren'
          })
          actions.push({
            name: 'duplicate',
            title: 'Duplizieren'
          })
          actions.push({
            name: 'duplicate-as-template',
            title: 'Als Vorlage speichern'
          })
        }
      }

      actions.push({
        name: 'delete',
        title: 'Löschen',
      })

      return actions
    },
    someContentsVisible() {
      return this.course.chapters.some(chapter => {
        return chapter.contents.some(content => {
          return content.visible
        })
      })
    },
    noUsersAssigned() {
      return this.persistedCourseData.has_individual_attendees && this.persistedCourseData.individualAttendees.length === 0
    },
    isReadonly() {
      return !this.myRights['courses-edit']
    },
    afterDeletionRedirectURL() {
      return "/courses#/courses"
    },
    madeVisible() {
      if(this.courseData.available_status) {
        return false
      }
      if(!this.courseData.visible) {
        return false
      }

      // there's no fixed start date if we have a dynamic duration
      if (this.courseData.duration_type == this.$constants.COURSES.DURATION_TYPES.DYNAMIC) {
        return true
      }

      const availableFrom = new Date(this.courseData.available_from)
      const availableUntil = new Date(this.courseData.available_until)

      if(this.courseData.available_from && isFuture(availableFrom)) {
        return false
      }
      if(this.courseData.available_until && isPast(availableUntil)) {
        return false
      }

      return true
    }
  },
  methods: {
    doAction(action) {
      switch (action.name) {
        case 'preview':
          window.open(this.courseData.frontendUrl)
          break
        case 'archive':
          this.archiveModalOpen = true
          break
        case 'duplicate':
          this.duplicateCourse()
          break
        case 'duplicate-as-template':
          this.duplicateCourseAsTemplate()
          break
        case 'unarchive':
          this.unarchive()
          break
        case 'delete':
          this.deleteDialogOpen = true
          break
      }
    },
    duplicateCourse() {
      axios.post(`/backend/api/v1/courses/${this.course.id}/clone`, {})
        .then((response) => {
          this.$router.push(`/courses/${response.data.course_id}/general`)
        })
        .catch(() => {
          alert('Der Kurs konnte nicht dupliziert werden. Bitte probieren Sie es später erneut.')
        })
    },
    duplicateCourseAsTemplate() {
      axios.post(`/backend/api/v1/courses/${this.course.id}/clone-as-template`, {})
        .then((response) => {
          this.$router.push(`/courses/templates/${response.data.course_id}/general`)
        })
        .catch(() => {
          alert('Der Kurs konnte nicht dupliziert werden. Bitte probieren Sie es später erneut.')
        })
    },
    remove() {
      this.deleteDialogOpen = true
    },
    handleCourseDeleted() {
      this.$store.commit("courses/deleteCourse", this.courseData.id)
      this.$store.dispatch("courses/loadCourses")
    },
    async unarchive() {
      if (this.isSaving) {
        return
      }

      this.isSaving = true
      await this.$store.dispatch("courses/unarchiveCourse", {
        id: this.course.id,
      })
      this.isSaving = false
    },
    save() {
      if(this.madeVisible && !this.courseData.is_template) {
        this.saveModalOpen = true
        return
      }
      this.persistedCourseData = null
      this.$emit('save')
    },
    updateNewCourseNotification(sendNotification) {
      this.$emit('updateNewCourseNotification', sendNotification)
    }
  },
  components: {
    DeleteDialog,
    ArchiveCourseModal,
    SaveCourseModal,
  },
}
</script>
