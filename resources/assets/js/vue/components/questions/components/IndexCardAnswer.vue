<template>
  <div>
    <div
      v-for="(answer, index) in answers"
      :key="`answer-${answer.id}`"
    >
      <AnswerContent
        v-model="answer.content"
        :translations="answer.translations"
        :input-max-length="$constants.QUESTIONS.MAX_LENGTHS.INDEX_CARD_ANSWER"
        :progress-bar-max-length="$constants.QUESTIONS.MAX_LENGTHS.INDEX_CARD_ANSWER"
      />
    </div>
  </div>
</template>

<script>
import answersMixin from "./answersMixin"
export default {
  mixins: [answersMixin]
}
</script>
