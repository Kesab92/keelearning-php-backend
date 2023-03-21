<template>
  <v-dialog
    v-model="dialogOpen"
    width="500"
  >
    <v-btn
      slot="activator"
    >
      Vorschau
    </v-btn>

    <v-card>
      <v-card-title
        class="headline"
        :class="{
          'grey': !!questionTitle,
          'lighten-2': !!questionTitle,
          'red': !questionTitle,
          'white--text': !questionTitle
        }"
        primary-title
      >
        <template v-if="!questionTitle">
          Spalte "Frage" nicht ausgewählt
        </template>
        <template v-else>
          {{ questionTitle }}
        </template>
      </v-card-title>

      <v-list>
        <template v-for="(answer, idx) in answers">
          <v-divider
            v-if="idx > 0"
            :key="answer.content + '-divider'" />
          <v-list-tile
            avatar
            :key="answer.content"
          >
            <v-list-tile-avatar>
              <v-icon
                large
                color="green"
                v-if="answer.correct">check</v-icon>
              <v-icon
                large
                color="red"
                v-else>clear</v-icon>
            </v-list-tile-avatar>

            <v-list-tile-content>
              <v-list-tile-title>{{ answer.content }}</v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </template>
      </v-list>

      <v-divider/>

      <v-card-actions>
        <v-spacer/>
        <v-btn
          color="primary"
          flat
          @click="dialogOpen = false"
        >
          Vorschau schließen
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
  export default {
    props: ['question', 'headers', 'availableHeaders', 'type'],
    data() {
      return {
        dialogOpen: false,
      }
    },
    computed: {
      questionTitle() {
        let titleIndex = null
        this.headers.forEach((header, idx) => {
          if(header === 'question') {
            titleIndex = idx
          }
        })
        if(titleIndex === null) {
          return null
        }
        return this.question[titleIndex]
      },
      answers() {
        if(this.type === 'singlechoice') {
          return this.singleChoiceAnswers
        }
        if(this.type === 'multiplechoice') {
          return this.multipleChoiceAnswers
        }
        if(this.type === 'boolean') {
          return this.booleanAnswers
        }

        return []
      },
      singleChoiceAnswers() {
        let questions = []
        this.headers.forEach((header, idx) => {
          if(!header) {
            return
          }
          if(header === 'correct_answer') {
            questions.push({
              correct: true,
              content: this.question[idx]
            })
          }
          if(header.substr(0, 'incorrect_answer'.length) === 'incorrect_answer' && this.question[idx]) {
            questions.push({
              correct: false,
              content: this.question[idx]
            })
          }
        })
        return questions
      },
      multipleChoiceAnswers() {
        let questions = []
        this.headers.forEach((header, idx) => {
          if(!header) {
            return
          }
          // Check if this column is an answer content column
          if(header.substr(0, 6) === 'answer' && header.substr(-8) !== '_correct') {
            // Find the index of the column which shows if the current column is correct or incorrect
            let correctIdx = null
            this.headers.forEach((correctHeader, idx) => {
              if(correctHeader === header + '_correct') {
                correctIdx = idx
              }
            })
            if(correctIdx !== null && this.question[idx]) {
              questions.push({
                correct: !!parseInt(this.question[correctIdx]),
                content: this.question[idx],
              })
            }
          }
        })
        return questions
      },
      booleanAnswers() {
        let questions = []
        this.headers.forEach((header, idx) => {
          if(header === 'correct_answer') {
            questions.push({
              correct: true,
              content: this.question[idx]
            })
          }
          if(header === 'incorrect_answer') {
            questions.push({
              correct: false,
              content: this.question[idx]
            })
          }
        })
        return questions
      }
    }
  }
</script>
