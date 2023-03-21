<template>
  <div>
    <div
      v-for="(answer, index) in answers"
      :key="`answer-${answer.id}`"
    >
      <v-layout
        row
        justify-space-between
        class="mb-4"
      >
        <BooleanChoiceButtons
          v-model="answer.correct"
          class="elevation-0"
          />
        <v-btn
          v-if="!answer.id"
          flat
          icon
          color="red"
          class="ma-0"
          @click="removeAnswer(index)">
          <v-icon
            dark>delete</v-icon>
        </v-btn>
      </v-layout>
      <AnswerContent
        v-model="answer.content"
        :translations="answer.translations"
        class="mb-4"
      />
      <AnswerFeedback
        v-if="answer === answerWithVisibleFeedback"
        v-model="answer.feedback"
        :translations="answer.translations"
        class="mb-4"
      />
    </div>
    <v-btn @click="addNewAnswer">
      <v-icon
        left
        dark>add</v-icon>
      Antwort hinzufügen
    </v-btn>
  </div>
</template>

<script>
import answersMixin from "./answersMixin"
export default {
  mixins: [answersMixin],
  computed: {
    answerWithVisibleFeedback() {
      const correctAnswers = this.answers.filter(answer => {
        return answer.correct
      })
      if(correctAnswers.length) {
        return correctAnswers[0]
      }
      return null
    }
  },
}
</script>
