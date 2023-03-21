<template>
  <div>
    <v-layout row>
      <v-flex
        class="s-courseContents"
        xs6>
        <CourseContentList
          :course="course"
          @contentAdded="handleContentAdded"
          @updateChapterPositions="handleUpdateChapterPositions"
          @updateContentPositions="handleUpdateContentPositions"/>
      </v-flex>
      <v-flex
        class="s-courseSettings pa-4"
        xs6>
        <router-view
          :key="$route.path"
          @saved="saved"
          @contentsUpdated="handleContentsUpdated"
          :course="course" />
      </v-flex>
    </v-layout>
  </div>
</template>

<script>
import CourseContentList from "../components/course-content-list/CourseContentList.vue"

export default {
  props: ["course"],
  watch: {
    "$route": {
      handler() {
        this.loadData()
      },
      immediate: true,
    },
  },
  methods: {
    loadData() {
      if (!this.$route.params.id) {
        this.redirectToFirstContent()
      }
    },
    redirectToFirstContent() {
      let contents = this.course.chapters.reduce((contents, chapter) => {
        chapter.contents.forEach((content) => {
          contents.push(content)
        })
        return contents
      }, [])
      if (contents.length) {
        this.$router.replace({
          name: 'course.content',
          params: {
            id: contents[0].id,
          },
        })
      }
    },
    saved() {
      this.$emit("saved")
    },
    handleContentAdded(data) {
      this.$emit("contentAdded", data)
    },
    handleContentsUpdated() {
      this.$emit('contentsUpdated')
    },
    handleUpdateChapterPositions(data) {
      this.$emit("updateChapterPositions", data)
    },
    handleUpdateContentPositions(data) {
      this.$emit("updateContentPositions", data)
    },
  },
  components: {
    CourseContentList,
  },
}
</script>
