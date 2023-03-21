<template>
  <v-card class="mt-4">
    <v-dialog
      v-model="deletionDialog"
      max-width="290"
    >
      <v-card>
        <v-card-text>
          Soll diese Frage entfernt werden?
        </v-card-text>

        <v-card-actions>
          <v-spacer/>

          <v-btn
            color="red"
            flat="flat"
            @click="deleteTestQuestion"
          >
            Entfernen
          </v-btn>

          <v-btn
            color="gray"
            flat="flat"
            @click="deletionDialog = false"
          >
            Abbrechen
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-toolbar>
      <v-toolbar-title>
        Fragen
      </v-toolbar-title>
      <v-spacer />
      <SearchQuestionsModal
        v-if="!isReadonly"
        @add="addQuestions"
        @error="$emit('message', {type: 'error', message: $event})"
      >
        <v-btn
          color="primary"
          flat
        >
          Fragen hinzufügen
          <v-icon right>
            add
          </v-icon>
        </v-btn>
      </SearchQuestionsModal>
    </v-toolbar>

    <v-card-text>
      <v-list two-line>
        <v-list-tile
          v-if="!testQuestions.length"
          class="grey--text"
        >
          Noch keine Fragen ausgewählt.
        </v-list-tile>
        <draggable
          v-model="testQuestions"
          :animation="300"
          :disabled="isReadonly"
          :class="{
            'draggable': !isReadonly,
          }"
          ghost-class="ghost"
          drag-class="dragging"
          handle=".v-list__tile__content"
          @start="isDragging = true"
          @end="isDragging = false"
        >
          <v-list-tile
            v-for="(testQuestion, index) in testQuestions"
            :key="testQuestion.question_id"
            class="testquestion"
          >
            <v-list-tile-action v-if="!isReadonly">
              <v-btn
                v-if="!testQuestion.in_use"
                icon
                @click="askDeleteTestQuestion(index)"
              >
                <v-icon>
                  delete
                </v-icon>
              </v-btn>
              <v-tooltip
                v-else
                right>
                <v-btn
                  slot="activator"
                  disabled
                  icon
                >
                  <v-icon>
                    delete
                  </v-icon>
                </v-btn>
                <span>Frage wurde bereits beantwortet</span>
              </v-tooltip>
            </v-list-tile-action>

            <v-list-tile-content>
              <v-list-tile-title>
                {{ questions[testQuestion.question_id].title }}
              </v-list-tile-title>
              <v-list-tile-sub-title>
                {{ questions[testQuestion.question_id].category }}
              </v-list-tile-sub-title>
            </v-list-tile-content>

            <v-list-tile-action class="testquestion-action">
              <v-text-field
                label="Punkte"
                :placeholder="'' + (questions[testQuestion.question_id].points || 1)"
                :readonly="isReadonly"
                type="number"
                v-model="testQuestion.points"
              />
            </v-list-tile-action>
          </v-list-tile>
        </draggable>
      </v-list>
    </v-card-text>
    <v-card-actions v-if="!isReadonly">
      <v-btn
        color="primary"
        :disabled="isSaving"
        :loading="isSaving"
        @click="saveQuestions"
      >
        Fragen speichern
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import draggable from 'vuedraggable'
import {mapGetters} from 'vuex'
import SearchQuestionsModal from "../partials/questions/SearchQuestionsModal"

export default {
  props: {
    test: {
      type: Object,
    },
  },
  data() {
    return {
      deletionDialog: false,
      indexToDelete: null,
      isDragging: false,
      isSaving: false,
      questions: {}, // preloaded question data
      testQuestions: [], // selected questions
    }
  },
  mounted() {
    // parse the preloaded question data
    this.test.test_questions.forEach((testQuestion) => {
      if (testQuestion.question) {
        this.questions[testQuestion.question.id] = {
          id: testQuestion.question.id,
          title: testQuestion.question.title,
          category: testQuestion.question.category.name,
          points: testQuestion.question.category.points,
        }
      }
    })
    // fill the selected test questions
    let sortedTestQuestions = [...this.test.test_questions].sort((testQuestionA, testQuestionB) => {
      if (testQuestionA.position < testQuestionB.position) {
        return -1
      }
      if (testQuestionA.position > testQuestionB.position) {
        return 1
      }
      return 0
    })
    sortedTestQuestions.forEach((sortedTestQuestion) => {
      // remove entries w/o preloaded question data, since they have been deleted
      // usually this should not happen
      if (this.questions.hasOwnProperty(sortedTestQuestion.question_id)) {
        this.testQuestions.push({
          question_id: sortedTestQuestion.question_id,
          in_use: !!sortedTestQuestion.in_use, // already answered, can't be deleted
          points: sortedTestQuestion.points,
        })
      }
    })
  },
  watch: {
    testQuestions: {
      handler() {
        this.updateCount()
      },
      deep: true,
    },
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    isReadonly() {
      return !this.myRights['tests-edit']
    },
  },
  methods: {
    addQuestions(questionData) {
      questionData.forEach((questionDataEntry) => {
        if (!this.questions.hasOwnProperty(questionDataEntry.id)) {
          this.questions[questionDataEntry.id] = questionDataEntry
        }
        if (!this.testQuestions.some(tQ => tQ.question_id == questionDataEntry.id)) {
          this.testQuestions.push({
            question_id: questionDataEntry.id,
            in_use: false,
            points: null,
          })
        }
      })
    },
    askDeleteTestQuestion(index) {
      this.indexToDelete = index
      this.deletionDialog = true
    },
    deleteTestQuestion(index) {
      this.deletionDialog = false
      this.$delete(this.testQuestions, this.indexToDelete)
    },
    saveQuestions() {
      this.isSaving = true
      axios.post(`/backend/api/v1/tests/${this.test.id}/questions`, {
        questions: this.testQuestions,
      }).then(response => {
        if (response.data.success) {
          this.$emit('message', {
            type: 'success',
            message: 'Die Test-Fragen wurden gespeichert.',
          })
        } else {
          this.$emit('message', {
            type: 'error',
            message: response.data.error,
          })
        }
      }).catch(error => {
        this.$emit('message', {
          type: 'error',
          message: 'Ein unerwarteter Fehler ist aufgetreten.',
        })
      }).finally(() => {
        this.isSaving = false
      })
    },
    updateCount() {
      this.$emit('update', this.testQuestions.length)
    },
  },
  components: {
    draggable,
    SearchQuestionsModal,
  },
}
</script>

<style lang="scss" scoped>
.testquestion-action {
  width: 50px;
}

.testquestion {
  border-bottom: 1px solid rgba(0, 0, 0, 0.12);
  transition: border-bottom-color 0.3s ease;

  &:last-child {
    border-bottom-color: transparent;
  }

  .draggable & ::v-deep .v-list__tile__content {
    cursor: move;
  }
}

.ghost {
  border-bottom-color: rgba(0, 0, 0, 0.24); // offset the 0.5 opacity
  opacity: 0.5;
}

.dragging {
  border-bottom: 0;

  & ::v-deep .v-list__tile__action {
    opacity: 0;
  }
}
</style>
