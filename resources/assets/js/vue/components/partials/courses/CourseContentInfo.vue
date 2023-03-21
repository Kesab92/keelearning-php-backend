<template>
  <div :class="{
      'body-2': !subtle,
      'body-3': subtle,
    }">
    <course-content-icon
      v-if="course"
      :count="course.chapters.length"
      :subtle="subtle"
      :type="$constants.COURSES.TYPE_CHAPTER"
      class="mr-2" />
    <course-content-icon
      v-for="contentCount in contentCounts"
      :key="contentCount.type"
      :count="contentCount.count"
      :subtle="subtle"
      :type="contentCount.type"
      class="mr-2" />
    {{ duration }} min
  </div>
</template>

<script>
import CourseContentIcon from './CourseContentIcon.vue'

const COUNT_TYPES = [
  {
    typeLabel: 'TYPE_LEARNINGMATERIAL',
    label: 'Dateien',
  },
  {
    typeLabel: 'TYPE_QUESTIONS',
    label: 'Lernfragen',
  },
  {
    typeLabel: 'TYPE_CERTIFICATE',
    label: 'Zertifikate',
  },
]

export default {
  props: {
    chapter: {
      default: null,
      required: false,
      type: Object,
    },
    course: {
      default: null,
      required: false,
      type: Object,
    },
    subtle: {
      default: false,
      required: false,
      type: Boolean,
    },
  },
  computed: {
    contentCounts() {
      return COUNT_TYPES.map((countType) => {
        countType.type = this.$constants.COURSES[countType.typeLabel]
        countType.count = this.contents.filter((content) => content.type == countType.type && content.visible).length
        return countType
      }).filter(contentCount => !!contentCount.count)
    },
    contents() {
      if (this.chapter) {
        return this.chapter.contents
      }
      if (this.course.contents) {
        return this.course.contents
      }
      return this.course.chapters.reduce((contents, chapter) => contents.concat(chapter.contents), [])
    },
    duration() {
      return this.contents.filter(content => content.visible).reduce((total, content) => total + content.duration, 0)
    },
  },
  components: {
    CourseContentIcon,
  },
}
</script>
