<template>
  <div v-if="questionData">
    <QuestionToolbar
      :question-data="questionData"
      :is-saving="isSaving"
      @save="save"
      @updateVisibility="updateVisibility"
    />
    <div class="pa-4">
      <v-layout row>
        <v-flex xs6>
          <translated-input
            v-model="questionData.title"
            input-type="textarea"
            :translations="questionData.translations"
            attribute="title"
            :maxlength="$constants.QUESTIONS.MAX_LENGTHS.TITLE"
            label="Frage"
            class="mb-2"
            style="z-index: 2"/>
          <ContentLengthProgress
            :length="questionData.title ? questionData.title.length : 0"
            :max-length="$constants.QUESTIONS.MAX_LENGTHS.TITLE"
            class="mt-0 mb-4"
            style="z-index: 3"
          />
        </v-flex>
        <v-flex xs6 class="ml-3">
          <CategorySelect
            v-model="questionData.category_id"
            outline
            label="Kategorie"
            limit-to-tag-rights
            show-limited-categories
            :clearable="false"
          />
          <MediaSelector
            v-if="attachment !== null"
            v-model="attachment"
            :upload-url="`/backend/api/v1/questions/${questionData.id}/upload-attachment`"
            :enable-link="false"
            :enable-wbt="false"
            :enable-watermark="false"
            @reset="deleteAttachment"
            @update="updateAttachment"/>
        </v-flex>
      </v-layout>
      <h4
        v-if="questionData.type === this.$constants.QUESTIONS.TYPE_INDEX_CARD"
        class="sectionHeader">
        Rückseite
      </h4>
      <h4
        v-else
        class="sectionHeader">
        Antworten
      </h4>
      <SingleChoiceAnswer
        v-if="questionData.type === this.$constants.QUESTIONS.TYPE_SINGLE_CHOICE"
        :answers.sync="questionData.question_answers"
      />
      <MultipleChoiceAnswer
        v-else-if="questionData.type === this.$constants.QUESTIONS.TYPE_MULTIPLE_CHOICE"
        :answers.sync="questionData.question_answers"
      />
      <BooleanAnswer
        v-else-if="questionData.type === this.$constants.QUESTIONS.TYPE_BOOLEAN"
        :answers.sync="questionData.question_answers"
      />
      <IndexCardAnswer
        v-else-if="questionData.type === this.$constants.QUESTIONS.TYPE_INDEX_CARD"
        :answers.sync="questionData.question_answers"
      />
    </div>
  </div>
</template>

<script>
import QuestionToolbar from "./QuestionToolbar"
import CategorySelect from "../partials/global/CategorySelect"
import SingleChoiceAnswer from "./components/SingleChoiceAnswer"
import MultipleChoiceAnswer from "./components/MultipleChoiceAnswer"
import BooleanAnswer from "./components/BooleanAnswer"
import IndexCardAnswer from "./components/IndexCardAnswer"
import MediaSelector from "../partials/global/media-selector/MediaSelector"
import ContentLengthProgress from "./components/ContentLengthProgress"

export default {
  props: ["question"],
  data() {
    return {
      questionData: null,
      attachment: null,
      isSaving: false,
    }
  },
  watch: {
    question: {
      handler() {
        this.questionData = JSON.parse(JSON.stringify(this.question))
      },
      immediate: true,
    },
    questionData: {
      handler() {
        if(!this.questionData.attachments.length) {
          this.attachment = {
            file: null,
            url: null,
            type: null,
            download_disabled: false,
            show_watermark: false,
            file_size_kb: null,
            link: null,
          }
          return
        }
        let type = null
        let link = null

        switch(this.questionData.attachments[0].type) {
          case this.$constants.QUESTIONS.ATTACHMENTS.TYPE_IMAGE:
            type = 'image'
            break
          case this.$constants.QUESTIONS.ATTACHMENTS.TYPE_AUDIO:
            type = 'audio'
            break
          case this.$constants.QUESTIONS.ATTACHMENTS.TYPE_YOUTUBE:
            type = 'youtube'
            link = this.questionData.attachments[0].attachment
            break
          case this.$constants.QUESTIONS.ATTACHMENTS.TYPE_AZURE_VIDEO:
            type = 'azure_video'
            break
        }

        this.attachment = {
          file: this.questionData.attachments[0].attachment,
          url: this.questionData.attachments[0].attachment_url,
          type: type,
          download_disabled: false,
          show_watermark: false,
          file_size_kb: null,
          link: link,
        }
      },
      immediate: true,
    },
  },
  methods: {
    async save() {
      if (this.isSaving) {
        return
      }

      const emptyAnswers = this.questionData.question_answers.filter(answer => {
        if(!answer.id) {
          return false
        }
        return answer.content.length === 0
      })

      if(emptyAnswers.length) {
        alert('Bestehende Antworten können nicht gelöscht werden')
        return
      }

      this.isSaving = true
      await this.$store.dispatch("questions/saveQuestion", {
        id: this.questionData.id,
        title: this.questionData.title,
        latex: this.questionData.latex,
        visible: this.questionData.visible,
        category_id: this.questionData.category_id,
        question_answers: this.questionData.question_answers,
        attachment: this.attachment,
      }).catch(() => {
        alert('Die Frage konnte leider nicht gespeichert werden')
      }).finally(() => {
        this.isSaving = false
      })
    },
    updateVisibility(visible) {
      this.questionData.visible = visible
    },
    updateAttachment() {
      this.$store.dispatch('questions/loadQuestion', { questionId: this.questionData.id })
    },
    async deleteAttachment() {
      await this.$store.dispatch('questions/removeQuestionAttachment', {questionId: this.questionData.id})
    },
  },
  components: {
    QuestionToolbar,
    CategorySelect,
    SingleChoiceAnswer,
    MultipleChoiceAnswer,
    BooleanAnswer,
    IndexCardAnswer,
    MediaSelector,
    ContentLengthProgress,
  },
}
</script>
