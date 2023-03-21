<template>
  <div>
    <v-snackbar
      :color="snackbar.type"
      :top="true"
      v-model="snackbar.active"
    >
      {{ snackbar.message }}
    </v-snackbar>
    <test-editor-settings
      :quiz-teams="quizTeams"
      :placeholder-minutes="placeholderMinutes"
      :tags="tags"
      :test="test"
      @message="handleSnackbar($event.type, $event.message)"
    />
    <test-editor-questions
      v-if="test.mode == $constants.TEST.MODE_QUESTIONS"
      :test="test"
      @message="handleSnackbar($event.type, $event.message)"
      @update="questionCount = $event"
    />
    <test-editor-categories
      v-if="test.mode == $constants.TEST.MODE_CATEGORIES"
      :test="test"
      @message="handleSnackbar($event.type, $event.message)"
      @update="questionCount = $event"
    />
  </div>
</template>

<script>
import TestEditorCategories from './tests/TestEditorCategories'
import TestEditorQuestions from './tests/TestEditorQuestions'
import TestEditorSettings from './tests/TestEditorSettings'

export default {
  props: {
    quizTeams: {
      type: Array
    },
    tags: {
      type: Array
    },
    test: {
      type: Object
    }
  },
  data() {
    return {
      questionCount: 0,
      snackbar: {
        active: false,
        type: null,
        message: null,
      },
    }
  },
  computed: {
    placeholderMinutes() {
      let minutes = Math.ceil(this.questionCount / 2);
      return Math.max(2, minutes)
    },
  },
  methods: {
    handleSnackbar(type, message) {
      this.snackbar.active = true
      this.snackbar.type = type
      this.snackbar.message = message
    },
  },
  components: {
    TestEditorCategories,
    TestEditorQuestions,
    TestEditorSettings,
  },
};
</script>
