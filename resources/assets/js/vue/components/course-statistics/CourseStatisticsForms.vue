<template>
  <v-layout
    v-if="contentsWithForm.length"
    row>
    <v-flex shrink>
      <v-navigation-drawer
        permanent>
        <v-toolbar flat>
          <v-list>
            <v-list-tile>
              <v-list-tile-title class="title">
                Formular Antworten
              </v-list-tile-title>
            </v-list-tile>
          </v-list>
        </v-toolbar>
        <v-list
          dense
          class="pt-0">
          <v-list-tile
            v-for="content in contentsWithForm"
            :key="content.id"
            v-if="content.relatable"
            active-class="primary white--text"
            :to="`/course-statistics/forms/${content.id}`"
          >
            <v-list-tile-content>
              <v-list-tile-title>
                <template v-if="content.title">
                  {{ content.title }}
                </template>
                <template v-else>
                  {{ content.relatable.title }}
                </template>
              </v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </v-list>
      </v-navigation-drawer>
    </v-flex>
    <v-flex
      class="overflow-auto overflow-y-hidden"
      style="flex-grow: 1;">
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
    Dieser Kurs hat aktuell keine Formulare.
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
        if(!this.$route.params.courseContentId) {
          if(this.contentsWithForm.length) {
            this.$router.replace('/course-statistics/forms/' + this.contentsWithForm[0].id)
          }
        }
      },
      immediate: true,
    },
  },
  computed: {
    contentsWithForm() {
      return this.course.chapters.reduce((contentsWithForm, chapter) => {
        chapter.contents.forEach((content) => {
          if(content.type === this.$constants.COURSES.TYPE_FORM) {
            contentsWithForm.push(content)
          }
        })
        return contentsWithForm
      }, [])
    }
  }
}
</script>
