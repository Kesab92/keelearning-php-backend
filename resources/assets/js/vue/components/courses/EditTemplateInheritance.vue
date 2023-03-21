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
      <h4 class="sectionHeader">Welche Mandanten sollen Zugriff auf diese Vorlage haben?</h4>
      <v-data-table
        v-model="courseData.templateInheritanceApps"
        :items="childApps"
        select-all
        item-key="id"
        hide-actions
        class="elevation-1"
      >
        <template v-slot:headers>
          <tr>
            <th style="width: 1px;">
              <v-checkbox
                :input-value="courseData.templateInheritanceApps.length === availableChildApps.length"
                primary
                hide-details
                :disabled="isReadonly"
                @click.stop="toggleAll"
              ></v-checkbox>
            </th>
            <th class="text-sm-left" colspan="2">
              Mandant
            </th>
          </tr>
        </template>
        <template v-slot:items="props">
          <tr
            @click="toggleApp(props.item.id)"
            :class="{
              'grey--text': isDisabled(props.item.id),
            }">
            <td>
              <v-checkbox
                :input-value="courseData.templateInheritanceApps.includes(props.item.id)"
                primary
                hide-details
                :disabled="isReadonly || isDisabled(props.item.id)"
              ></v-checkbox>
            </td>
            <td>
              {{ props.item.app_name }}

            </td>
            <td class="text-sm-right">
              <template v-if="isDisabled(props.item.id)">
                <v-icon color="grey" small>warning</v-icon> Kurs ist nicht in Sprache {{ props.item.default_language }} übersetzt
              </template>
            </td>
          </tr>
        </template>
      </v-data-table>
    </div>

  </div>
</template>

<script>
import {mapGetters} from "vuex"
import CourseToolbar from "./CourseToolbar"

export default {
  props: ["course"],
  data() {
    return {
      courseData: null,
    }
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
      childApps: 'templateInheritance/childApps',
    }),
    isSaving: {
      get() {
        return this.$store.state.courses.isSaving
      },
      set(data) {
        this.$store.commit('courses/setIsSaving', data)
      },
    },
    isReadonly() {
      return !this.myRights['courses-edit']
    },
    availableChildApps() {
      return this.childApps.filter(app => {
        return this.courseData.translations.some(translation => translation.language === app.default_language)
      })
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
    save() {
      if (this.isSaving) {
        return
      }

      const dataToSave = {
        id: this.courseData.id,
        visible: this.courseData.visible,
      }

      if (this.courseData.templateInheritanceApps) {
        dataToSave.templateInheritanceApps = this.courseData.templateInheritanceApps
      }

      this.$store.dispatch("courses/saveCourse", dataToSave).catch((error) => {
        if(error.response.data.message) {
          alert(error.response.data.message)
        } else {
          alert('Ein unbekannter Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.')
        }
      }).finally(() => {
        this.isSaving = false
      })
    },
    isDisabled(appId) {
      return !this.availableChildApps.some(app => app.id === appId)
    },
    updateVisibility(visible) {
      this.courseData.visible = visible
    },
    updateNewCourseNotification(sendNotification) {
      this.courseData.send_new_course_notification = sendNotification
    },
    toggleApp(appId) {
      if(this.isDisabled(appId)) {
        return
      }
      if(this.courseData.templateInheritanceApps.includes(appId)) {
        this.$set(this.courseData, 'templateInheritanceApps', this.courseData.templateInheritanceApps.filter(entry => entry !== appId))
      } else {
        this.courseData.templateInheritanceApps.push(appId)
      }
    },
    toggleAll () {
      if (this.courseData.templateInheritanceApps.length === this.availableChildApps.length) {
        this.$set(this.courseData, 'templateInheritanceApps', [])
      } else {
        this.$set(this.courseData, 'templateInheritanceApps', this.availableChildApps.map(child => child.id))
      }
    },
  },
  components: {
    CourseToolbar,
  },
}
</script>
