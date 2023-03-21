<template>
  <div>
    <v-alert
      v-for="error in errors"
      :key="error"
      :value="true"
      type="error"
    >
      {{ error }}
    </v-alert>
    <v-alert
      v-for="warning in warnings"
      :key="warning"
      :value="true"
      type="warning"
    >
      {{ warning }}
    </v-alert>
    <template v-if="backendValidationResult !== null">
      <h3>Folgende Aktionen werden durchgeführt</h3>
      <v-flex
        xs12
        lg6>
        <v-expansion-panel>
          <v-expansion-panel-content
            v-for="(error, actionKey) in backendValidationResult.errors"
            :key="actionKey"
          >
            <v-icon
              slot="actions"
              color="error">error</v-icon>
            <div slot="header">{{ error.message }}</div>
            <v-card>
              <v-card-text
                class="grey lighten-3"
                v-for="(entry, idx) in error.data"
                :key="idx">{{ entry }}</v-card-text>
            </v-card>
          </v-expansion-panel-content>
          <v-expansion-panel-content
            v-for="(action, actionKey) in backendValidationResult.actions"
            :key="actionKey"
          >
            <div slot="header">{{ action.message }}</div>
            <v-card>
              <template v-for="(entry, idx) in action.data">
                <v-card-text
                  class="grey lighten-3"
                  :key="idx + '-entry'">{{ entry }}</v-card-text>
                <v-divider
                  light
                  :key="idx + '-divider'"/>
              </template>
            </v-card>
          </v-expansion-panel-content>
        </v-expansion-panel>
      </v-flex>
    </template>
    <v-btn
      @click="$emit('goBack')"
      class="mt-2"
    >
      Zurück
    </v-btn>
    <v-btn
      v-if="!dataValidated && !errors && !warnings && !validationRunning"
      color="primary"
      @click="validateData"
    >
      Import prüfen
    </v-btn>
    <v-progress-circular
      indeterminate
      color="primary"
      v-if="validationRunning"
    />
    <template v-if="dataValidated && !hasErrors">
      <p
        class="mt-3 mb-2"
        v-if="type === 'USERS'">
        <strong>Hinweis:</strong>
        <span
          v-if="!dontInviteUsers"
          class="warning-message">
          Mit dem Klick auf "Import durchführen" werden automatisch Benutzereinladungs-Emails versandt.
          Um die bestehende Einladungsemail (AppInvitation) zu bearbeiten klicken Sie bitte <a href="/mails?edit=AppInvitation">hier</a>.
        </span>
        <span
          v-if="dontInviteUsers"
          class="warning-message">
          Beim Import werden keine Einladungsemails an die Benutzer versendet.
          Um den Benutzern ihre Zugangsdaten zur App zuzusenden nutzen Sie anschließend bitte die Benutzerverwaltung.
        </span>
      </p>
      <v-btn
        color="primary"
        :disabled="loading"
        @click="$emit('startImport')"
      >
        Import durchführen
      </v-btn>
      <p class="mt-4 mb-1">
        Die Daten wurden überprüft und können nun importiert werden.
      </p>
    </template>
  </div>
</template>

<script>
  export default {
    props: [
      'availableHeaders',
      'backendValidator',
      'configuration',
      'dontInviteUsers',
      'generalErrorDetector',
      'headers',
      'items',
      'loading',
      'type',
    ],
    data() {
      return {
        warnings: null,
        errors: null,
        dataValidated: false,
        backendValidationResult: null,
        validationRunning: false,
      }
    },
    watch: {
      items() {
        this.resetErrors()
      },
      headers() {
        this.resetErrors()
      },
    },
    methods: {
      resetErrors() {
        this.warnings = null
        this.errors = null
        this.dataValidated = false
        this.backendValidationResult = null
      },
      validateData() {
        this.validationRunning = true
        this.warnings = []
        this.errors = []
        let duplicateHeader = this.headers.find(header => {
          if(header !== null) {
            return this.headers.filter(otherHeader => header === otherHeader).length > 1
          } else {
            return false
          }
        })
        if (duplicateHeader) {
          this.errors.push('Sie haben die Spalte "' + this.availableHeaders[duplicateHeader].title + '" mehr als einmal zugewiesen.')
        }

        let missingHeader = Object.keys(this.availableHeaders).find(availableHeaderKey => {
          let availableHeader = this.availableHeaders[availableHeaderKey]
          return availableHeader.required && !this.headers.find(h => h === availableHeaderKey)
        })
        if (missingHeader) {
          this.errors.push('Sie haben die Spalte "' + this.availableHeaders[missingHeader].title + '" nicht zugewiesen.')
        }

        if(this.generalErrorDetector) {
          let generalError = this.generalErrorDetector(this.items, this.headers, this.availableHeaders)
          if(generalError) {
            this.errors.push(generalError)
          }
        }

        if(this.errors.length > 0) {
          this.dataValidated = true
          this.validationRunning = false
          return
        }

        this.headers.forEach((header, idx) => {
          let data = this.items.map(item => item[idx])
          let headerEntry = this.availableHeaders[header]
          if (headerEntry) {
            if (typeof headerEntry.warning !== 'undefined') {
              let warning = headerEntry.warning(data, this.configuration)
              if(warning) {
                this.warnings.push(warning)
              }
            }
            if (typeof headerEntry.error !== 'undefined') {
              let error = headerEntry.error(data, this.configuration)
              if(error) {
                this.errors.push(error)
              }
            }
          }
        })

        if(this.errors.length === 0 && this.backendValidator) {
          this.backendValidator().then(data => {
            this.backendValidationResult = data
            this.dataValidated = true
            this.validationRunning = false
          })
        } else {
          this.dataValidated = true
          this.validationRunning = false
        }
      }
    },
    computed: {
      hasErrors() {
        if(this.errors.length > 0) {
          return true
        }
        if(this.backendValidationResult && this.backendValidationResult.errors.length > 0) {
          return true
        }
        return false
      }
    }
  }
</script>

<style lang="scss">
  #app ul.v-expansion-panel {
    box-shadow: none;
  }

  .warning-message {
    color: #e86856;
  }
</style>
