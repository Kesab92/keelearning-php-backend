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
            Kategorien hinzufügen
          </v-toolbar-title>
          <v-spacer/>
          <v-text-field
            ref="search"
            v-model="searchString"
            placeholder="Suche nach Kategorie / ID"
            @input="search"
          />
        </v-toolbar>
        <v-card-text v-if="isSearching">
          <v-progress-linear indeterminate/>
        </v-card-text>
        <v-card-text v-else>
          <span
            v-if="foundCategories === null"
            class="grey--text"
          >
            Bitte Suchbegriff (min. 3 Zeichen) eingeben.
          </span>
          <span
            v-if="foundCategories !== null && !foundCategories.length"
            class="grey--text"
          >
            Keine Kategorien gefunden.
          </span>
          <v-data-table
            v-if="foundCategories && foundCategories.length"
            v-model="selectedCategories"
            :headers="table.headers"
            :items="foundCategories"
            :pagination.sync="table.pagination"
            class="elevation-1"
            item-key="id"
            select-all
          >
            <template v-slot:headers="props">
              <tr>
                <th>
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
                    'active': header.value === table.pagination.sortBy,
                    'asc': !table.pagination.descending,
                    'desc': table.pagination.descending,
                    'text-xs-left': header.align != 'right',
                    'text-xs-right': header.align == 'right',
                  }"
                  class="column sortable"
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
                  {{ props.item.name }}
                </td>
                <td class="text-xs-right">
                  {{ props.item.points }}
                </td>
                <td class="text-xs-right">
                  {{ props.item.question_count }}
                </td>
              </tr>
            </template>
          </v-data-table>
        </v-card-text>
        <v-divider/>
        <v-card-actions>
          <v-btn
            :disabled="!selectedCategories.length"
            color="primary"
            @click="addQuestions"
          >
            Kategorien hinzufügen
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
let axiosCancel = null

export default {
  data() {
    return {
      active: false,
      foundCategories: null,
      isSearching: false,
      searchString: "",
      selectedCategories: [],
      snackbar: {
        active: false,
        type: null,
        message: null,
      },
      table: {
        headers: [{
          text: "ID",
          value: "id",
        }, {
          text: "Kategorie",
          value: "name",
        }, {
          align: "right",
          text: "Punkte",
          value: "points",
        }, {
          align: "right",
          text: "Aktive Fragen",
          value: "question_count",
        }],
        pagination: {
          descending: false,
          rowsPerPage: 25,
          sortBy: "id",
        },
      },
    }
  },
  watch: {
    active() {
      if (axiosCancel) {
        axiosCancel()
      }
      if (this.active) {
        // only reset on open, to prevent flickering on close
        this.foundCategories = null
        this.isSearching = false
        this.searchString = ""
        this.selectedCategories = []
        this.$nextTick(() => this.$refs.search.$refs.input.select())
      }
    },
  },
  methods: {
    addQuestions() {
      this.$emit("add", this.selectedCategories)
      this.active = false
    },
    search() {
      this.foundCategories = null
      this.selectedCategories = []
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
      axios.post(`/backend/api/v1/categories/search`, {
        query: this.searchString,
        withoutIndexcards: true,
      }, {cancelToken}).then(response => {
        if(response instanceof axios.Cancel) {
          return
        }

        if (response.data.success) {
          this.foundCategories = response.data.categories
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
      if (this.selectedCategories.length) {
        this.selectedCategories = []
      } else {
        this.selectedCategories = [...this.foundCategories]
      }
    },
    changeSort(column) {
      if (this.table.pagination.sortBy === column) {
        this.table.pagination.descending = !this.table.pagination.descending
      } else {
        this.table.pagination.sortBy = column
        this.table.pagination.descending = false
      }
    },
  },
}
</script>
