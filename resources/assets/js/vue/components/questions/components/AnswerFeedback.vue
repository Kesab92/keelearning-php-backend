<template>
  <div>
    <translated-input
      v-model="feedback"
      :translations="translations"
      attribute="feedback"
      class="mb-2"
      input-type="textarea"
      label="Feedback"
      placeholder="Optionales Feedback um den Kontext der gestellten Frage nach der Auflösung in der App besser zu verstehen."
      :maxlength="$constants.QUESTIONS.MAX_LENGTHS.FEEDBACK"
      auto-grow
      rows="3"
      style="z-index: 1"
    />
    <ContentLengthProgress
      :length="feedback ? feedback.length : 0"
      :max-length="$constants.QUESTIONS.MAX_LENGTHS.FEEDBACK"
      class="mt-0 mb-4"
      style="z-index: 3"
    />
  </div>
</template>

<script>
import ContentLengthProgress from "./ContentLengthProgress"
export default {
  props: ['value', 'translations'],
  data () {
    return {
      feedback: null,
    }
  },
  watch: {
    value: {
      handler() {
        this.feedback = this.value
      },
      immediate: true,
    },
    feedback() {
      this.$emit('input', this.feedback)
    }
  },
  components: {
    ContentLengthProgress,
  }
}
</script>
