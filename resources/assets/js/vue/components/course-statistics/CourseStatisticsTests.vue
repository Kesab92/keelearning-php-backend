<template>
  <v-layout
    v-if="tests.length"
    row>
    <v-flex shrink>
      <v-navigation-drawer
        permanent>
        <v-toolbar flat>
          <v-list>
            <v-list-tile>
              <v-list-tile-title class="title">
                Testergebnisse
              </v-list-tile-title>
            </v-list-tile>
          </v-list>
        </v-toolbar>
        <v-list
          dense
          class="pt-0">
          <v-list-tile
            v-for="test in tests"
            :key="test.id"
            active-class="primary white--text"
            :to="`/course-statistics/tests/${test.id}`"
          >
            <v-list-tile-content>
              <v-list-tile-title>{{ test.title }}</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </v-list>
      </v-navigation-drawer>
    </v-flex>
    <v-flex style="flex-grow: 1;">
      <router-view
        :course="course"
        :tags="tags" />
    </v-flex>
  </v-layout>
  <v-alert
    v-else
    class="ma-4"
    :value="true"
    type="info">
    Dieser Kurs hat aktuell keine Tests.
  </v-alert>
</template>

<script>

export default {
  props: {
    course: {
      type: Object,
      required: true,
    },
    tags: {
      type: Array,
      required: true,
    },
  },
  watch: {
    '$route': {
      handler() {
        if(!this.$route.params.testId) {
          if(this.tests.length) {
            this.$router.replace('/course-statistics/tests/' + this.tests[0].id)
          }
        }
      },
      immediate: true,
    },
  },
  computed: {
    tests() {
      return this.course.chapters.reduce((tests, chapter) => {
        chapter.contents.forEach((content) => {
          if(content.type === this.$constants.COURSES.TYPE_QUESTIONS && content.is_test) {
            tests.push(content)
          }
        })
        return tests
      }, [])
    }
  }
}
</script>
