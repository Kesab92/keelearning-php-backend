<template>
  <div v-if="!isLoading" class="mt-4">
    <v-alert
      v-if="attempt === null"
      :value="true"
      type="info"
    >
      {{ participation.name }} hat mit der Bearbeitung dieser Aufgabenliste noch nicht begonnen.
    </v-alert>
    <div v-else>
      <v-layout row align-center class="mt-section mb-2">
        <v-flex>
          <h4 class="mb-0 sectionHeader">Aufgabenliste</h4>
        </v-flex>
        <v-flex shrink>
          <v-btn
            :to="`/courses/${course.id}/contents/content/${this.$route.params.contentId}`"
            flat>
            <v-icon left>edit</v-icon>
            Bearbeiten
          </v-btn>
        </v-flex>
      </v-layout>
      <v-layout v-for="item in todolist.todolist_items" :key="item.id" row align-center class="mb-1">
        <div class="s-iconWrapper">
          <v-icon class="px-2" medium>{{ itemStatus[item.id] ? 'check_box' : 'check_box_outline_blank' }}</v-icon>
        </div>
        <v-flex grow>
          {{ item.title }}
        </v-flex>
      </v-layout>

      <CommentsForEntry
        v-if="appSettings.module_comments == 1 && myRights['comments-personaldata']"
        class="mt-4"
        :foreignId="attempt.id"
        :type="$constants.MORPH_TYPES.TYPE_COURSE_CONTENT_ATTEMPT"
      />
    </div>
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import CommentsForEntry from "../../partials/comments/CommentsForEntry.vue"

export default {
  props: ['course', 'participation'],
  data() {
    return {
      isLoading: true,
      answers: null,
      todolist: null,
      attempt: null,
    }
  },
  created() {
    this.loadData()
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
      myRights: 'app/myRights',
    }),
    itemStatus() {
      if(this.todolist === null || this.answers === null) {
        return {}
      }
      const itemStatus = {}
      this.todolist.todolist_items.forEach((item) => {
        const answer = this.answers.find(answer => answer.todolist_item_id === item.id)
        if(!answer) {
          itemStatus[item.id] = false
        } else {
          itemStatus[item.id] = answer.is_done
        }
      })
      return itemStatus
    }
  },
  methods: {
    loadData() {
      this.isLoading = true
      axios.get(`/backend/api/v1/courses/${this.course.id}/participations/${this.$route.params.participationId}/todolist-status/${this.$route.params.contentId}`).then(response => {
        this.answers = response.data.answers
        this.todolist = response.data.todolist
        this.attempt = response.data.attempt
      }).catch(() => {
        alert('Die Daten für diesen User konnten leider nicht geladen werden. Bitte versuchen Sie es später erneut.')
      })
      .finally(() => {
        this.isLoading = false
      })
    }
  },
  components: {
    CommentsForEntry,
  },
}
</script>

<style lang="scss" scoped>
.s-iconWrapper {
  width: 45px;
  overflow: hidden;
  height: 28px;
}
</style>
