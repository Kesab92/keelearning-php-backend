<template>
  <div>
    <v-snackbar
      :timeout="6000"
      :top="true"
      color="error"
      v-model="snackbar"
    >
      {{ errorText }}
      <v-btn
        @click.native="snackbar = false"
        dark
        flat>OK
      </v-btn>
    </v-snackbar>
    <v-stepper v-model="stepper">
      <v-stepper-header>
        <v-stepper-step
          :complete="stepper > 1"
          @click.native="stepper = 1"
          step="1">Vorbereitung</v-stepper-step>
        <v-divider />
        <v-stepper-step
          :complete="stepper > 2"
          @click.native="stepper = 2"
          step="2">Vorschau</v-stepper-step>
        <v-divider />
        <v-stepper-step
          @click.native="stepper = 3"
          step="3">Importieren</v-stepper-step>
      </v-stepper-header>

      <v-stepper-items>
        <v-stepper-content step="1">
          <h6 class="title">Import vorbereiten</h6>
          <div class="subheading mb-4">
            Wählen Sie optional TAGs und/oder ein Quiz-Team aus, die den Benutzern zugeordnet werden sollen. Anschließend
            wählen Sie Ihre CSV Datei aus.
          </div>

          <v-layout>
            <v-flex grow>
              <tag-select
                v-model="selectedTags"
                outline
                multiple
                limit-to-tag-rights />
            </v-flex>
            <v-flex shrink>
              <v-tooltip bottom>
                <div
                  class="pa-3"
                  slot="activator"
                  style="cursor: help">
                  <v-icon>info</v-icon>
                </div>
                <span>Diese TAGs werden allen Benutzern zugeordnet</span>
              </v-tooltip>
            </v-flex>
          </v-layout>

          <v-layout>
            <v-flex grow>
              <v-select
                :items="quizTeams"
                label="Quiz-Team"
                clearable
                outline
                v-if="!(!quizTeamsLoading && quizTeams.length === 0)"
                v-model="selectedQuizTeam"
              />
              <p
                v-if="!quizTeamsLoading && quizTeams.length === 0"
                class="grey--text">Es wurden noch keine Quiz-Teams angelegt.</p>
            </v-flex>
            <v-flex shrink>
              <v-tooltip bottom>
                <div
                  class="pa-3"
                  slot="activator"
                  style="cursor: help">
                  <v-icon>info</v-icon>
                </div>
                <span>Alle Benutzer werden diesem Quiz-Team zugeordnet</span>
              </v-tooltip>
            </v-flex>
          </v-layout>

          <v-layout>
            <v-flex grow>
              <v-select
                :items="availableCompareHeaders"
                label="Benutzer vergleichen anhand von"
                outline
                :hide-details="true"
                v-model="compareHeader"
              />
              <p
                v-show="isComparingByMeta"
                class="orange--text"
              >
                Bitte beachten Sie, dass der Abgleich anhand von unzuverlässigen Feldern zu Datenverlust führen kann.
              </p>
            </v-flex>
            <v-flex shrink>
              <v-tooltip bottom>
                <div
                  class="pa-3"
                  slot="activator"
                  style="cursor: help">
                  <v-icon>info</v-icon>
                </div>
                <span>Wählen Sie ein Feld, anhand dessen die Benutzer aus Ihrer CSV Datei mit den bestehenden Benutzern im System abgeglichen werden sollen</span>
              </v-tooltip>
            </v-flex>
          </v-layout>

          <v-layout>
            <v-flex grow>
              <v-checkbox
                v-model="deleteUsers"
                label="Benutzer löschen die nicht in der CSV vorhanden sind"/>
            </v-flex>
            <v-flex shrink>
              <v-tooltip bottom>
                <div
                  class="pa-3"
                  slot="activator"
                  style="cursor: help">
                  <v-icon>info</v-icon>
                </div>
                <span>Hiermit können Sie einen "Benutzerabgleich" durchführen. Dadurch werden alle Benutzer gelöscht, die in der CSV Datei nicht vorhanden sind.</span>
              </v-tooltip>
            </v-flex>
          </v-layout>

          <v-layout>
            <v-flex grow>
              <v-checkbox
                v-model="dontInviteUsers"
                label="Keine Einladungsemails versenden" />
            </v-flex>
            <v-flex shrink>
              <v-tooltip bottom>
                <div
                  class="pa-3"
                  slot="activator"
                  style="cursor: help">
                  <v-icon>info</v-icon>
                </div>
                <span>Wenn Sie die Einladungsemails später manuell über die Benutzerverwaltung versenden möchten, können Sie diesen Haken setzen, dann werden die Benutzer im System angelegt, aber noch nicht informiert.</span>
              </v-tooltip>
            </v-flex>
          </v-layout>

          <v-layout row>
            <v-flex
              grow
              pa-1>
              <input
                type="file"
                @change="setCSV"
                accept=".csv,.xls,.xlsx"
                ref="fileInput"
                class="hidden-screen-only"
              >
              <v-btn
                v-if="!csvParsing"
                color="primary"
                @click="openFileInput"
              >
                Import Datei wählen
              </v-btn>
              <p v-else>
                Datei wird gelesen...
              </p>
              <v-alert
                :key="error.code"
                :value="true"
                type="error"
                v-for="error in parsingErrors"
              >
                {{ error.message }} (Zeile {{ error.row }})
              </v-alert>
            </v-flex>
            <v-flex
              shrink
              pa-1>
              <v-btn
                :href="csvDownloadLink"
                target="_blank"
              >
                <v-icon left>cloud_download</v-icon>
                Vorlage herunterladen
              </v-btn>
            </v-flex>
          </v-layout>
        </v-stepper-content>

        <v-stepper-content step="2">
          <h6 class="title">Import überprüfen</h6>
          <div class="subheading mb-4">
            Hier sehen Sie alle zu importierenden Benutzer. Bitte achten Sie darauf alle Spalten richtig zuzuordnen.
          </div>
          <!-- Only for FORD -->
          <v-alert
            v-if="configuration && configuration.app_id === 10"
            v-model="advice"
            type="error"
          >
            Das Feld: 'Meta: Nachname' muss zugeordnet sein, damit sich die Benutzer registrieren können.
          </v-alert>
          <SelectionTable
            :available-headers="availableHeaders"
            :headers="headers"
            :items="users"
            @setHeader="setHeader"
            v-if="headers && users"
          />
          <v-btn
            class="mt-4"
            @click="stepper = 1"
          >
            Zurück
          </v-btn>
          <v-btn
            @click="stepper = 3"
            class="ml-0 mt-4"
            color="primary"
          >
            Nächster Schritt
          </v-btn>
        </v-stepper-content>

        <v-stepper-content step="3">
          <h6 class="title">Import durchführen</h6>
          <template v-if="users">
            <div class="subheading mb-4">
              <template v-if="users.length !== 1">Der Import enthält Daten zu {{ users.length }} Benutzern.</template>
              <template v-else>Der Import enthält Daten zu einem Benutzer.</template>
            </div>
            <CSVValidator
              :available-headers="availableHeaders"
              :backend-validator="backendValidation"
              :configuration="configuration"
              :general-error-detector="errorDetector"
              :headers="headers"
              :items="users"
              :loading="importIsLoading"
              :key="csvValidatorKey"
              :dont-invite-users="this.dontInviteUsers"
              @goBack="stepper = 2"
              @startImport="startImport"
              v-if="headers && users"
              type="USERS"
            />
          </template>
          <template v-else>
            <p>Bitte wählen Sie zuerst eine CSV Datei aus.</p>
          </template>

          <ImportProgressModal
            :open="importId !== null"
            :import-id="importId" />

        </v-stepper-content>
      </v-stepper-items>
    </v-stepper>
  </div>
</template>

<script>
  import CSVParser from '../../../logic/CSVParser'
  import CSVValidator from './CSVValidator'
  import ImportProgressModal from './ImportProgressModal'
  import SelectionTable from './SelectionTable'
  import userImportTableHeaders from '../../../logic/import/userImportTableHeaders'
  import TagSelect from "../global/TagSelect"
  import {mapGetters} from "vuex";

  export default {
    data() {
      return {
        stepper: 1,
        csvParsing: false,
        quizTeamsLoading: true,
        quizTeams: [],
        importIsLoading: false,
        selectedQuizTeam: null,
        selectedTags: [],
        parsingErrors: null,
        headers: null,
        users: null,
        snackbar: false,
        errorText: null,
        configurationLoading: false,
        configuration: null,
        compareHeader: 'mail',
        importId: null,
        deleteUsers: false,
        dontInviteUsers: false,
        advice: true,
      }
    },
    created() {
      this.fetchQuizTeams()
      this.fetchImportConfiguration()
    },
    methods: {
      fetchQuizTeams() {
        axios.get('/backend/api/v1/quiz-teams/list')
          .then((response) => {
            this.quizTeamsLoading = false
            this.quizTeams = response.data.quizTeams.map(quizTeam => {
              return {
                text: quizTeam.name,
                value: quizTeam.id,
              }
            }).sort((a, b) => {
              return a.text.localeCompare(b.text)
            })
          })
          .catch((e) => {
            this.quizTeamsLoading = false
            this.showError('Die Quiz-Teams konnten nicht vom Server abgerufen werden. Bitte wenden Sie sich an den Support.')
          })
      },
      fetchImportConfiguration() {
        axios.get('/backend/api/v1/import/configuration/userimport')
          .then((response) => {
            this.configurationLoading = false
            this.configuration = response.data
          })
          .catch(() => {
            this.configurationLoading = false
            this.showError('Die Import Konfiguration konnten nicht vom Server abgerufen werden. Bitte wenden Sie sich an den Support.')
          })
      },
      openFileInput() {
        if(!this.selectedTags.length && !this.isFullAdmin) {
          this.showError('Sie müssen mindestens einen TAG auswählen!')
          return
        }
        this.$refs.fileInput.value = ''
        this.$refs.fileInput.click()
      },
      showError(error) {
        this.errorText = error
        this.snackbar = true
      },
      setHeader(data) {
        this.$set(this.headers, data.idx, data.newValue)
      },
      setCSV($event) {
        if ($event.target.files.length > 0) {
          this.csvParsing = true
          this.$nextTick(() => {
            CSVParser.parse($event.target.files[0], data => {
              if (data.errors.length) {
                this.parsingErrors = data.errors
              } else {
                this.errors = null
                data = data.data
                let headers = data.shift()
                this.headers = CSVParser.matchHeaders(headers, this.availableHeaders)
                this.users = data
                this.stepper = 2
              }
              this.csvParsing = false
            })
          })
        }
      },
      startImport() {
        if (this.importIsLoading) {
          return
        }
        this.importIsLoading = true
        let data = JSON.stringify({
          tag_ids: this.selectedTags,
          quiz_team_id: this.selectedQuizTeam,
          compare_header: this.compareHeader,
          headers: this.headers,
          users: this.users,
          delete_users: this.deleteUsers,
          dont_invite_users: this.dontInviteUsers,
        })
        axios.post('/backend/api/v1/import/users', {data: data}).then(response => {
          this.importId = response.data.importId
        })
        .catch(() => {
          this.showError('Die Daten konnten nicht importiert werden.')
        })
        .finally(() => {
          this.importIsLoading = false
        })
      },
      backendValidation() {
        let data = JSON.stringify({
          tag_ids: this.selectedTags,
          quiz_team_id: this.selectedQuizTeam,
          compare_header: this.compareHeader,
          headers: this.headers,
          users: this.users,
          delete_users: this.deleteUsers,
          dont_invite_users: this.dontInviteUsers,
        })
        return axios.post('/backend/api/v1/import/collect-changes/userimport', {data: data}).then(response => {
          return response.data
        })
          .catch((response) => {
            if (typeof response.data !== 'undefined' && typeof response.data.errors !== 'undefined') {
              this.showError(response.data.errors.join(' '))
            } else {
              this.showError('Die Daten konnten nicht überprüft werden. Bitte kontaktieren Sie den Support.')
            }
          })
      },
      errorDetector() {
        let compareIdx = null
        this.headers.forEach((header, idx) => {
          if(header === this.compareHeader) {
            compareIdx = idx
          }
        })
        if(compareIdx === null) {
          return 'Das Feld "' + this.availableHeaders[this.compareHeader].title + '" für den Vergleich der Benutzer wurde nicht zugewiesen.'
        }
        let existingUserIdentifications = {}
        let duplicateUserIdentification = null
        this.users.forEach(user => {
          if(duplicateUserIdentification !== null) {
            return
          }
          let userIdentification = user[compareIdx]
          if(typeof existingUserIdentifications[userIdentification] === 'undefined') {
            existingUserIdentifications[userIdentification] = true
          } else {
            duplicateUserIdentification = userIdentification
          }
        })
        if(duplicateUserIdentification !== null) {
          return `Der Benutzer mit dem Vergleichswert ${this.availableHeaders[this.compareHeader].title}: "${duplicateUserIdentification}" ist doppelt vorhanden. Eventuell ist die Spalte falsch zugeordnet, oder der Benutzer ist tatsächlich doppelt vorhanden.`
        }
        return null
      },
    },
    computed: {
      ...mapGetters({
        isFullAdmin: 'app/isFullAdmin',
      }),
      availableHeaders() {
        let headers = userImportTableHeaders
        if (!this.configuration) {
          return headers
        }

        this.configuration.tagGroups.forEach(tagGroup => {
          headers['tag_group_' + tagGroup.id] = {
            title: 'TAG Gruppe: ' + tagGroup.name,
            required: false,
          }
        })

        Object.keys(this.configuration.meta).forEach(metaKey => {
          // Ford adjustments: Meta Fields are required
          let required = this.configuration.app_id === 10
          headers['meta_' + metaKey] = {
            title: 'Meta: ' + this.configuration.meta[metaKey].label,
            required: required,
          }
        })

        let languages = this.configuration.languages.join(',')
        headers.language.title = 'Sprache (' + languages + ')'
        if (this.configuration.languages.length > 1) {
          headers.language.required = true
          headers.language.title = '*' + headers.language.title
        }

        return headers
      },
      availableCompareHeaders() {
        let availableHeaders = [
          {
            text: 'E-Mail Adresse',
            value: 'mail',
          },
        ]
        if (!this.configuration) {
          return availableHeaders
        }
        Object.keys(this.configuration.meta).forEach(metaKey => {
          if (!this.configuration.meta[metaKey].compare) {
            return
          }
          availableHeaders.push({
            text: `Meta: ${this.configuration.meta[metaKey].label}`,
            value: `meta_${metaKey}`,
          })
        })
        return availableHeaders
      },
      csvDownloadLink() {
        return '/import/examples/user-import'
      },
      csvValidatorKey() {
        let tags = ""
        if(this.selectedTags) {
          tags = this.selectedTags.join(',')
        }
        return this.compareHeader + '-' + this.selectedQuizTeam + '-' + parseInt(this.deleteUsers) + '-' + tags
      },
      isComparingByMeta() {
        return this.compareHeader.indexOf('meta_') === 0
      },
    },
    components: {
      TagSelect,
      CSVValidator,
      SelectionTable,
      ImportProgressModal,
    },
  }
</script>
