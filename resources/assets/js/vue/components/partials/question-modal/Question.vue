<template>
  <v-card-text>
    <v-text-field
      v-model="question.title"
      label="Frage"
      :rules="[v => !!v || 'Bitte geben Sie eine Frage ein']"
    />
    <v-autocomplete
      :items="categorySelect"
      v-model="question.category"
      label="Kategorie"
      :rules="[v => !!v || 'Bitte wählen Sie eine Kategorie aus']"
      menu-props="auto"
    />
    <v-select
      :items="questionTypes"
      v-model="question.type"
      label="Fragentyp"
      :rules="[v => v !== null || 'Bitte wählen Sie einen Typ aus']"
      menu-props="auto"
    />
    <v-divider/>

    <template
      v-for="(answer, index) in question.answers"
      v-if="question.type === 0"
    >
      <v-layout
        align-center
        :key="'a-' + index">
        <v-checkbox
          :input-value="index === 0"
          hide-details
          disabled
          class="shrink mr-2"
          :color="index === 0 ? 'success' : 'error'"
        />
        <v-text-field
          :label="index === 0 ? 'Richtige Antwort' : 'Falsche Antwort #' + (index)"
          v-model="answer.content"
          :rules="index > 2 ? [] : [v => !!v || 'Antwort wird benötigt']"
        />
        <v-icon
          @click="removeAnswer(index)"
          class="remove-answer"
          v-if="index > 2">delete
        </v-icon>
      </v-layout>
      <v-layout
        class="feedback-wrapper"
        align-center
        :key="'f-' + index"
        v-show="answer.content && answer.content.length && index === 0">
        <v-text-field
          label="Feedback (Optional)"
          v-model="answer.feedback"
        />
      </v-layout>
      <v-divider
        v-if="question.answers.length > index + 1"
        :key="'d-' + index"/>
    </template>

    <template
      v-if="question.type === 1"
      v-for="(answer, index) in question.answers">
      <v-layout
        align-center
        :key="'a-' + index">
        <v-checkbox
          v-model="answer.correct"
          hide-details
          class="shrink mr-2"
          :color="answer.correct ? 'success' : 'error'"
        />
        <v-text-field
          :label="answer.correct ? 'Richtige Antwort' : 'Falsche Antwort'"
          v-model="answer.content"
          :rules="index > 2 ? [] : [v => !!v || 'Antwort wird benötigt']"
        />
        <v-icon
          @click="removeAnswer(index)"
          class="remove-answer"
          v-if="index > 2">delete
        </v-icon>
      </v-layout>
      <v-layout
        class="feedback-wrapper"
        align-center
        :key="'f-' + index"
        v-show="answer.content && answer.content.length && answer.correct">
        <v-text-field
          label="Feedback (Optional)"
          v-model="answer.feedback"
        />
      </v-layout>
      <v-divider
        v-if="question.answers.length > index + 1"
        :key="'d-' + index"/>
    </template>

    <template v-if="question.type === 2">
      <v-layout align-center>
        <v-checkbox
          :input-value="true"
          hide-details
          class="shrink mr-2"
          color="success"
        />
        <v-text-field
          label="Correct Answer"
          v-model="question.answers[0].content"
          :rules="[v => !!v || 'Antwort wird benötigt']"
        />
      </v-layout>
      <v-layout
        class="feedback-wrapper"
        align-center
        v-show="question.answers[0].content && question.answers[0].content.length">
        <v-text-field
          label="Feedback (Optional)"
          v-model="question.answers[0].feedback"
        />
      </v-layout>
      <v-divider/>
      <v-layout align-center>
        <v-checkbox
          :input-value="false"
          hide-details
          class="shrink mr-2"
          color="error"
        />
        <v-text-field
          label="Wrong Answer"
          v-model="question.answers[1].content"
          :rules="[v => !!v || 'Antwort wird benötigt']"
        />
      </v-layout>
    </template>
    <v-btn
      v-if="question.type !== null && question.type !== 2 && question.answers.length < 6"
      flat
      small
      color="primary"
      @click.native="addAnswer"
    >
      Zusätzliche Antwort hinzufügen
    </v-btn>
  </v-card-text>
</template>

<script>
  import questionTypes from "../../../logic/questionTypes"

  export default {
    props: ["categories", "question", "settings"],
    data() {
      return {
        questionTypes: questionTypes,
      }
    },
    methods: {
      addAnswer() {
        this.question.answers.push({})
      },
      removeAnswer(index) {
        this.question.answers.splice(index, 1)
      },
    },
    computed: {
      categorySelect() {
        let categories = []
        for (let i = 0; i < this.categories.length; i++) {
          categories.push({
            text: this.categories[i].name,
            value: this.categories[i].id,
          })
        }
        return categories
      },

    },
  }
</script>

<style>
  .remove-answer {
    cursor: pointer;
  }
</style>
