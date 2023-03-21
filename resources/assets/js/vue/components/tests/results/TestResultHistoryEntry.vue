<template>
    <div
        @click.self="visible = !visible"
        class="clickable">
        <template v-if="historyEntry.type == 2">
            <v-icon color="success" v-if="historyEntry.meta && historyEntry.meta.passed">done</v-icon>
            <v-icon color="error" v-else>close</v-icon>
            Bearbeitung des Tests am {{ historyEntry.date | date }} ({{historyEntry.meta.result_percentage }}%)
            <v-icon small v-if="visible">keyboard_arrow_up</v-icon>
            <v-icon small v-else>keyboard_arrow_down</v-icon>
            <div
                class="questions"
                v-show="historyEntry.meta && historyEntry.meta.questions && visible">
                <div>
                    <strong>Fragen</strong>
                </div>
                <div
                    :key="question.question_id"
                    v-for="question in historyEntry.meta.questions">
                    <v-icon
                        color="success"
                        v-if="question.correct">
                        done
                    </v-icon>
                    <v-icon
                        color="error"
                        v-else>
                        close
                    </v-icon>
                    {{ questionTitle(question.question_id) }}
                </div>
                <div class="result">
                    Gesamtergebnis:
                    {{ historyEntry.meta.passed ? 'bestanden' : 'nicht bestanden' }}
                    ({{ historyEntry.meta.result_percentage }}%)
                </div>
            </div>
        </template>

        <template v-if="historyEntry.type == 1">
            <v-icon>notification_important</v-icon>
            Benachrichtigung gesendet am {{ historyEntry.date | date }}
        </template>
    </div>
</template>

<script>
    export default {
      props: {
        historyEntry: {
          type: Object,
          required: true
        },
        questions: {
          type: Array,
          required: true
        }
      },
      data() {
        return {
          visible: false
        }
      },
      methods: {
        questionTitle(questionId) {
          let question = this.questions.find(question => question.id == questionId)
          if (question) {
            return question.title
          }
        }
      }
    }
</script>

<style lang="scss" scoped>
    #app .questions {
        padding: 15px 30px;
        background: rgba(0, 0, 0, 0.08);

        .result {
            margin-top: 10px;
            font-weight: bold;
        }
    }

    #app .clickable {
        cursor: pointer;
        padding: 3px 0;
        line-height: 20px;
    }
</style>