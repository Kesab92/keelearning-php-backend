<template>
  <div
    v-if="courseProgress !== null"
    class="s-overall__wrapper">
    <template v-if="userCount">
      <div class="s-overall__description">
        Gesamtfortschritt
      </div>
      <div class="s-overall__progress">
        <div
          class="c-courseProgress"
          :class="{
            '-started': hasStarted,
            '-finished': isFinished,
          }"
        >
          <div
            v-for="chapter in course.chapters"
            :key="chapter.id"
            class="c-courseProgress__chapter">
            <div class="c-courseProgress__chapterTitle">
              {{ chapter.title }}
            </div>
            <OverallCourseProgressEntry
              v-for="content in chapter.contents"
              :key="content.id"
              :course="course"
              :course-progress="courseProgress"
              :user-count="userCount"
              :content="content" />
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import OverallCourseProgressEntry from "./OverallCourseProgressEntry"
export default {
  components: {OverallCourseProgressEntry},
  props: {
    course: {
      type: Object,
      required: true,
    },
    userCount: {
      type: Number,
      default: null,
    },
  },
  data() {
    return {
      courseProgress: null,
      isLoading: false,
    }
  },
  created() {
    this.loadData()
  },
  computed: {
    hasStarted() {
      return Object.keys(this.courseProgress).length > 0
    },
    isFinished() {
      const hasUnfinishedContent = this.course.chapters.find(chapter => {
        return chapter.contents.find(content => {
          return !this.courseProgress[content.id] || this.courseProgress[content.id] < this.userCount
        })
      })
      return !hasUnfinishedContent
    }
  },
  methods: {
    loadData() {
      this.isLoading = true
      axios.get("/backend/api/v1/course-statistics/" + this.course.id + "/overall").then(response => {
        this.courseProgress = response.data
        this.isLoading = false
      }).catch(e => {
        console.log(e)
      })
    },
    getOpacity(contentId) {
      const min = 0.3
      if(typeof this.courseProgress[contentId] === 'undefined') {
        return min
      }
      return Math.max(this.courseProgress[contentId] / this.userCount, min)
    }
  },
}
</script>

<style lang="scss" scoped>
#app {
  .s-overall__wrapper {
    display: flex;
    flex-direction: row;
    width: 100%;
    margin-top: 15px;
  }

  .s-overall__description {
    width: 211px;
    padding: 8px 24px 0 24px;
    font-size: 16px;
  }

  .s-overall__progress {
    flex-grow: 1;
    overflow-x: auto;
    padding: 0 24px;
  }
}
</style>
