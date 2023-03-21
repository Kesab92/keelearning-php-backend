<template>
  <div>
    <translated-input
      v-model="content"
      :translations="translations"
      attribute="content"
      class="mb-2"
      input-type="textarea"
      label="Antwort"
      :maxlength="inputMaxLength"
      auto-grow
      rows="3"
      style="z-index: 2"
    />
    <ContentLengthProgress
      :length="content ? content.length : 0"
      :max-length="progressBarMaxLength"
      class="mt-0"
      style="z-index: 3"
    />
  </div>
</template>

<script>
import ContentLengthProgress from "./ContentLengthProgress"
import constants from "../../../logic/constants";
export default {
  props: {
    value: {
      type: String|null,
      required: true,
    },
    translations: {
      type: Array,
      required: true,
    },
    inputMaxLength: {
      type: Number,
      required: false,
      default: constants.QUESTIONS.MAX_LENGTHS.ANSWER,
    },
    progressBarMaxLength: {
      type: Number,
      required: false,
      default: 120,
    },
  },
  data () {
    return {
      content: null,
    }
  },
  watch: {
    value: {
      handler() {
        this.content = this.value
      },
      immediate: true,
    },
    content() {
      this.$emit('input', this.content)
    }
  },
  components: {
    ContentLengthProgress,
  }
}
</script>
