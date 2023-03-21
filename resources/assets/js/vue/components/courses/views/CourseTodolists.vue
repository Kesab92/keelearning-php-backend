<template>
  <div class="pa-4">
    <v-layout row>
      <v-flex xs6 class="mr-2">
        <v-autocomplete
          v-model="selectedContentId"
          :items="todolists"
          label="Aufgabenliste"
          item-text="title"
          item-value="id"
          hide-details
          outline
          class="mb-1"
        />
      </v-flex>
      <v-flex xs6>
        <v-autocomplete
          v-if="participations"
          v-model="selectedParticipationId"
          :items="participations"
          :allow-overflow="false"
          item-text="name"
          item-value="participation_id"
          hide-details
          label="User"
          outline
          />
      </v-flex>
    </v-layout>

    <v-alert
      v-if="!selectedParticipationId || !selectedContentId"
      :value="true"
      type="info"
    >
      Wählen Sie eine Aufgabenliste und einen User um den Lernstand anzuzeigen.
    </v-alert>

    <router-view v-if="selectedParticipation" :key="`${selectedContentId}-${selectedParticipationId}`" :course="course" :participation="selectedParticipation" />
  </div>
</template>

<script>
import UserSelect from "../../partials/global/UserSelect.vue"

export default {
  components: {UserSelect},
  props: ["course"],
  data() {
    return {
      selectedContentId: null,
      selectedParticipationId: null,
      participations: null,
    }
  },
  inject: ['routePrefix'],
  created() {
    this.loadParticipations()
    this.selectedContentId = parseInt(this.$route.params.contentId) || null
    this.selectedParticipationId = parseInt(this.$route.params.participationId) || null
  },
  watch: {
    selectedContentId() {
      this.redirectToResults()
    },
    selectedParticipationId() {
      this.redirectToResults()
    },
  },
  computed: {
    todolists() {
      return this.course.chapters.reduce((todolists, chapter) => {
        const chapterTodolists = chapter.contents.filter(content => content.type === this.$constants.COURSES.TYPE_TODOLIST).map(content => {
          return {
            id: content.id,
            title: `${chapter.title}: ${content.title || "[Neuer Inhalt]"}`,
          }
        })
        return [...todolists, ...chapterTodolists]
      }, [])
    },
    selectedParticipation() {
      if(this.participations === null) {
        return null
      }
      return this.participations.find((participation) => participation.participation_id === this.selectedParticipationId)
    }
  },
  methods: {
    loadParticipations() {
      axios.get(`/backend/api/v1/courses/${this.course.id}/participations/participants`).then((response) => {
        this.participations = response.data.participations
      }).catch(() => {
        alert('Die Liste von Usern konnte leider nicht geladen werden. Bitte versuchen Sie es später erneut.')
      })
    },
    redirectToResults() {
      if(!this.selectedContentId || ! this.selectedParticipationId) {
        const baseRouteName = `${this.routePrefix}.edit.todolists`
        if(this.$route.name === baseRouteName) {
          return
        }
        this.$router.push({
          name: baseRouteName,
          params: {
            courseId: this.course.id,
          },
        })
      } else {
        this.$router.push({
          name: `${this.routePrefix}.edit.todolists.results`,
          params: {
            courseId: this.course.id,
            contentId: this.selectedContentId,
            participationId: this.selectedParticipationId,
          },
        })
      }
    }
  },
}
</script>
