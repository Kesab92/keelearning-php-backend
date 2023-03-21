<template>
  <div v-if="courseData">
    <CourseToolbar
      :course="courseData"
      :is-saving="isSaving"
      @save="save"
      @updateVisibility="updateVisibility"
      @updateNewCourseNotification="updateNewCourseNotification"
    />
    <div class="pa-4">
      <v-layout
        row
        class="mb-5">
        <v-flex xs6>
          <translated-input
            v-model="courseData.title"
            :translations="courseData.translations"
            attribute="title"
            label="Kurs Name"
            hide-details
            class="mb-4"
            :readOnly="isReadonly"/>

          <v-select
            v-model="courseAccessType"
            :items="accessTypeItems"
            :disabled="isReadonly"
            color="blue-grey lighten-2"
            hide-details
            class="mb-3"
            label="Art des Kurszugangs"/>

          <tag-select
            v-if="!courseData.has_individual_attendees"
            v-model="courseData.tags"
            label="Sichtbar für folgende TAGs"
            placeholder="Alle"
            :disabled="isReadonly"
            limit-to-tag-rights
            show-limited-tags
            multiple/>

          <user-select
            v-if="courseData.has_individual_attendees"
            v-model="courseData.individualAttendees"
            module="courses"
            color="blue-grey lighten-2"
            label="Sichtbar für einzelne User"
            multiple
            placeholder="User wählen..."
            :disabled="isReadonly" />

          <Toggle
            v-model="courseData.is_mandatory"
            :disabled="isReadonly"
            label="Pflichtkurs"/>

          <content-category-select
            v-model="courseData.categories"
            label="Kategorie"
            :disabled="isReadonly"
            :type="$constants.CONTENT_CATEGORIES.TYPE_COURSES"
            multiple />

          <v-alert
            v-if="courseData.parent && courseData.parent.nextRepetitionDate"
            :value="true"
            type="info">
            Dieser Kurs wird automatisch am {{ courseData.parent.nextRepetitionDate | date }} wiederholt. Dabei wird dieser Kurs archiviert und ein neuer Kurs aus der Vorlage <router-link class="white--text" style="text-decoration: underline;" :to="{ name: 'courses.templates.edit.general', params: { courseId: courseData.parent.id }}">{{ courseData.parent.title }}</router-link> erstellt.<br>
            Der dann neu erstellte Kurs trägt den Kursnamen der Vorlage + aktuelle Woche/Jahr
          </v-alert>
        </v-flex>

        <v-flex xs6 class="ml-4">
          <ImageUploader
            :current-image="courseData.cover_image_url"
            name="Coverbild"
            width="100%"
            height="auto"
            :url="`/backend/api/v1/courses/${courseData.id}/cover`"
            :isReadOnly="isReadonly"
            @newImage="handleNewImage" />

          <v-layout class="mt-3">
            <v-spacer/>
            <table>
              <tr>
                <td class="pr-2">
                  Eskalationsmanagement:
                </td>
                <td>
                  <template v-if="courseData.hasReminders">
                    <span class="green--text">aktiv</span>
                  </template>
                  <template v-else>
                    inaktiv
                  </template>
                </td>
              </tr>
              <template v-if="!courseData.is_template">
                <tr>
                  <td class="pr-2">
                    User:
                  </td>
                  <td>
                    {{ courseData.eligibleUserCount }}
                  </td>
                </tr>
                <tr>
                  <td class="pr-2">
                    Eingeschrieben:
                  </td>
                  <td>
                    {{ courseData.participationUserCount }}
                  </td>
                </tr>
                <tr>
                  <td class="pr-2">
                    Abgeschlossen:
                  </td>
                  <td>
                    {{ courseData.finishedParticipationUserPercentage }}%
                  </td>
                </tr>
                <tr>
                  <td class="pr-2">
                    Bestanden:
                  </td>
                  <td>
                    {{ courseData.passedParticipationUserPercentage }}%
                  </td>
                </tr>
                <tr>
                  <td colspan="2" style="text-align: right;">
                    <v-btn
                      :href="`/course-statistics/${courseData.id}`"
                      class="mt-3 mr-0"
                      color="primary">
                      <v-icon left>trending_up</v-icon>
                      Statistiken
                    </v-btn>
                  </td>
                </tr>
              </template>
            </table>
          </v-layout>
        </v-flex>
      </v-layout>

      <translated-input
        v-model="courseData.description"
        input-type="texteditor"
        :translations="courseData.translations"
        attribute="description"
        label="Kurs-Beschreibung"
        :readOnly="isReadonly"/>
    </div>
  </div>
</template>

<script>
import ClickOutside from "vue-click-outside"
import {mapGetters} from "vuex"
import CourseToolbar from "./CourseToolbar"
import TagSelect from "../partials/global/TagSelect"
import TextEditor from "../partials/global/TextEditor"
import ImageUploader from "../partials/global/ImageUploader"
import UserSelect from "../partials/global/UserSelect.vue"

export default {
  props: ["course"],
  data() {
    return {
      courseData: null,
      accessTypeItems: [{text: 'TAG Zuweisung', value: 'tags'}, {text: 'Einzelne User', value: 'users'}]
    }
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
    // We map the has_individual_attendees column to the access type, because in the future we will
    // allow both, setting TAGs and individual attendees at the same time. Therefore it will make more sense
    // to have a toggle instead of a select.
    courseAccessType: {
      get() {
        return this.courseData.has_individual_attendees ? 'users' : 'tags'
      },
      set(newValue) {
        this.courseData.has_individual_attendees = newValue === 'users'
      },
    },
    isReadonly() {
      return !this.myRights['courses-edit']
    },
  },
  watch: {
    course: {
      handler() {
        this.courseData = JSON.parse(JSON.stringify(this.course))
      },
      immediate: true,
    },
  },
  methods: {
    handleNewImage(image) {
      this.courseData.cover_image_url = image
    },
     save() {
      if (this.isSaving) {
        return
      }
      this.isSaving = true
      this.$store.dispatch("courses/saveCourse", {
        id: this.courseData.id,
        title: this.courseData.title,
        description: this.courseData.description,
        tags: this.courseData.tags,
        is_mandatory: this.courseData.is_mandatory,
        cover_image_url: this.courseData.cover_image_url,
        visible: this.courseData.visible,
        categories: this.courseData.categories,
        send_new_course_notification: this.courseData.send_new_course_notification,
        has_individual_attendees: this.courseData.has_individual_attendees,
        individualAttendees: this.courseData.individualAttendees,
      }).catch((error) => {
        if(error.response.data.message) {
          alert(error.response.data.message)
        } else {
          alert('Ein unbekannter Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.')
        }
      }).finally(() => {
        this.isSaving = false
      })
    },
    updateVisibility(visible) {
      this.courseData.visible = visible
    },
    updateNewCourseNotification(sendNotification) {
      this.courseData.send_new_course_notification = sendNotification
    },
  },
  components: {
    UserSelect,
    CourseToolbar,
    TagSelect,
    TextEditor,
    ImageUploader,
  },
  directives: {
    ClickOutside,
  },
}
</script>
