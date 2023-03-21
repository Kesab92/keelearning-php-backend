<template>
  <v-layout
    v-if="wbts.length"
    row>
    <v-flex shrink>
      <v-navigation-drawer
        permanent>
        <v-toolbar flat>
          <v-list>
            <v-list-tile>
              <v-list-tile-title class="title">
                WBT Ergebnisse
              </v-list-tile-title>
            </v-list-tile>
          </v-list>
        </v-toolbar>
        <v-list
          dense
          class="pt-0">
          <v-list-tile
            v-for="wbt in wbts"
            :key="wbt.id"
            active-class="primary white--text"
            :to="`/course-statistics/wbts/${wbt.id}`"
          >
            <v-list-tile-content>
              <v-list-tile-title>{{ wbt.title ? wbt.title : wbt.relatable.title }}</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </v-list>
      </v-navigation-drawer>
    </v-flex>
    <v-flex style="flex-grow: 1;">
      <router-view
        :course="course" />
    </v-flex>
  </v-layout>
  <v-alert
    v-else
    class="ma-4"
    :value="true"
    type="info">
    Dieser Kurs hat aktuell keine Web Based Trainings.
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
      default: [],
    },
  },
  watch: {
    '$route': {
      handler() {
        if(!this.$route.params.wbtId) {
          if(this.wbts.length) {
            this.$router.replace('/course-statistics/wbts/' + this.wbts[0].id)
          }
        }
      },
      immediate: true,
    },
  },
  computed: {
    wbts() {
      return this.course.chapters.reduce((wbts, chapter) => {
        chapter.contents.forEach((content) => {
          if(content.type === this.$constants.COURSES.TYPE_LEARNINGMATERIAL && content.relatable && content.relatable.wbt_id) {
            wbts.push(content)
          }
        })
        return wbts
      }, [])
    }
  }
}
</script>
