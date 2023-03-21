import colors from "vuetify/es5/util/colors"
import AnswerContent from "./AnswerContent"
import AnswerFeedback from "./AnswerFeedback"
import BooleanChoiceButtons from "./BooleanChoiceButtons"
export default {
  props: {
    answers: {
      type: Array,
      required: true,
    }
  },
  computed: {
    feedbackInputColor()  {
      return colors.grey.lighten3
    }
  },
  methods: {
    addNewAnswer() {
      this.answers.push({
        content: null,
        feedback: null,
        correct: 0,
        id: null,
        translations: [],
      })
    },
    removeAnswer(deletedAnswerIndex) {
      const answers = this.answers.filter((answer, index) => {
        if(deletedAnswerIndex === index && answer.id === null) {
          return false
        }
        return true
      })
      this.$emit('update:answers', answers)
    }
  },
  components: {
    AnswerContent,
    AnswerFeedback,
    BooleanChoiceButtons,
  }
}
