<template>
  <div>
    <div class="c-moduleIntro">
      <h1 class="c-moduleIntro__heading">
        Lernfragen
      </h1>
      <div class="c-moduleIntro__description">
        Verwalten Sie die Fragen die im Quiz-Battle, Powerlearning, Test und Kurs verwendet werden.
      </div>
      <div class="c-moduleIntro__links">
        <v-btn
          flat
          color="primary"
          small
          href="https://helpdesk.keelearning.de/de/articles/4256027-fragen-exportieren-redigieren-und-ubersetzen"
          target="_blank"
        >
          <v-icon
            small
            class="mr-1">
            help
          </v-icon>
          Fragen exportieren, redigieren und übersetzen
        </v-btn>
      </div>
    </div>
    <DeleteQuestionsModal
      ref="deleteQuestionsModal"
      :question-ids="selectedQuestionIds"
      @done="loadData" />
    <CreateQuestionModal
      ref="createQuestionModal"
      :hide-multiple-questions-types="appSettings.hide_multiple_questions_types == 1"
      :categories="categories"
      @done="editQuestion" />
    <QuestionsExportModal
      ref="questionsExportModal"
      :available-languages="availableLanguages"
      :current-language="activeLanguage"
      :selected-filters="selectedFilters"
      :category="selectedCategory"
      :query="query"
      :questions-count="questionCount" />
    <QuestionsImportModal
      ref="questionsImportModal"
      :available-languages="availableLanguages"
      @imported="loadData" />

    <v-layout row>
      <v-flex shrink>
        <v-btn
          color="primary"
          @click="$refs.createQuestionModal.open()">
          <v-icon
            left
            dark>add</v-icon>
          Neue Frage
        </v-btn>
      </v-flex>
      <v-flex shrink>
        <v-overflow-btn
          class="s-actionsButton"
          v-model="action"
          :items="actions"
          :loading="actionLoading"
          label="Aktionen" />
      </v-flex>
      <v-spacer />
      <v-flex shrink>
        <v-btn
          color="secondary"
          @click="exportQuestions">
          Fragen exportieren
        </v-btn>
      </v-flex>
    </v-layout>

    <warnings :warnings="warnings" />

    <v-card class="mt-2 mb-4">
      <v-card-title primary-title>
        <v-layout row>
          <v-flex
            xs12
            md4>
            <v-select
              v-model="selectedFilters"
              :items="filters"
              label="Filter"
              class="mr-4"
              multiple
              clearable
              dense>
              <template v-slot:item="data">
                <template v-if="(typeof data.item) !== 'object'">
                  <v-list-tile-content v-text="data.item"/>
                </template>
                <template v-else>
                  <v-list-tile-action>
                    <v-checkbox
                      v-model="data.tile.props.value" />
                  </v-list-tile-action>
                  <v-list-tile-content>
                    <v-list-tile-title v-html="data.item.text"/>
                  </v-list-tile-content>
                </template>
              </template>
            </v-select>
          </v-flex>
          <v-flex
            v-if="false"
            xs12
            md3>
            <v-select
              v-model="usageFilter"
              :items="usageFilters"
              label="Verwendet in"
              class="mr-4"
              clearable
              dense />
          </v-flex>
          <v-flex
            xs12
            md4>
            <CategorySelect
              v-model="selectedCategory"
              dense
              label="Kategorien"
              class="mr-4"
              clearable
              limit-to-tag-rights
            />
          </v-flex>
          <v-flex
            xs12
            md4>
            <v-text-field
              placeholder="Frage suchen..."
              append-icon="search"
              v-model="query"
              clearable
              single-line />
          </v-flex>
        </v-layout>
      </v-card-title>
      <v-data-table
        :loading="isLoading"
        :headers="headers"
        :items="questions"
        :pagination.sync="pagination"
        :rows-per-page-items="[50, 100, 200]"
        :total-items="questionCount"
        v-model="selected"
        class="elevation-1 questions-table"
        select-all
        item-key="id">
        <tr
          slot="items"
          slot-scope="props"
          @click="editQuestion(props.item.id)"
          class="question-row clickable"
          :class="{ invisible: !props.item.visible }">
          <td width="80px">
            <v-checkbox
              v-model="props.selected"
              @click.stop="props.selected = !props.selected"
              primary
              hide-details />
          </td>
          <td>
            #{{ props.item.id }}
          </td>
          <td>
            {{ props.item.title }}
          </td>
          <td>
            {{ getCategory(props.item.category_id) }}
          </td>
          <td>
            <v-icon v-if="props.item.visible">visibility</v-icon>
            <v-icon v-else>visibility_off</v-icon>
          </td>
          <td v-if="availableLanguages.length > 1">
            {{ getMissingTranslations(missingTranslations, props.item.id) }}
          </td>
          <td class="no-wrap">
            {{ props.item.updated_at | dateTime }}
          </td>
        </tr>
        <template slot="no-data">
          <v-alert
            v-show="(!questions || questions.length === 0) && !isLoading"
            :value="true"
            type="info">
            Es wurden keine Fragen gefunden.
          </v-alert>
        </template>
        <template slot="actions-prepend">
          <div class="page-select">
            Page:
            <v-select
              v-model="pagination.page"
              :items="pageSelectOptions"
              class="pagination" />
          </div>
        </template>
      </v-data-table>
    </v-card>
    <QuestionSidebar />
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import {debounce} from 'lodash'
import QuestionSidebar from "./QuestionSidebar"
import DeleteQuestionsModal from "./components/DeleteQuestionsModal"
import CreateQuestionModal from "./components/CreateQuestionModal"
import QuestionsImportModal from "./components/QuestionsImportModal"
import QuestionsExportModal from "./components/QuestionsExportModal"
import CategorySelect from "../partials/global/CategorySelect"
import constants from "../../logic/constants"
import tableConfig from "../../mixins/tableConfig"

  export default {
    mixins: [
      tableConfig,
    ],
    data() {
      return {
        filters: [
          {
            header: 'Sichtbarkeit'
          },
          {
            text: 'Sichtbare Fragen',
            value: 'visibility_1',
          },
          {
            text: 'Unsichtbare Fragen',
            value: 'visibility_0',
          },
          {
            text: 'Alle Fragen',
            value: 'visibility_-1',
          },
          {
            header: 'Typ'
          },
          {
            text: 'Single Choice',
            value: 'type_' + constants.QUESTIONS.TYPE_SINGLE_CHOICE,
          },
          {
            text: 'Multiple Choice',
            value: 'type_' + constants.QUESTIONS.TYPE_MULTIPLE_CHOICE,
          },
          {
            text: 'Richtig / Falsch',
            value: 'type_' + constants.QUESTIONS.TYPE_BOOLEAN,
          },
          {
            text: 'Lernkarte',
            value: 'type_' + constants.QUESTIONS.TYPE_INDEX_CARD,
          },
        ],
        usageFilter: null,
        usageFilters: [
          {
            text: 'Quiz',
            value: 'quiz',
          },
          {
            text: 'Powerlearning',
            value: 'learning',
          },
          {
            text: 'Tests',
            value: 'tests',
          },
        ],
        action: null,
        actionLoading: null,
        actions: [
          {
            text: 'Auswahl aktivieren',
            value: 'activate',
          },
          {
            text: 'Auswahl deaktivieren',
            value: 'deactivate',
          },
          {
            text: 'Auswahl löschen',
            value: 'delete',
          },
        ],
        selected: [],
        warnings: [],
      }
    },
    watch: {
      $route() {
        if(this.$route.query.create !== undefined) {
          this.$refs.createQuestionModal.open()
        }
        if(this.$route.name === 'questions.index') {
          this.restoreConfig()
          this.loadData()
        }
      },
      pagination: {
        handler() {
          this.loadData()
        },
        deep: true
      },
      selectedFilters() {
        this.storeConfig()
        this.loadData()
      },
      query: debounce(function() {
        this.storeConfig()
        this.loadData()
      }, 500),
      selectedCategory() {
        this.storeConfig()
        this.loadData()
      },
      action() {
        if(this.action) {
          if(!this.selected.length) {
            alert('Bitte wählen Sie zuerst Fragen aus der Liste aus')
          }  else {
            // Call the action handler
            this[this.action]()
          }
          this.$nextTick(() => {
            this.action = null
          })
        }
      },
    },
    mounted() {
      if(this.$route.query.create !== undefined) {
        this.$refs.createQuestionModal.open()
      }
    },
    created() {
      if(this.$route.name === 'questions.index' && this.$route.query.create !== undefined) {
        this.restoreConfig()
      }
    },
    computed: {
      ...mapGetters({
        appSettings: 'app/appSettings',
        activeLanguage: 'languages/activeLanguage',
        categories: 'categories/categories',
        questionCount: 'questions/questionCount',
        questions: 'questions/questions',
        missingTranslations: 'questions/missingTranslations',
        isLoading: 'questions/listIsLoading'
      }),
      pagination: {
        get() {
          return this.$store.state.questions.pagination
        },
        set(data) {
          this.$store.commit('questions/setPagination', data)
        },
      },
      query: {
        get() {
          return this.$store.state.questions.query
        },
        set(data) {
          this.$store.commit('questions/setQuery', data)
        },
      },
      selectedFilters: {
        get() {
          return this.$store.state.questions.filters
        },
        set(data) {
          this.$store.commit('questions/setFilters', data)
        },
      },
      selectedCategory: {
        get() {
          return this.$store.state.questions.category
        },
        set(data) {
          this.$store.commit('questions/setCategory', data)
        },
      },
      availableLanguages() {
        return Object.keys(this.$store.getters['app/languages'])
      },
      headers() {
        const headers = [
          {
            text: 'ID',
            value: 'id',
            width: '90px',
          },
          {
            text: 'Frage',
            value: 'title',
            sortable: false,
          },
          {
            text: 'Kategorie',
            value: 'category',
          },
          {
            text: 'Sichtbar',
            value: 'visible',
          },
        ]
        if(this.availableLanguages.length > 1) {
          headers.push({
            text: 'Fehlende Übersetzungen',
            value: 'missing_translations',
          })
        }
        headers.push({
          text: 'Zuletzt bearbeitet',
          value: 'updated_at',
          width: '140px',
        })

        return headers
      },
      exportLink() {
        let settings = {...this.pagination}
        delete settings.page
        delete settings.rowsPerPage
        delete settings.totalItems
        settings.query = this.query
        settings.selectedFilters = this.selectedFilters
        if (this.selectedCategory) {
          settings.category = this.selectedCategory
        }

        let query = Object.keys(settings).map(key => {
          if (!settings[key]) {
            return null
          }
          return `${encodeURIComponent(key)}=${encodeURIComponent(settings[key])}`
        }).filter(v => v !== null).join('&')
        return `/questions/export?${query}`
      },
      pageSelectOptions() {
        if(!this.questionCount || !this.pagination.rowsPerPage) {
          return [1]
        }
        const max = Math.ceil(this.questionCount / this.pagination.rowsPerPage)
        const options = []
        for(let i = 1;i<=max;i++) {
          options.push(i)
        }
        return options
      },
      selectedQuestionIds() {
        return this.selected.map(question => question.id)
      },
    },
    methods: {
      exportQuestions() {
        this.$refs.questionsExportModal.open()
      },
      importQuestions() {
        this.$refs.questionsImportModal.open()
      },
      getCategory(categoryId) {
        const category = this.categories.find(category => category.id === categoryId)
        if(!category) {
          return 'n/a'
        }
        return category.name
      },
      editQuestion(questionId) {
        this.$router.push({
          name: 'questions.edit.general',
          params: {
            questionId: questionId,
          },
        })
      },
      loadData() {
        this.$store.dispatch('questions/loadQuestions').finally(() => {
          this.selected = []
        })
      },
      activate() {
        if(!confirm('Sind Sie sicher, dass sie alle ' + this.selectedQuestionIds.length + ' Fragen aktivieren möchten?')) {
          return
        }
        this.actionLoading = true
        axios.post('/backend/api/v1/questions/activateMultiple', {
          questions: this.selectedQuestionIds,
        })
          .then(() => {
            this.loadData()
          })
          .catch(() => {
            alert("Beim Aktivieren der gewählten Fragen ist ein Fehler aufgetreten.")
          })
          .finally(() => {
            this.actionLoading = false
          })
      },
      deactivate() {
        if(!confirm('Sind Sie sicher, dass sie alle ' + this.selectedQuestionIds.length + ' Fragen deaktivieren möchten?')) {
          return
        }
        this.actionLoading = true
        axios.post('/backend/api/v1/questions/deactivateMultiple', {
          questions: this.selectedQuestionIds,
        })
          .then(() => {
            this.loadData()
          })
          .catch(() => {
            alert("Beim Deaktivieren der gewählten Fragen ist ein Fehler aufgetreten.")
          })
          .finally(() => {
            this.actionLoading = false
          })
      },
      delete() {
        this.$refs.deleteQuestionsModal.open()
      },
      getMissingTranslations(missingTranslations, questionId) {
        if(typeof missingTranslations[questionId] === 'undefined') {
          return '-'
        }
        return missingTranslations[questionId].map(translation => translation.toUpperCase()).join(', ')
      },
      getCurrentTableConfig() {
        const config = {
          selectedFilters: this.selectedFilters,
        }
        if(this.query) {
          config.query = this.query
        }
        if(this.selectedCategory) {
          config.selectedCategory = this.selectedCategory
        }
        return config
      },
      getBaseRoute() {
        return {
          name: 'questions.index',
        }
      },
    },
    components: {
      QuestionSidebar,
      DeleteQuestionsModal,
      CreateQuestionModal,
      QuestionsImportModal,
      QuestionsExportModal,
      CategorySelect,
    },
  }
</script>

<style lang="scss">
  body > .v-dialog__content .v-dialog.v-dialog--active {
    height: 90% !important;
  }

  .modal {
    background: white;
    margin: 0 auto;
    min-height: 100%;
    padding: 20px;
    position: relative;
    width: 640px;

    &.loading * {
      opacity: 0;
    }
  }

  #app .s-actionsButton.v-overflow-btn {
    margin-top: 0;
    min-width: 240px;

    .v-input__control:before {
      display: none;
    }
  }

  .ui.segment.menu {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
  }

  .question-row.invisible td {
    color: rgba(0, 0, 0, 0.5);

    ::v-deep .v-icon {
      opacity: 0.5;
    }
  }

  #app .questions-table {
    .v-datatable__actions__select {
      max-width: 180px;
    }
    .page-select {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      margin-right: 14px;
      height: 58px; // IE11 fix
      margin-bottom: -6px;
      color: rgba(0, 0, 0, 0.54);

      // IE11 fixes
      .v-select__slot, .v-select__selections {
        height: 32px;
      }

      .v-select {
        flex: 0 1 0;
        margin: 13px 0 13px 34px;
        font-size: 12px;
      }
    }
  }

  // "select all" input button
  #app > div.application--wrap > div > div.v-card.v-sheet.theme--light > div.elevation-1 > div.v-table__overflow > table > thead > tr:nth-child(1) > th:nth-child(1) .v-input--selection-controls__input {
    position: relative;

    &::after {
      background: white;
      border: 2px solid gray;
      border-radius: 5px;
      content: 'Betrifft nur sichtbare Ergebnisse';
      display: block;
      left: 50%;
      opacity: 0;
      padding: 3px 5px;
      pointer-events: none;
      position: absolute;
      top: 25px;
      z-index: 50;
    }

    &:hover::after {
      opacity: 1;
    }
  }
</style>
