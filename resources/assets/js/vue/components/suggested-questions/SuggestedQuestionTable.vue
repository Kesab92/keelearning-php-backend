<template>
  <div>
    <v-data-table
      :headers="headers"
      :items="suggestedQuestions"
      :loading="isLoading"
      :pagination.sync="pagination"
      :rows-per-page-items="[50, 100, 200]"
      :total-items="suggestedQuestionsCount"
      class="elevation-1"
      item-key="id">
      <tr
        @click="editSuggestedQuestion(props.item.id)"
        class="clickable"
        slot="items"
        slot-scope="props">
        <td>
          {{ props.item.title }}
        </td>
        <td v-if="showPersonalData('suggestedquestions')">
          <span v-if="props.item.user">
            {{ props.item.user.username }}
          </span>
        </td>
      </tr>
    </v-data-table>
  </div>
</template>

<script>
import {mapGetters} from "vuex";

export default {
  watch: {
    pagination: {
      handler() {
        this.loadData()
      },
      deep: true,
    },
  },
  computed: {
    ...mapGetters({
      suggestedQuestionsCount: 'suggestedQuestions/suggestedQuestionsCount',
      suggestedQuestions: 'suggestedQuestions/suggestedQuestions',
      isLoading: 'suggestedQuestions/listIsLoading',
      showPersonalData: 'app/showPersonalData',
    }),
    headers() {
      let headers = [
        {
          text: 'Frage',
          value: 'title',
          sortable: false,
        },
      ]
      if (this.showPersonalData('suggestedquestions')) {
        headers.push({
          text: 'Benutzer',
          value: 'user.username',
          sortable: false,
        })
      }
      return headers
    },
    pagination: {
      get() {
        return this.$store.state.suggestedQuestions.pagination
      },
      set(data) {
        this.$store.commit('suggestedQuestions/setPagination', data)
      },
    },
  },
  methods: {
    editSuggestedQuestion(suggestedQuestionId) {
      this.$router.push({
        name: 'suggested-questions.edit.general',
        params: {
          suggestedQuestionId: suggestedQuestionId,
        },
      })
    },
    loadData() {
      this.$store.dispatch('suggestedQuestions/loadSuggestedQuestions')
    },
  },
}
</script>
