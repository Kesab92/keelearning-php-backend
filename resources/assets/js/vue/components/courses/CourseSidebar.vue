<template>
  <details-sidebar
    :root-url="{
      name: rootUrl,
    }"
    :drawer-open="(typeof $route.params.courseId) !== 'undefined'"
    data-action="courses/loadCourse"
    :data-getter="(params) => $store.getters['courses/course'](params.courseId)"
    :data-params="{courseId: $route.params.courseId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: course, refresh, refreshSilently }">
      <router-view
        :course="course"
        @refresh="refresh"
        @refresh-silently="refreshSilently" />
    </template>
    <template v-slot:headerTitle="{ data: course }">
      {{ course.title }}
      <div v-if="course.is_template" class="s-drawerNavigation__subtitle">
        Vorlage
      </div>
    </template>
    <template v-slot:headerExtension="{ data: course }">
      <div>Erstellt am {{ course.created_at | date }}</div>
      <div v-if="course.creator">Erstellt von: {{ course.creator }}</div>
      <template v-if="nextRepetitionDate">
        <div>Nächste Wiederholung: {{ nextRepetitionDate | date }}</div>
      </template>
    </template>
  </details-sidebar>
</template>

<script>
import {mapGetters} from "vuex"
import helpers from "../../logic/helpers"

export default {
  props: {
    rootUrl: {
      default: 'courses.index',
      required: false,
      type: String,
    },
    routePrefix: {
      default: 'courses',
      required: false,
      type: String,
    },
  },
  provide() {
    return {
      routePrefix: this.routePrefix,
    }
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
      childApps: 'templateInheritance/childApps',
      myRights: 'app/myRights',
    }),
    course() {
      return this.$store.getters['courses/course'](this.$route.params.courseId)
    },
    nextRepetitionDate() {
      return helpers.nextRepetitionCourseDate(this.course)
    },
    hasTodolists() {
      return this.course.chapters.some(chapter => {
        return chapter.contents.some(content => content.type === this.$constants.COURSES.TYPE_TODOLIST)
      })
    }
  },
  methods: {
    getLinks(course) {
      let tabs = [
        {
          label: this.course.is_template ? 'Übersicht' : 'Allgemein',
          to: {
            name: `${this.routePrefix}.edit.general`,
            params: {
              courseId: course.id,
            },
          },
        },
        {
          label: 'Einstellungen',
          to: {
            name: `${this.routePrefix}.edit.settings`,
            params: {
              courseId: course.id,
            },
          },
        },
        {
          label: 'Inhalte',
          to: {
            name: `${this.routePrefix}.edit.contents`,
            params: {
              courseId: course.id,
            },
          },
        },
      ]
      if(this.appSettings.module_comments == 1 && !course.is_template && this.myRights['comments-personaldata']) {
        tabs.push({
          label: 'Kommentare',
          to: {
            name: `${this.routePrefix}.edit.comments`,
            params: {
              courseId: course.id,
            },
          },
        })
      }
      if(this.hasTodolists && !course.is_template) {
        tabs.push({
          label: 'Aufgabenlisten',
          to: {
            name: `${this.routePrefix}.edit.todolists`,
            params: {
              courseId: course.id,
            },
          },
        })
      }
      if(course.is_template && this.childApps.length) {
        tabs.push({
          label: 'Mandanten-Vorlage',
          to: {
            name: `${this.routePrefix}.edit.templateInheritance`,
            params: {
              courseId: course.id,
            },
          },
        })
      }
      return tabs
    }
  }
}
</script>
