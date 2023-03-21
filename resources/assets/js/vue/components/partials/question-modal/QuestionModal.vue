<template>
  <div>
    <v-snackbar
      :timeout="6000"
      :top="true"
      color="error"
      v-model="snackbar"
    >
      {{ errorText }}
    </v-snackbar>
    <v-dialog
      v-model="dialog"
      max-width="500px"
      hide-overlay
      scrollable
      transition="slide-y-transition"
    >
      <div
        slot="activator"
      >
        <v-btn
          v-if="addMode"
          color="primary"
        >
          Frage hinzufügen
        </v-btn>
        <v-btn
          class="mx-0"
          v-if="editMode"
        >
          <v-icon color="green">edit</v-icon> Bearbeiten
        </v-btn>
      </div>
      <v-card
        style="height:100%"
      >
        <v-toolbar
          card
          dark
          color="primary">
          <v-btn
            icon
            dark
            @click.native="close">
            <v-icon>close</v-icon>
          </v-btn>
          <v-toolbar-title v-if="addMode">Frage einreichen</v-toolbar-title>
          <v-toolbar-title v-if="editMode">Frage bearbeiten</v-toolbar-title>
          <v-spacer/>
          <v-toolbar-items>
            <v-btn
              dark
              flat
              @click.native="save"
              :disabled="!valid || isLoading"
              :loading="isLoading"
            >
              Speichern
            </v-btn>
          </v-toolbar-items>
        </v-toolbar>
        <v-form
          ref="form"
          v-model="valid"
          lazy-validation>
          <v-tabs
            v-model="activeTab"
            dark
          >
            <v-tab
              key="question"
              ripple
            >
              Frage
            </v-tab>
            <v-tab-item
              key="question"
            >
              <Question
                :question.sync="question"
                :categories="categories"
              />
            </v-tab-item>
            <v-tab
              key="media"
              ripple
            >
              Medien
            </v-tab>
            <v-tab-item
              key="media"
            >
              <Media
                @fileSelected="setFile"
              />
              <div class="attachment-container">
                <QuestionAttachment
                  :key="attachment.url"
                  v-for="attachment in question.attachments"
                  :attachment="attachment"
                />
              </div>
            </v-tab-item>
          </v-tabs>
        </v-form>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
  import QuestionAttachment from './QuestionAttachment'
  import Question from "./Question"
  import Media from "./Media"

  let emptyQuestion = {
    answers: [{}, {}, {}, {}],
    category: null,
    type: null,
  }

  export default {
    props: ["categories", "baseQuestion", "mode"],
    data() {
      return {
        activeTab: null,
        valid: true,
        dialog: false,
        isLoading: false,
        question: emptyQuestion,
        snackbar: false,
        errorText: null,
        file: null
      }
    },
    mounted() {
      this.reset()
    },
    methods: {
      showError(error) {
        this.errorText = error
        this.snackbar = true
      },
      close() {
        this.reset()
        this.dialog = false
      },
      setFile(file) {
        this.file = file
      },
      save() {
        if (this.isLoading) {
          return
        }
        if (!this.$refs.form.validate()) {
          return
        }
        if (this.question.type === 1 && !this.question.answers.filter(answer => answer.correct == true).length) {
          this.showError("Bitte wählen Sie mindestens eine richtige Antwort aus")
          return
        }
        this.isLoading = true
        let fileData = null
        if(this.file) {
          fileData = {
            data: this.file.dataURL,
            name: this.file.name
          }
        }
        let postURL = "/backend/api/v1/questions/submissions"
        if(this.editMode) {
          postURL = "/backend/api/v1/questions/submissions/" + this.question.id
        }
        axios.post(postURL, {
          title: this.question.title,
          category: this.question.category,
          type: this.question.type,
          answers: this.question.answers.map((v) => v.content),
          answers_correct: this.question.answers.map((v) => v.correct),
          answers_feedback: this.question.answers.map((v) => v.feedback),
          file: fileData,
        })
          .then(response => {
            this.isLoading = false
            if (response.data.success) {
              this.close()
              this.$emit("added")
            } else {
              this.showError(response.data.msg)
            }
          })
          .catch(error => {
            this.isLoading = false
            this.showError("Error connecting to the server.")
          })
      },
      reset() {
        this.$refs.form.reset()
        if(this.editMode) {
          this.question = {
            id: this.baseQuestion.id,
            title: this.baseQuestion.title,
            answers: JSON.parse(JSON.stringify(this.baseQuestion.answers)),
            category: this.baseQuestion.category_id,
            type: this.baseQuestion.type,
            attachments: this.baseQuestion.attachments
          }
        }
        if(this.addMode) {
          this.question = JSON.parse(JSON.stringify(emptyQuestion))
        }
      },
      addAnswer() {
        this.question.answers.push({})
      },
      removeAnswer(index) {
        this.question.answers.splice(index, 1)
      },
    },
    computed: {
      editMode() {
        return this.mode === 'edit'
      },
      addMode() {
        return this.mode === 'add'
      },
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
    components: {
      QuestionAttachment,
      Question,
      Media
    }
  }
</script>

<style>
  .remove-answer {
    cursor: pointer;
  }
</style>

<style lang="scss" scoped>
  #app {
    .attachment-container {
      padding: 0 26px 26px 26px;
    }
  }
</style>
