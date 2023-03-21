<template>
  <div>
    <v-layout
      row
      class="mb-4">
      <v-flex
        grow>
        <translated-input
          v-model="value.title"
          :translations="value.translations"
          attribute="title"
          label="Name vom Kursinhalt"
          hide-details
          class="mb-3"
          :readOnly="isReadonly"/>
        <tag-select
          v-model="value.tags"
          label="Sichtbar für folgende User"
          placeholder="Alle"
          class="mt-section"
          limit-to-tag-rights
          :disabled="isReadonly"
          multiple />
      </v-flex>
    </v-layout>

    <div
      class="elevation-1 px-4 py-3">
      <v-layout row>
        <v-flex shrink>
          <v-switch
            class="s-visibilitySwitch"
            hide-details
            height="30"
            :disabled="isReadonly"
            v-model="value.is_test" />
        </v-flex>
        <v-flex align-self-center>
          Prüfungsmodus
        </v-flex>
      </v-layout>
      <template v-if="value.is_test">
        <div class="mt-1 mb-2 caption">
          Ein Kurs mit Prüfung gilt nur als bestanden, wenn alle Prüfungen des Kurses bestanden wurden. Der Prüfungsmodus garantiert, dass ein Benutzer im Kurs nur weiter kommt, wenn er die Bestehensgrenze der Prüfung erreicht hat.
        </div>
        <v-layout
          row
          style="align-items: stretch">
          <v-flex xs2>
            <v-text-field
              v-model="value.pass_percentage"
              type="number"
              min="0"
              max="100"
              :disabled="isReadonly"
              label="Bestehensgrenze"
              suffix="%" />
          </v-flex>
          <v-flex
            xs10
            class="ml-4">
            <v-layout
              row
              align-center
              style="height: 100%">
              <v-flex xs4>
                <v-layout
                  row
                  align-center>
                  <v-flex shrink>
                    <v-switch
                      class="s-visibilitySwitch"
                      hide-details
                      height="30"
                      :disabled="isReadonly"
                      v-model="isRepeatable" />
                  </v-flex>
                  <v-flex align-self-center>
                    Wiederholbar
                  </v-flex>
                </v-layout>
              </v-flex>
              <v-flex
                xs4
                v-if="isRepeatable && !isEndlesslyRepeatable">
                <v-layout row>
                  <v-text-field
                    v-model="value.repetitions"
                    type="number"
                    min="0"
                    :disabled="isEndlesslyRepeatable || isReadonly"
                    label="Maximale Versuche" />
                </v-layout>
              </v-flex>
              <v-flex
                xs4
                v-if="isRepeatable">
                <v-layout
                  row
                  class="ml-4">
                  <v-flex shrink>
                    <v-switch
                      class="s-visibilitySwitch"
                      hide-details
                      height="30"
                      :disabled="isReadonly"
                      v-model="isEndlesslyRepeatable" />
                  </v-flex>
                  <v-flex align-self-center>
                    Beliebig viele Versuche
                  </v-flex>
                </v-layout>
              </v-flex>
            </v-layout>
          </v-flex>
        </v-layout>
      </template>

      <v-layout
        row
        class="mt-4">
        <v-flex shrink>
          <v-switch
            class="s-visibilitySwitch"
            hide-details
            height="30"
            :disabled="isReadonly"
            v-model="value.show_correct_result" />
        </v-flex>
        <v-flex align-self-center>
          Korrekte Antworten nach dem Beantworten anzeigen
        </v-flex>
      </v-layout>
    </div>

    <QuestionsList
      class="mt-4"
      :attachments="value.attachments"
      @remove="removeQuestion"
      @add="addQuestions" />
    <v-text-field
      v-model="value.duration"
      placeholder="1"
      label="Geschätzte Lerndauer"
      :disabled="isReadonly"
      :suffix="'Minute' + ((value.duration && value.duration != 1) ? 'n' : '')"
      class="mt-4" />
  </div>
</template>

<script>
import QuestionsList from "./partials/QuestionsList"
import TagSelect from "../../../partials/global/TagSelect"
import {mapGetters} from "vuex";

export default {
  props: [
    'course',
    'value',
  ],
  data() {
    return {
      isRepeatable: false,
    }
  },
  watch: {
    value: {
      handler() {
        if (this.value.repetitions != 1) {
          this.isRepeatable = true
        }
        this.$emit('input', this.value)
      },
      immediate: true,
    },
    isRepeatable() {
      if(!this.isRepeatable) {
        this.value.repetitions = 1
      }
    },
    'value.is_test': {
      handler() {
        if(this.value.is_test && !this.value.pass_percentage) {
          this.value.pass_percentage = 80
        }
      }
    }
  },
  methods: {
    removeQuestion(attachment) {
      const idx = this.value.attachments.findIndex(entry => entry.id === attachment.id)
      if(idx === -1) {
        return
      }
      this.value.attachments.splice(idx, 1)
    },
    addQuestions(questions) {
      questions.filter(question => {
        return !this.value.attachments.some(attachment => attachment.question.id == question.id)
      }).forEach(question => {
        let position = this.value.attachments.length + 1
        this.value.attachments.push({
          id: -1 * position, // Set a unique id which is negative, so the api knows to create this attachment
          position: position,
          question: {
            id: question.id,
            title: question.title,
            category: question.category,
            type: question.type,
          }
        })
      })
    },
    save() {
      if(this.value.visible && !this.value.attachments.length) {
        alert('Bitte wählen Sie mindestens eine Frage, bevor Sie den Kursinhalt veröffentlichen.')
        return false
      }
      return axios.post(`/backend/api/v1/courses/${this.course.id}/content/${this.value.id}`, {
        attachments: this.value.attachments,
        duration: this.value.duration,
        is_test: this.value.is_test,
        pass_percentage: this.value.pass_percentage,
        repetitions: this.value.repetitions,
        show_correct_result: this.value.show_correct_result,
        tags: this.value.tags,
        title: this.value.title,
        visible: this.value.visible,
      })
    },
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    isEndlesslyRepeatable: {
      get() {
        return this.value.repetitions === null
      },
      set(value) {
        if(value) {
          this.value.repetitions = null
        } else {
          this.value.repetitions = 3
        }
      }
    },
    isReadonly() {
      return !this.myRights['courses-edit']
    },
  },
  components: {
    QuestionsList,
    TagSelect,
  },
}
</script>


<style lang="scss" scoped>
#app .s-visibilitySwitch {
  margin-top: 0;
  padding-top: 0;
}
</style>
