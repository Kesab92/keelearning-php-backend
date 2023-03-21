<template>
  <div>
    <div
      class="c-courseProgress"
      :class="{
        '-started': hasStarted,
        '-finished': hasFinished,
      }"
    >
      <div
        v-for="chapter in course.chapters"
        :key="chapter.id"
        class="c-courseProgress__chapter">
        <div class="c-courseProgress__chapterTitle">
          {{ chapter.title }}
        </div>
        <UserCourseProgressEntry
          v-for="content in chapter.contents"
          :key="content.id"
          :course="course"
          :attempts="user.attempts"
          :content="content" />
      </div>
    </div>
  </div>
</template>

<script>
import UserCourseProgressEntry from "./UserCourseProgressEntry"
export default {
  props: {
    course: {
      type: Object,
      required: true,
    },
    user: {
      type: Object,
      required: true,
    },
  },
  computed: {
    hasStarted() {
      let firstContentId = this.course.chapters[0].contents[0].id
      if(!firstContentId) {
        return false
      }
      return typeof this.user.attempts[firstContentId] !== 'undefined' && this.user.attempts[firstContentId].passed !== null
    },
    hasFinished() {
      const lastChapter = this.course.chapters[this.course.chapters.length - 1]
      let lastContentId = lastChapter.contents[lastChapter.contents.length - 1].id
      if(!lastContentId) {
        return false
      }
      return typeof this.user.attempts[lastContentId] !== 'undefined' && this.user.attempts[lastContentId].passed !== null
    }
  },
  components: {UserCourseProgressEntry},
}
</script>
