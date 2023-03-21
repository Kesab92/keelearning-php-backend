<template>
  <v-container grid-list-md>
    <v-snackbar
      v-model="snackbarOptions.enabled"
      :color="snackbarOptions.color"
      :top="true">
      {{ snackbarOptions.text }}
    </v-snackbar>

    <v-toolbar color="white">
      <v-layout row wrap>
        <v-flex class="heading" xs6>
          <a :href="'/tests/' + test">
            <v-btn flat icon>
              <v-icon>arrow_back</v-icon>
            </v-btn>
          </a>
          <v-toolbar-title>Erinnerungen für Test:
            <template v-if="!loading">{{ testInformation.name }}</template>
          </v-toolbar-title>
        </v-flex>
        <v-flex class="heading" xs6>
          <v-toolbar-title v-if="!loading && testInformation.active_until">
            Test endet am: {{ testInformation.active_until | date }}
          </v-toolbar-title>
        </v-flex>
      </v-layout>
    </v-toolbar>
    <v-layout row wrap>
      <v-flex md12 lg6>
        <v-card>
          <div
            v-if="loading"
            class="pa-3 text-xs-center"
          >
            <v-progress-circular
              indeterminate
              color="primary"
            />
          </div>
          <template v-else>
            <v-toolbar flat>
              <v-toolbar-title>
                Benutzer
              </v-toolbar-title>
              <v-spacer />
              <v-btn
                color="success"
                icon
                @click="addUserReminder"
              >
                <v-icon>add</v-icon>
              </v-btn>
            </v-toolbar>
            <p class="pa-3">
              Stellen Sie hier ein, in welchen Abständen an die Teilnehmer, die den Test noch nicht absolviert haben, eine Erinnerungsmail verschickt werden soll. Den Text zur Erinnerungsmail können Sie hier bearbeiten:
              <a href="/mails?edit=TestReminder">E-Mail-Vorlagen</a>
            </p>
            <v-list
              v-if="userReminders.length"
            >
              <v-list-tile
                v-for="entry in userReminders"
                :key="entry.id"
              >
                <v-edit-dialog>
                  Erinnerung {{ entry.days }} Tage vorher
                  <template v-slot:input>
                    <v-text-field
                      v-model="entry.days"
                      type="number"
                      min="0"
                      step="1"
                    />
                  </template>
                </v-edit-dialog>
                <v-list-tile-action>
                  <v-btn
                    @click="remove(entry.id)"
                    color="error"
                    flat
                    icon
                    ripple
                    class="offset-deletebutton"
                  >
                    <v-icon>delete_outline</v-icon>
                  </v-btn>
                </v-list-tile-action>
              </v-list-tile>
            </v-list>
          </template>
        </v-card>
      </v-flex>
      <v-flex md12 lg6>
        <v-card>
          <div
            v-if="loading"
            class="pa-3 text-xs-center"
          >
            <v-progress-circular
              indeterminate
              color="primary"
            />
          </div>
          <template v-else>
            <v-toolbar flat>
              <v-toolbar-title>
                Ergebnisse
              </v-toolbar-title>
              <v-spacer />
              <v-btn
                color="success"
                icon
                @click="addResultsReminder"
              >
                <v-icon>add</v-icon>
              </v-btn>
            </v-toolbar>
            <p class="pa-3">
              Hinterlegen Sie hier eine oder mehrere Personen, die x Tage vor Ablauf des Tests per E-Mail darüber benachrichtigt werden sollen, welcher Teilnehmer bereits bestanden hat und wer nicht.
            </p>
            <v-list
              v-if="resultReminders.length"
              two-line
            >
              <v-list-tile
                v-for="entry in resultReminders"
                :key="entry.id"
              >
                <v-list-tile-content>
                  <v-list-tile-title class="s-reminderEntry">
                    <v-edit-dialog>
                      Erinnerung {{ entry.days }} Tage vorher
                      <template v-slot:input>
                        <v-text-field
                          v-model="entry.days"
                          type="number"
                          min="0"
                          step="1"
                        />
                      </template>
                    </v-edit-dialog>
                    <v-edit-dialog>
                      <template v-if="entry.email">
                        {{ entry.email }}
                      </template>
                      <span
                        v-else
                        class="font-italic"
                      >
                        Mailadresse
                      </span>
                      <template v-slot:input>
                        <v-text-field
                          v-model="entry.email"
                          type="email"
                        />
                      </template>
                    </v-edit-dialog>
                  </v-list-tile-title>
                  <v-list-tile-sub-title v-if="entry.user_name">
                    Erstellt von {{ entry.user_name }}
                  </v-list-tile-sub-title>
                </v-list-tile-content>
                <v-list-tile-action class="s-justifyStart">
                  <v-btn
                    @click="remove(entry.id)"
                    color="error"
                    flat
                    icon
                    ripple
                    class="offset-deletebutton"
                  >
                    <v-icon>delete_outline</v-icon>
                  </v-btn>
                </v-list-tile-action>
              </v-list-tile>
            </v-list>
          </template>
        </v-card>
      </v-flex>
    </v-layout>
    <v-flex md12 lg6>
      <v-btn
        :disabled="loading || saving"
        :loading="loading || saving"
        @click.prevent="save"
        color="success">
        Speichern
      </v-btn>
    </v-flex>
  </v-container>
</template>

<script>
  export default {
    props: {
      test: {
        required: true,
        type: Number,
      },
    },
    data() {
      return {
        userHeaders: ['days'],
        resultHeaders: ['days', 'email'],
        reminders: [],
        snackbarOptions: {
          color: 'success',
          text: '',
          enabled: false
        },
        loading: false,
        saving: false,
        testInformation: null
      }
    },
    created() {
      this.loadReminders()
    },
    computed: {
      userReminders() {
        return this.reminders.filter(item => item.type === 0)
      },
      resultReminders() {
        return this.reminders.filter(item => item.type === 1)
      },
    },
    methods: {
      save() {
        if (this.loading || this.saving) {
          return
        }
        this.saving = true
        axios.post('/backend/api/v1/tests/' + this.test + '/reminders', {reminders: this.reminders}).then(response => {
          if (response.data.success) {
            this.handleResponse('Die Erinnerungen wurden erfolgreich gespeichert', 'success')
            this.loadReminders()
          }
          this.saving = false
        }).catch(err => {
          if (err && err.response && err.response.data && err.response.data.message) {
            this.handleResponse(err.response.data.message, 'error')
          } else {
            this.handleResponse(err, 'error')
          }
          this.saving = false
        })
      },
      handleResponse(text, color) {
        this.snackbarOptions.enabled = true
        this.snackbarOptions.color = color
        this.snackbarOptions.text = text
      },
      remove(id) {
        this.reminders = this.reminders.filter(item => item.id !== id)
      },
      addUserReminder() {
        this.reminders.push({
          id: 'TEMP_' + this.reminders.length,
          days: 0,
          type: 0,
        })
      },
      addResultsReminder() {
        this.reminders.push({
          id: 'TEMP_' + this.reminders.length,
          days: 0,
          email: '',
          type: 1,
        })
      },
      loadReminders() {
        this.loading = true
        axios.get('/backend/api/v1/tests/' + this.test + '/reminders').then(response => {
          if (response.data.success) {
            this.reminders = response.data.data.reminders
            this.testInformation = response.data.data.test
          }
          this.loading = false
        }).catch(err => {
          this.handleResponse(err, 'error')
        })
      }
    }
  }
</script>

<style lang="scss" scoped>
.offset-deletebutton {
  margin-left: 18px !important;
}

#app p.pa-3 {
  margin-bottom: 0px;
}

.heading {
  display: flex;
  line-height: 50px;
}

#app .heading div.v-toolbar__title {
  margin-left: 20px;
}

#app .s-reminderEntry {
  display: flex;
  overflow: visible;
}

.s-justifyStart {
  justify-content: flex-start !important;
}
</style>
