<template>
  <div v-if="questionData">
    <QuestionToolbar
      :question-data="questionData"
      :is-saving="isSaving"
      @save="save"
      @updateVisibility="updateVisibility"
    />
    <div class="pa-4">
      <v-text-field
        v-model.number="questionData.answertime"
        type="number"
        min="1"
        label="Antwortzeit"
        placeholder="15"
        hint="Sekunden"
        persistent-hint
        outline />
      </div>

  </div>
</template>

<script>
import {mapGetters} from "vuex"
import QuestionToolbar from "./QuestionToolbar"

export default {
  props: ["question"],
  data() {
    return {
      questionData: null,
      isSaving: false,
    }
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
    }),
  },
  watch: {
    question: {
      handler() {
        this.questionData = JSON.parse(JSON.stringify(this.question))
      },
      immediate: true,
    },
  },
  methods: {
    async save() {
      if (this.isSaving) {
        return
      }
      this.isSaving = true
      await this.$store.dispatch("questions/saveQuestion", {
        id: this.questionData.id,
        category_id: this.questionData.category_id,
        answertime: this.questionData.answertime,
        visible: this.questionData.visible,
      }).catch(() => {
        alert('Die Frage konnte leider nicht gespeichert werden')
      }).finally(() => {
        this.isSaving = false
      })
    },
    updateVisibility(visible) {
      this.questionData.visible = visible
    },
  },
  components: {
    QuestionToolbar,
  },
}
</script>
