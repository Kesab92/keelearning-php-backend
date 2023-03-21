<template>
  <div v-if="myRights['tests-stats']">
    <v-snackbar
      :color="snackbarOptions.color"
      :top="true"
      v-model="snackbarOptions.enabled">
      {{ snackbarOptions.text }}
    </v-snackbar>
    <v-toolbar color="white">
      <a href="/tests#/tests">
        <v-btn
          flat
          icon>
          <v-icon>arrow_back</v-icon>
        </v-btn>
      </a>
      <v-toolbar-title
        class="test-title"
        v-if="!loading">
        {{ test.name }}<br>
        <span>Benutzer, die aktuell berechtigt sind, den Test durchzuführen</span>

      </v-toolbar-title>
    </v-toolbar>
    <v-flex xs12>
      <v-card>
        <v-card-title>
          <v-btn
            :disabled="loading || selectedElements.length === 0"
            :loading="loading"
            @click.prevent="sendReminder"
            color="success"
            v-if="attempts != 1 && canAnyUserNotified">
            Erinnerung versenden
          </v-btn>
          <v-spacer/>
          <a :href="'/tests/' + testId + '/resultscsv'" target="_blank">
            <v-btn color="primary">
              <v-icon
                dark
                left>cloud_download</v-icon>
              Gesamte Statistik
            </v-btn>
          </a>
          <a :href="'/tests/' + testId + '/results-history-csv'" target="_blank">
            <v-btn color="primary">
              <v-icon
                dark
                left>history</v-icon>
              Ergebnisse & Erinnerungen
            </v-btn>
          </a>
        </v-card-title>
        <v-card-text>
          <v-data-table
            :headers="headers"
            :items="results"
            :loading="loading"
            :rows-per-page-items="rowsPerPage"
            :select-all="attempts != 1"
            v-model="selectedElements">
            <template
              slot="headerCell"
              slot-scope="props">
              <span class="test-result-no-padding">
                {{ props.header.text }}
              </span>
            </template>
            <template
              slot="items"
              slot-scope="props">
              <tr @click="props.expanded = !props.expanded">
                <td
                  v-if="attempts != 1"
                  @click.stop
                >
                  <v-checkbox
                    hide-details
                    primary
                    v-model="props.selected"
                    v-show="!props.item.passed"
                  />
                </td>
                <td class="test-result-no-padding">
                  <v-icon
                    small
                    v-if="props.expanded">keyboard_arrow_up</v-icon>
                  <v-icon
                    small
                    v-if="!props.expanded">keyboard_arrow_down</v-icon>
                  <template v-if="showPersonalData">
                    {{ props.item.name }}
                  </template>
                </td>
                <td
                  v-if="showPersonalData"
                  class="test-result-no-padding">
                  {{ props.item.firstname }}
                </td>
                <td
                  v-if="showPersonalData"
                  class="test-result-no-padding">
                  {{ props.item.lastname }}
                </td>
                <td
                  v-if="showEmails('tests')"
                  class="test-result-no-padding">
                  {{ props.item.email }}
                </td>
                <td>
                  <v-icon
                    color="success"
                    v-if="props.item.passed">done</v-icon>
                  <v-icon
                    color="error"
                    v-else>close</v-icon>
                </td>
                <td>
                  <template v-if="props.item.date">
                    {{ props.item.date | dateTime }}
                  </template>
                  <template v-else>-</template>
                </td>
                <td v-if="showPersonalData">
                  <a
                    v-if="props.item.certificateLink"
                    :href="props.item.certificateLink"
                    target="_blank"
                  >
                    <v-btn color="success">
                      <v-icon
                        dark
                        left>cloud_download</v-icon>
                      Download
                    </v-btn>
                  </a>
                </td>
                <td>
                  <a
                    v-if="props.item.date"
                    :href="`/tests/${testId}/answers/${props.item.id}`"
                    target="_blank"
                  >
                    <v-btn
                      color="success">
                      <v-icon dark>cloud_download</v-icon>
                    </v-btn>
                  </a>
                </td>
              </tr>
            </template>

            <template v-slot:expand="props">
              <v-card flat>
                <v-card-text>
                  <template v-if="props.item.history.length > 0">
                    <div
                      v-for="entry in props.item.history"
                      :key="entry.id"
                    >
                      <test-result-history-entry
                        :history-entry="entry"
                        :questions="questions"
                      />
                    </div>
                  </template>
                  <div v-else>Keine Ereignisse</div>
                </v-card-text>
              </v-card>
            </template>
          </v-data-table>
        </v-card-text>
      </v-card>
    </v-flex>
  </div>
</template>

<script>
  import TestResultHistoryEntry from "./TestResultHistoryEntry"
  import {mapGetters} from "vuex"

  export default {
    computed: {
      ...mapGetters({
        myRights: 'app/myRights',
        showEmails: 'app/showEmails',
        showPersonalDataGetter: 'app/showPersonalData',
      }),
      testId() {
        return /\/tests\/(.*)\/results/.exec(location.pathname)[1]
      },
      showPersonalData() {
        return this.showPersonalDataGetter('tests')
      },
      canAnyUserNotified() {
        if (!this.results) {
          return false
        }
        return this.results.filter(item => !item.passed).length > 0
      },
      headers() {
        let headers = [
          {
            text: this.showPersonalData ? "Benutzername" : "",
            sortable: this.showPersonalData,
            value: "name",
            visible: true,
          },
          {
            text: "Vorname",
            sortable: true,
            value: "firstname",
            visible: this.showPersonalData,
          },
          {
            text: "Nachname",
            sortable: true,
            value: "lastname",
            visible: this.showPersonalData,
          },
          {
            text: "E-Mail",
            sortable: true,
            value: "email",
            visible: this.showEmails('tests'),
          },
          {
            text: "Bestanden",
            sortable: true,
            value: "passed",
            visible: true,
          },
          {
            text: "Datum",
            sortable: true,
            value: "date",
            visible: true,
          },
          {
            text: "Zertifikat",
            sortable: false,
            value: "certificateLink",
            visible: this.showPersonalData,
          },
          {
            text: "Antworten",
            sortable: false,
            visible: true,
          },
        ]
        return headers.filter(header => header.visible)
      },
    },
    created() {
      this.loadResults()
    },
    data() {
      return {
        loading: false,
        selectedElements: [],
        attempts: false,
        results: [],
        test: null,
        questions: null,
        snackbarOptions: {
          text: "",
          color: "",
          enabled: false,
        },
        rowsPerPage: [50, 100, {"text": "Alle", "value": -1}],
      }
    },
    methods: {
      loadResults() {
        this.loading = true
        axios.get("/backend/api/v1/tests/" + this.testId + "/results").then(response => {
          if (response.data.success) {
            this.test = response.data.data.test
            this.results = response.data.data.users
            this.attempts = response.data.data.attempts
            this.questions = response.data.data.questions
          }
          this.loading = false
        })
      },
      sendReminder() {
        this.loading = true
        let model = this.selectedElements.map(item => item.id)
        axios.post("/backend/api/v1/tests/" + this.testId + "/remind", {user_ids: model}).then(response => {
          if (response.data.success) {
            this.handleResponse("Die Erinnerungen wurden versandt.", "success")
            this.loadResults()
          }
          this.loading = false
        }).catch(err => {
          this.handleResponse(err, "error")
        })
      },
      handleResponse(text, color) {
        this.snackbarOptions.text = text
        this.snackbarOptions.color = color
        this.snackbarOptions.enabled = true
      },
    },
    components: {
      TestResultHistoryEntry,
    },
  }
</script>

<style lang="scss" scoped>

  #app table.v-table tbody td:first-child,
  #app table.v-table tbody th:first-child,
  #app table.v-table thead td:first-child,
  #app table.v-table thead th:first-child {
    width: 50px;
    padding-right: 0;
    padding-left: 24px;
  }

  #app table.v-table tbody td {
    cursor: pointer;
  }

  #app {
    .spacer {
      margin-left: 20px;
    }
  }

  .test-title {
    line-height: 20px;

    span {
      font-size: 14px;
      line-height: 14px;
      color: #676767;
    }
  }
</style>

<style>
  .test-result-no-padding {
    padding-left: 0px !important;
  }
</style>
