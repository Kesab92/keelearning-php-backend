<template>
  <div class="text-xs-center">
    <v-dialog
      v-model="isOpen"
      width="500">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Neue Frage erstellen
        </v-card-title>
        <template v-if="categories.length > 0">
          <v-card-text v-if="isLoading">
            <v-progress-circular indeterminate/> Frage wird erstellt
          </v-card-text>
          <v-card-text v-else>
            <p>
              Geben Sie hier die Frage ein. Die Frage ist anschließend unsichtbar. Sie können dann die Antworten hinzufügen und die Frage einer Kategorie zuordnen und sie sichtbar schalten. Erst dann wird sie im Spiel verwendet.
            </p>

            <template v-if="!hideMultipleQuestionsTypes">
              <v-select
                v-model="questionType"
                :items="questionTypes"
                box
                hint="Der Frage-Typ kann nach dem Erstellen der Frage nicht mehr geändert werden."
                label="Fragetyp" />
            </template>

            <v-text-field
              v-model="title"
              label="Fragestellung"
              :counter="100"
              required
              hint="Da der Nutzer nur eine begrenzte Zeit für die Antwort zur Verfügung hat, sollte die Frage maximal 100 Zeichen lang sein."
              box />

            <CategorySelect
              v-model="category"
              box
              label="Kategorie"
              limit-to-tag-rights
              show-limited-categories
              :clearable="false"
            />

          </v-card-text>

          <v-divider />
          <v-card-actions>
            <v-spacer />
            <v-btn
              color="success"
              @click="save">Frage erstellen</v-btn>
            <v-btn
              color="secondary"
              @click="isOpen = false">Abbrechen</v-btn>
          </v-card-actions>
        </template>
        <template v-else>
          <v-card-title
            class="headline grey lighten-2"
            primary-title>
            Es sind keine Kategorien angelegt.
          </v-card-title>
          <v-card-text>
            Bitte erstellen Sie zuerst eine Kategorie bevor Sie eine Frage erstellen.
          </v-card-text>

          <v-divider />
          <v-card-actions>
            <v-spacer />
            <v-btn
              color="secondary"
              @click="isOpen = false">Schließen</v-btn>
          </v-card-actions>
        </template>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import CategorySelect from "../../partials/global/CategorySelect"
import constants from "../../../logic/constants"

  export default {
    props: {
      categories: {
        type: Array,
        required: true
      },
      hideMultipleQuestionsTypes: {
        type: Boolean,
        required: true,
      },
    },
    data() {
      return {
        isOpen: false,
        isLoading: true,
        title: null,
        category: null,
        questionType: constants.QUESTIONS.TYPE_SINGLE_CHOICE,
        questionTypes: [
          {
            text: 'Single Choice',
            value: constants.QUESTIONS.TYPE_SINGLE_CHOICE,
          },
          {
            text: 'Multiple Choice',
            value: constants.QUESTIONS.TYPE_MULTIPLE_CHOICE,
          },
          {
            text: 'Ja / Nein, Richtig / Falsch',
            value: constants.QUESTIONS.TYPE_BOOLEAN,
          },
          {
            text: 'Lernkarte',
            value: constants.QUESTIONS.TYPE_INDEX_CARD,
          },
        ],
      }
    },
    methods: {
      open() {
        this.isLoading = false
        this.isOpen = true
      },
      save() {
        if(this.isLoading) {
          return false
        }
        if(!this.title || !this.category) {
          alert('Bitte füllen Sie alle Felder aus.')
          return false
        }
        this.isLoading = true
        axios.post('/backend/api/v1/questions', {
          title: this.title,
          type: this.questionType,
          category: this.category,
        })
          .then((response) => {
            this.isOpen = false

            this.title = null
            this.category = null
            this.questionType = constants.QUESTIONS.TYPE_SINGLE_CHOICE

            this.$emit('done', response.data.id)
          })
          .catch(() => {
            alert("Beim Erstellen der Frage ist ein Fehler aufgetreten.")
          })
          .finally(() => {
            this.isLoading = false
          })
      },
    },
    components: {
      CategorySelect,
    },
  }
</script>
