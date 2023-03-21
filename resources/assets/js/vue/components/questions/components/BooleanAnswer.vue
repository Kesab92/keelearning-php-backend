<template>
  <div>
    <div
      v-for="(answer, index) in answers"
      :key="`answer-${answer.id}`"
    >
      <AnswerOrderBox
        v-if="answer.correct"
        class="green">
        <v-icon color="white">done</v-icon>
      </AnswerOrderBox>
      <AnswerOrderBox
        v-else
        class="red">
        <v-icon color="white">close</v-icon>
      </AnswerOrderBox>
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
    </div>
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
