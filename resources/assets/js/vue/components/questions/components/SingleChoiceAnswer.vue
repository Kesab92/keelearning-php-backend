<template>
  <div>
    <v-layout row
      v-for="(answer, index) in answers"
      :key="`answer-${answer.id}`"
    >
      <v-flex shrink class="mr-2 mt-2">
        <AnswerOrderBox
          v-if="answer.correct"
          class="green">
          <v-icon color="white">done</v-icon>
        </AnswerOrderBox>
        <AnswerOrderBox
          v-else
          class="grey lighten-2">
          {{ String.fromCharCode(65 + index) }}
        </AnswerOrderBox>
      </v-flex>
      <v-flex grow>
        <AnswerContent
          v-model="answer.content"
          :translations="answer.translations"
          class="mb-4"
        />
        <AnswerFeedback
          v-if="answer.correct"
          v-model="answer.feedback"
          :translations="answer.translations"
          class="mb-4"
        />
      </v-flex>
    </v-layout>
    <v-btn @click="addNewAnswer">
      <v-icon
        left
        dark>add
      </v-icon>
      Antwort hinzufügen
    </v-btn>
  </div>
</template>

<script>
import answersMixin from "./answersMixin"
import AnswerOrderBox from "./AnswerOrderBox"

export default {
  components: {AnswerOrderBox},
  mixins: [answersMixin]
}
</script>
