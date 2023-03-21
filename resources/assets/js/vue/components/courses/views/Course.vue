<template>
  <v-card class="s-card">
    <template v-if="course">
      <v-toolbar
        @click="$router.push('/course')"
        card
        tabs
        dark
        color="primary"
        prominent>
        <v-btn
          href="/courses#/courses"
          icon
        >
          <v-icon>keyboard_backspace</v-icon>
        </v-btn>
        <v-toolbar-title class="title">{{ course.title }}</v-toolbar-title>

        <v-spacer/>

        <v-btn
          v-if="myRights['courses-stats']"
          :href="`/course-statistics/${course.id}#/course-statistics`"
          color="white"
          outline
        >
          <v-icon left>trending_up</v-icon>
          Statistiken
        </v-btn>

        <template v-slot:extension>
          <v-tabs
            dark
            color="primary">
            <v-tab
              tag="a"
              @click.stop.prevent="$router.push('/course')"
              key="overview">
              Kursübersicht
            </v-tab>
            <v-tab
              tag="a"
              @click.stop.prevent="openContents"
              key="contents">
              Inhalte
            </v-tab>
            <v-tab
              tag="a"
              @click.stop.prevent="$router.push('/course/managers')"
              key="managers">
              Verantwortliche
            </v-tab>
            <v-tab
              v-if="appSettings.module_comments == 1 && myRights['comments-personaldata']"
              tag="a"
              @click.stop.prevent="$router.push('/course/comments')"
              key="comments">
              Kommentare
            </v-tab>
            <v-tab
              v-if="course.is_template && templateInheritanceApps.length"
              tag="a"
              @click.stop.prevent="$router.push('/course/templateInheritanceApps')"
              key="comments">
              Mandanten Vorlagen
            </v-tab>
          </v-tabs>
        </template>
      </v-toolbar>

      <v-layout
        v-if="!course"
        align-center
        class="s-loadingWrapper"
        justify-center>
        <v-progress-circular
          :size="50"
          color="primary"
          indeterminate/>
      </v-layout>
      <router-view
        v-else
        :available-tags="tags"
        :course="course"
        @contentAdded="handleNewContent"
        @contentsUpdated="loadData"
        @updateChapterPositions="handleUpdateChapterPositions"
        @updateContentPositions="handleUpdateContentPositions"
        @saved="loadData"/>
    </template>
  </v-card>
</template>

<script>
import courses from "../../../logic/courses"
import {mapGetters} from "vuex";

export default {
  props: ["id", "tags"],
  data() {
    return {
      course: null,
      templateInheritanceApps: [],
      activeTab: 'overview',
    }
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
      myRights: 'app/myRights',
    }),
  },
  created() {
    if(this.$route.path === '/') {
      this.$router.replace('/course')
    }
    this.loadData()
  },
  methods: {
    loadData() {
      return axios.get("/backend/api/v1/courses/" + this.id)
        .then((response) => {
          this.course = response.data.course
          this.templateInheritanceApps = response.data.templateInheritanceApps
        })
        .catch(() => {
          alert("Der Kurs konnte leider nicht abgerufen werden. Bitte probieren Sie es später erneut.")
        })
    },
    openContents() {
      if(!this.course) {
        return
      }
      this.$router.push('/course/contents')
    },
    handleNewContent({response, type}) {
      this.loadData().then(() => {
        if (type !== courses.TYPE_CHAPTER) {
          this.$router.push({
            name: 'course.content',
            params: {
              id: response.id,
            }
          })
        }
      })
    },
    handleUpdateChapterPositions(chapters) {
      this.$set(this.course, 'chapters', chapters)
      axios.post("/backend/api/v1/courses/" + this.id + "/chapterpositions", {
        chapters: chapters.map(chapter => {
          return {
            id: chapter.id,
            position: chapter.position,
          }
        }),
      })
      .catch(() => {
        alert("Die neue Sortierung konnte leider nicht gespeichert werden. Bitte probieren Sie es später erneut.")
      })
    },
    handleUpdateContentPositions(chapter) {
      const chapterIdx = this.course.chapters.findIndex(c => c.id === chapter.id)
      this.$set(this.course.chapters, chapterIdx, chapter)
      const contents = []
      chapter.contents.forEach(content => {
        contents.push({
          id: content.id,
          course_chapter_id: content.course_chapter_id,
          position: content.position,
        })
      })
      axios.post("/backend/api/v1/courses/" + this.id + "/contentpositions", {
        contents
      })
      .catch(() => {
        alert("Die neue Sortierung konnte leider nicht gespeichert werden. Bitte probieren Sie es später erneut.")
      })
    },
  },
}
</script>


<style lang="scss" scoped>
#app .flex.s-courseContents {
  max-width: 600px;
  border-right: 1px solid #dadada;
  background: #fbfbfb;
}

#app .s-loadingWrapper {
  min-height: 400px;
}

</style>
