<template>
  <div>
    <v-dialog
      v-model="active"
      scrollable
      width="80%"
    >
      <slot slot="activator"/>
      <v-card>
        <v-toolbar>
          <v-toolbar-title>
            Fragen hinzufügen
            <a
              target="_blank"
              style="font-size: 13px;display: block;font-weight: normal;margin-top: -5px;"
              :href="createQuestionsLink">Erstellen Sie an dieser Stelle neue Lernfragen</a>
          </v-toolbar-title>
          <v-spacer/>
          <v-text-field
            ref="search"
            v-model="searchString"
            placeholder="Suche nach Frage / Kategorie / ID"
          />
        </v-toolbar>
        <v-card-text>
          <span
            v-if="searchString.length < 3"
            class="grey--text"
          >
            Bitte Suchbegriff (min. 3 Zeichen) eingeben.
          </span>
          <span
            v-else-if="!isSearching && !foundQuestions.length"
            class="grey--text"
          >
            Keine Fragen gefunden.
          </span>
          <v-data-table
            v-if="searchString.length >= 3"
            v-model="selectedQuestions"
            :headers="table.headers"
            :items="foundQuestions"
            :loading="isSearching"
            :pagination.sync="pagination"
            :rows-per-page-items="[15, 25, 50, 100]"
            :total-items="questionsTotal"
            class="elevation-1"
            item-key="id"
            select-all>
            <template v-slot:headers="props">
              <tr>
                <th style="width: 80px">
                  <v-checkbox
                    :indeterminate="props.indeterminate"
                    :input-value="props.all"
                    hide-details
                    primary
                    @click.stop="toggleAll"
                  />
                </th>
                <th
                  v-for="header in props.headers"
                  :key="header.text"
                  :class="{
                    'active': header.value === pagination.sortBy,
                    'asc': !pagination.descending,
                    'desc': pagination.descending,
                  }"
                  class="column sortable text-xs-left"
                  :style="{
                    width: header.width ? header.width + 'px' : 'auto'
                  }"
                  @click="changeSort(header.value)"
                >
                  <v-icon small>arrow_upward</v-icon>
                  {{ header.text }}
                </th>
              </tr>
            </template>
            <template v-slot:items="props">
              <tr
                :active="props.selected"
                @click="props.selected = !props.selected"
              >
                <td>
                  <v-checkbox
                    :input-value="props.selected"
                    hide-details
                    primary
                  />
                </td>
                <td>
                  #{{ props.item.id }}
                </td>
                <td>
                  {{ props.item.title }}
                </td>
                <td>
                  {{ props.item.type }}
                </td>
                <td>
                  {{ props.item.category }}
                </td>
              </tr>
            </template>
          </v-data-table>
        </v-card-text>
        <v-divider/>
        <v-card-actions>
          <v-btn
            :disabled="!selectedQuestions.length"
            color="primary"
            @click="addQuestions"
          >
            Fragen hinzufügen
          </v-btn>
          <v-spacer/>
          <v-btn
            flat
            @click="active = false"
          >
            Abbrechen
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import {debounce} from 'lodash'

let axiosCancel = null

export default {
  props: {
    allowLearncards: {
      default: false,
      type: Boolean,
    },
  },
  data() {
    return {
      active: false,
      foundQuestions: [],
      questionsTotal: null,
      isSearching: false,
      pagination: {
        descending: false,
        page: 1,
        rowsPerPage: 15,
        sortBy: "id",
      },
      searchString: "",
      selectedQuestions: [],
      snackbar: {
        active: false,
        type: null,
        message: null,
      },
      table: {
        headers: [{
          text: "ID",
          value: "id",
          width: 110,
        }, {
          text: "Frage",
          value: "title",
        }, {
          text: "Typ",
          value: "type",
        }, {
          text: "Kategorie",
          value: "category",
        }],
      },
    }
  },
  computed: {
    createQuestionsLink() {
      return window.VUEX_STATE.relaunchBackendUIUrl + '/questions#/questions?create'
    },
  },
  watch: {
    active() {
      if (axiosCancel) {
        axiosCancel()
      }
      if (this.active) {
        // only reset on open, to prevent flickering on close
        this.foundQuestions = []
        this.questionsTotal = null
        this.isSearching = false
        this.searchString = ""
        this.selectedQuestions = []
        this.$nextTick(() => this.$refs.search.$refs.input.select())
      }
    },
    pagination: {
      deep: true,
      handler() {
        this.search()
      },
    },
    searchString: debounce(function () {
      this.search()
    }, 500),
  },
  methods: {
    addQuestions() {
      this.$emit("add", this.selectedQuestions)
      this.active = false
    },
    search() {
      this.foundQuestions = []
      this.selectedQuestions = []
      if (axiosCancel) {
        axiosCancel()
      }
      if (this.searchString.length < 3) {
        return
      }
      this.isSearching = true
      let cancelToken = new axios.CancelToken(c => {
        axiosCancel = c
      })
      axios.post(`/backend/api/v1/questions/search`, {
        ...this.pagination,
        query: this.searchString,
        allow_learncards: this.allowLearncards,
      }, {cancelToken}).then(response => {
        if(response instanceof axios.Cancel) {
          return
        }

        if (response.data.success) {
          this.foundQuestions = response.data.questions
          this.questionsTotal = response.data.count
        } else {
          this.$emit("error", response.data.error)
        }
        this.isSearching = false
      }).catch(error => {
        if (!axios.isCancel(error)) {
          this.$emit("error", "Ein unerwarteter Fehler ist aufgetreten.")
          this.isSearching = false
        }
      })
    },
    toggleAll() {
      if (this.selectedQuestions.length) {
        this.selectedQuestions = []
      } else {
        this.selectedQuestions = [...this.foundQuestions]
      }
    },
    changeSort(column) {
      if (this.pagination.sortBy === column) {
        this.pagination.descending = !this.pagination.descending
      } else {
        this.pagination.sortBy = column
        this.pagination.descending = false
      }
    },
  },
}
</script>
