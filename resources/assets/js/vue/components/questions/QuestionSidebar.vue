<template>
  <details-sidebar
    :root-url="{
      name: 'questions.index',
    }"
    :drawer-open="typeof $route.params.questionId !== 'undefined'"
    data-action="questions/loadQuestion"
    :data-getter="(params) => $store.getters['questions/question'](params.questionId)"
    :data-params="{questionId: $route.params.questionId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: question, refresh }">
      <router-view
        :question="question"
        @refresh="refresh"
      />
    </template>
    <template v-slot:headerTitle="{ data: question }">
      {{ question.title }}
    </template>
    <template v-slot:headerExtension="{ data: question }">
      Fragentyp: {{ questionTypeLabel(question) }}<br>
      Fehlende Übersetzungen: {{ missingTranslations(question) }}<br>
      <template v-if="question.answertime">
        Antwortzeit: {{ question.answertime }} Sekunden<br>
      </template>
      Schwierigkeit:
      <template v-if="difficulty(question) >= 75">
        😈
      </template>
      <template v-if="difficulty(question) <= 33">
        😇
      </template>
      {{ difficulty(question) }} <template v-if="difficulty(question) !== '?'">%</template><br>
      Erstellt am {{ question.created_at | date }}
    </template>

  </details-sidebar>
</template>

<script>
import helpers from "../../logic/helpers"

export default {
  methods: {
    questionTypeLabel(question) {
      switch (question.type) {
        case this.$constants.QUESTIONS.TYPE_SINGLE_CHOICE:
          return 'Single Choice'
        case this.$constants.QUESTIONS.TYPE_MULTIPLE_CHOICE:
          return 'Multiple Choice'
        case this.$constants.QUESTIONS.TYPE_BOOLEAN:
          return 'Richtig / Falsch'
        case this.$constants.QUESTIONS.TYPE_INDEX_CARD:
          return 'Lernkarte'
      }

      return null
    },
    missingTranslations(question) {
      if(!question.missingTranslations.length) {
        return '-'
      }
      return question.missingTranslations.join(', ').toUpperCase()
    },
    difficulty(question) {
      return helpers.humanReadableQuestionsDifficulty(question.difficulty)
    },
    getLinks(question) {
      return [
        {
          label: 'Allgemein',
          to: {
            name: 'questions.edit.general',
            params: {
              questionId: question.id,
            },
          },
        },
        {
          label: 'Einstellungen',
          to: {
            name: 'questions.edit.settings',
            params: {
              questionId: question.id,
            },
          },
        },
      ]
    }
  }
}
</script>
