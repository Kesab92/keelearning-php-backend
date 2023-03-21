<template>
  <div>
    <CourseContentToolbar
      v-model="content"
      :key=" content ? `course-content-${content.id}` : `course-content-null`"
      :course="courseData"
      :is-saving="isSaving"
      @delete="onContentDelete"
      @save="doSave" />
    <v-layout row grow>
      <v-flex xs5>
        <CourseContentList
          :course="courseData"
          @contentAdded="handleContentAdded"
          @updateChapterPositions="handleUpdateChapterPositions"
          @updateContentPositions="handleUpdateContentPositions"
          class="s-scrollable" />
      </v-flex>
      <v-flex
        xs7
        pa-4
        class="s-scrollable">
        <router-view
          v-if="content"
          v-model="content"
          :course="courseData"
          ref="content" />
      </v-flex>
    </v-layout>
  </div>
</template>

<script>
import {mapGetters} from 'vuex'
import ContentEditor from './views/editors/ContentEditor'
import CourseContentList from './components/course-content-list/CourseContentList'
import CourseContentToolbar from './components/CourseContentToolbar'

let axiosCancel = null

export default {
  props: {
    course: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      content: null,
      courseData: null,
      isSaving: false,
    }
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    isReadonly() {
      return !this.myRights['courses-edit']
    },
    baseRoute() {
      return `courses.${this.course.is_template ? 'templates.' : ''}edit`
    },
  },
  watch: {
    '$route': {
      handler() {
        this.loadContentData()
      },
    },
    course: {
      handler() {
        this.courseData = JSON.parse(JSON.stringify(this.course))
        this.loadContentData()
      },
      immediate: true,
    },
  },
  methods: {
    async doSave() {
      if (this.isSaving) {
        return
      }
      let savePromise = this.$refs.content.save()
      if (!savePromise) {
        return
      }
      this.isSaving = true
      savePromise.then(() => {
        this.isSaving = false
        this.$emit('refresh-silently')
      }).catch(() => {
        alert('Der Inhalt konnte leider nicht gespeichert werden. Bitte probieren Sie es später erneut.')
        this.isSaving = false
      })
    },
    handleContentAdded({response, type}) {
      this.$emit('refresh-silently')
      if (type != this.$constants.COURSES.TYPE_CHAPTER) {
        this.$router.push({
          name: `${this.baseRoute}.contents.content`,
          params: {
            contentId: response.id,
            courseId: this.course.id,
          },
        })
      }
    },
    handleUpdateChapterPositions(chapters) {
      this.$set(this.courseData, 'chapters', chapters)
      axios.post(`/backend/api/v1/courses/${this.courseData.id}/chapterpositions`, {
        chapters: chapters.map(chapter => {
          return {
            id: chapter.id,
            position: chapter.position,
          }
        }),
      })
    },
    handleUpdateContentPositions(chapter) {
      const chapterIdx = this.courseData.chapters.findIndex(c => c.id === chapter.id)
      this.$set(this.courseData.chapters, chapterIdx, chapter)
      const contents = []
      chapter.contents.forEach(content => {
        contents.push({
          id: content.id,
          course_chapter_id: content.course_chapter_id,
          position: content.position,
        })
      })
      axios.post(`/backend/api/v1/courses/${this.courseData.id}/contentpositions`, {contents})
    },
    loadContentData() {
      if (!this.$route.params.contentId && !this.$route.params.chapterId) {
        this.$store.dispatch("courses/loadCourse", {courseId: this.courseData.id}).then(() => {
          this.redirectToFirstContent()
        })
        return
      }
      if (axiosCancel) {
        axiosCancel()
      }
      this.content = null
      switch (this.$route.name) {
        case `${this.baseRoute}.contents.chapter`:
          if (!this.courseData) {
            return
          }
          this.content = JSON.parse(JSON.stringify(this.courseData.chapters.find(c => c.id === parseInt(this.$route.params.chapterId))))
          break
        case `${this.baseRoute}.contents.content`:
          const cancelToken = new axios.CancelToken(c => {axiosCancel = c})
          axios.get(`/backend/api/v1/courses/${this.$route.params.courseId}/content/${this.$route.params.contentId}`, {cancelToken})
            .then((response) => {
              if(response instanceof axios.Cancel) {
                return
              }

              this.content = response.data.content
              if (response.data.attachments) {
                this.content.attachments = response.data.attachments
              }
            }).catch((error) => {
              if (!axios.isCancel(error)) {
                alert('Die Daten konnten nicht geladen werden.')
              }
            })
          break
        default:
          this.redirectToFirstContent()
          break
      }
    },
    onContentDelete() {
      this.$emit('refresh-silently')
    },
    redirectToFirstContent() {
      if (!this.courseData || !this.courseData.chapters.length) {
        return
      }
      const contents = this.courseData.chapters.reduce((contents, chapter) => {
        chapter.contents.forEach((content) => {
          contents.push(content)
        })
        return contents
      }, [])
      if (!contents.length) {
        if(this.$route.params.chapterId === this.courseData.chapters[0].id.toString()) {
          return
        }
        this.$router.replace({
          name: `${this.baseRoute}.contents.chapter`,
          params: {
            courseId: this.courseData.id.toString(),
            chapterId: this.courseData.chapters[0].id.toString(),
          },
        })
        return
      }
      if(this.$route.params.contentId === contents[0].id.toString()) {
        return
      }
      this.$router.replace({
        name: `${this.baseRoute}.contents.content`,
        params: {
          courseId: this.courseData.id.toString(),
          contentId: contents[0].id.toString(),
        },
      })
    },
  },
  components: {
    ContentEditor,
    CourseContentList,
    CourseContentToolbar,
  },
}
</script>


<style lang="scss" scoped>
#app .s-scrollable {
  height: calc(100vh - 124px);
  overflow-x: hidden;
  overflow-y: auto;
  padding-bottom: 50px;
}
</style>
