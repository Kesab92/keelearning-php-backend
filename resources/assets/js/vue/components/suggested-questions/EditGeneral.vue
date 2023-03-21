<template>
  <div v-if="suggestedQuestion">
    <SuggestedQuestionToolbar
      :suggested-question="suggestedQuestion"
    />
    <div class="pa-4">
      <h3>{{ suggestedQuestion.title }}</h3>
      <p v-if="suggestedQuestion.user">
        Frage von {{ suggestedQuestion.user.username }} (#{{ suggestedQuestion.user.id }})
      </p>
      <p v-if="suggestedQuestion.category">
        Kategorie: {{ suggestedQuestion.category.name }}
      </p>
      <div v-for="(answer, index) in suggestedQuestion['question_answers']"
           :key="`question_answer-${answer.id}`"
           class="my-3">
        <v-avatar
          v-if="answer.correct"
          size="40"
          color="green">
          <v-icon
            color="white">
            check
          </v-icon>
        </v-avatar>
        <v-avatar
          v-else
          size="40"
          color="grey lighten-4">
          <span class="dark--text headline">{{ letters[index] }}</span>
        </v-avatar>
        <span class="ml-2">
            {{ answer.content }}
          </span>
      </div>
    </div>
  </div>
</template>

<script>
import SuggestedQuestionToolbar from "./SuggestedQuestionToolbar"

export default {
  props: ["suggestedQuestion"],
  data() {
    return {
      letters: ['A', 'B', 'C', 'D']
    }
  },
  components: {
    SuggestedQuestionToolbar,
  },
}
</script>
