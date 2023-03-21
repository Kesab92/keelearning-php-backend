<template>
  <v-card class="s-card">
    <template v-if="course">
      <v-toolbar
        card
        tabs
        dark
        color="secondary"
        prominent>
        <v-btn
          href="/courses#/courses"
          icon
        >
          <v-icon>keyboard_backspace</v-icon>
        </v-btn>
        <v-toolbar-title class="title">Statistiken für {{ course.title }}</v-toolbar-title>

        <v-spacer/>

        <v-btn
          :href="`/courses#/courses/${course.id}/general`"
        >
          <v-icon left>edit</v-icon>
          Kurs bearbeiten
        </v-btn>

        <template v-slot:extension>
          <v-tabs
            dark
            color="secondary">
            <v-tab
              tag="a"
              @click.stop.prevent="$router.push('/course-statistics')"
              key="overview">
              Kursfortschritt
            </v-tab>
            <v-tab
              tag="a"
              @click.stop.prevent="$router.push('/course-statistics/tests')"
              key="tests">
              Tests
            </v-tab>
            <v-tab
              tag="a"
              @click.stop.prevent="$router.push('/course-statistics/forms')"
              key="forms">
              Formulare
            </v-tab>
            <v-tab
              tag="a"
              @click.stop.prevent="$router.push('/course-statistics/wbts')"
              key="wbts">
              Web Based Trainings
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
        :key="$route.path"
        :tags="tags"
        :course="course" />
    </template>
  </v-card>
</template>

<script>
export default {
  props: ["id", "tags"],
  data() {
    return {
      course: null,
      activeTab: 'overview',
    }
  },
  created() {
    if(this.$route.path === '/') {
      this.$router.replace('/course-statistics')
    }
    this.loadData()
  },
  methods: {
    loadData() {
      return axios.get("/backend/api/v1/courses/" + this.id)
        .then((response) => {
          this.$set(this, 'course', response.data.course)
        })
        .catch(() => {
          alert("Der Kurs konnte leider nicht abgerufen werden. Bitte probieren Sie es später erneut.")
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
