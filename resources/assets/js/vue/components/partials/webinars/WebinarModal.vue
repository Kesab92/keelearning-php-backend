<template>
  <span>
    <v-snackbar
      :top="true"
      color="success"
      v-model="successResponse"
    >
      Das Webinar wurde erfolgreich gespeichert.
      <template v-if="resetReminder">
        <br>
        Vor Beginn des Webinars wird erneut eine Benachrichtigung versendet.
      </template>
    </v-snackbar>
    <v-snackbar
      :top="true"
      color="error"
      v-model="errorResponse"
    >
      {{ message }}
    </v-snackbar>
    <v-dialog
      min-width="50%"
      max-width="90%"
      width="1000px"
      persistent
      scrollable
      v-model="isOpen"
    >
      <template slot="activator">
        <v-btn
          v-if="!webinarId"
          color="success"
          ripple
        >
          <v-icon
            dark
            left
          >
            add
          </v-icon>
          Neues Webinar
        </v-btn>
      </template>
      <v-card v-if="isOpen">
        <v-toolbar
          card
          color="primary"
          dark
        >
          <v-btn
            @click.native="isOpen = false"
            dark
            icon
          >
            <v-icon>close</v-icon>
          </v-btn>
          <v-toolbar-title v-if="webinarId || webinar.id">
            Webinar bearbeiten
          </v-toolbar-title>
          <v-toolbar-title v-else>
            Webinar erstellen
          </v-toolbar-title>
        </v-toolbar>
        <v-tabs
          v-if="webinarId || webinar.id"
          color="primary"
          dark
          grow
          v-model="activeTab"
        >
          <v-tab>
            Einstellungen
          </v-tab>
          <v-tab>
            Teilnehmer
            <template v-if="participantsCount !== null">
              ({{ participantsCount }})
            </template>
            <v-progress-circular
              v-else
              indeterminate
              :size="15"
              :width="2"
              class="ml-1"
            />
          </v-tab>
          <v-tab>
            Aufnahmen
          </v-tab>
        </v-tabs>
        <v-progress-linear
          height="4"
          indeterminate
          class="mt-0"
          :style="{
            opacity: isLoading ? 1 : 0,
          }"
        />
        <v-card-text
          :class="{
            disabled: isLoading,
          }"
        >
          <v-form
            lazy-validation
            ref="form"
          >
            <v-tabs-items v-model="activeTab">
              <v-tab-item>
                <WebinarSettings
                  v-model="webinar"
                  :tags="tags"
                />
              </v-tab-item>
              <v-tab-item>
                <WebinarUsers
                  v-if="webinar.id"
                  v-model="webinar.additional_users"
                />
              </v-tab-item>
              <v-tab-item>
                <WebinarRecordings
                  v-if="webinar.id"
                  :webinar-id="webinar.id"
                />
              </v-tab-item>
            </v-tabs-items>
          </v-form>
        </v-card-text>
        <v-divider />
        <v-card-actions style="flex-wrap: wrap">
          <v-flex xs12>
            <v-layout row>
              <v-flex grow>
                <v-btn
                  :disabled="isLoading || isSaving"
                  :loading="isSaving"
                  @click.native="save"
                  block
                  outline
                >
                  Speichern
                </v-btn>
              </v-flex>
              <v-flex shrink class="ml-2">
                <v-btn
                  v-if="webinar.id"
                  :disabled="isLoading || isSaving"
                  :loading="isDeleting"
                  block
                  outline
                  color="error"
                  @click.native="deleteWebinar"
                >
                  Webinar löschen
                </v-btn>
              </v-flex>
            </v-layout>
          </v-flex>
          <v-flex v-if="pendingLateReminders" xs12>
            <v-alert
              :value="true"
              type="info"
              outline
            >
              Neu hinzugefügte/bearbeitete User werden sofort benachrichtigt.
            </v-alert>
          </v-flex>
          <v-flex v-if="!isLoading && noModerator" xs12>
            <v-alert
              :value="true"
              type="info"
              outline
            >
              Für dieses Webinar ist noch kein Moderator hinterlegt.
              Bitte fügen Sie über den Teilnehmer-Tab mindestens einen hinzu.
            </v-alert>
          </v-flex>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </span>
</template>

<script>
import moment from 'moment'
import WebinarRecordings from './WebinarRecordings'
import WebinarSettings from './WebinarSettings'
import WebinarUsers from './WebinarUsers'

let cancelTokenSource

const webinarDefault = {
  id: null,
  topic: '',
  description: '',
  starts_at_date: null,
  starts_at_time: null,
  duration_minutes: null,
  send_reminder: false,
  reminder_sent_at: null,
  show_recordings: false,
  additional_users: [],
}

export default {
  props: {
    open: {
      required: true,
      type: Boolean,
    },
    tags: {
      required: true,
      type: Array,
    },
    webinarId: {
      default: null,
      required: false,
      type: Number,
    },
  },
  data() {
    return {
      activeTab: 0,
      errorResponse: false,
      isDeleting: false,
      internalParticipants: null,
      isLoading: false,
      isSaving: false,
      message: null,
      resetReminder: false,
      successResponse: false,
      webinar: {...webinarDefault},
    }
  },
  watch: {
    'webinar.additional_users': 'fetchParticipantCount',
    'webinar.tag_ids': 'fetchParticipantCount',
    open() {
      if (this.open) {
        this.activeTab = 0
        if (this.webinarId) {
          this.loadData()
        } else {
          this.webinar = {...webinarDefault}
        }
      }
    },
  },
  computed: {
    noModerator() {
      return !this.webinar.additional_users.filter(aU => aU.role === this.$constants.WEBINARS.ROLE_MODERATOR).length
    },
    participantsCount() {
      if (this.internalParticipants === null) {
        return null
      }
      return this.webinar.additional_users.filter(aU => !!aU.external).length + this.internalParticipants
    },
    /**
     * If webinar reminders have already been sent and new users were added afterwards,
     * we will have to immediately send reminders to the newly added users.
     *
     * @return boolean
     */
    pendingLateReminders() {
      if (!this.webinar.send_reminder || !this.webinar.reminder_sent_at) {
        return false
      }
      if (
        moment(`${this.webinar.starts_at_date} ${this.webinar.starts_at_time}`)
          .subtract(15, 'minutes')
          .isAfter()
      ) {
        return false
      }
      return !!this.webinar.additional_users.filter(aU => !aU.id || aU.dirty).length
    },
    isOpen: {
      get() {
        return this.open
      },
      set(open) {
        this.$emit('setOpen', open)
      },
    },
  },
  methods: {
    deleteWebinar() {
      if (!confirm('Wollen Sie dieses Webinar und alle zugehörigen Daten unwiderruflich löschen?')) {
        return
      }
      this.isLoading = true
      this.isDeleting = true
      axios.post(`/backend/api/v1/webinars/${this.webinar.id}/delete`)
        .then(response => {
          if (response.data.success) {
            this.$emit('delete', this.webinar.id)
            this.isOpen = false
          } else {
            this.message = response.data.error
            this.errorResponse = true
          }
          this.isLoading = false
          this.isDeleting = false
        }).catch(() => {
          this.isDeleting = false
          this.isLoading = false
          this.message = 'Fehler beim Löschen der Webinar-Daten'
          this.errorResponse = true
        })
    },
    fetchParticipantCount() {
      if (cancelTokenSource) {
        cancelTokenSource.cancel()
      }
      this.internalParticipants = null
      let tagIds = this.webinar.tag_ids || []
      let userIds = this.webinar.additional_users.filter(aU => !!aU.user_id).map(aU => aU.user_id)
      cancelTokenSource = axios.CancelToken.source()
      axios.get(`/backend/api/v1/search/tags-user-count/${tagIds.join(',')}`, {
        cancelToken: cancelTokenSource.token,
        params: {
          user_ids: userIds,
        },
      }).then(response => {
        if(response instanceof axios.Cancel) {
          return
        }

        this.internalParticipants = response.data.users_count
      }).catch(thrown => {
        if (!axios.isCancel(thrown)) {
          this.message = 'Es ist ein unerwarteter Fehler aufgetreten.'
          this.errorResponse = true
        }
      })
    },
    loadData() {
      if (this.isLoading) {
        return
      }
      this.isLoading = true
      let webinarId = this.webinarId
      if(this.webinar.id) {
        webinarId = this.webinar.id
      }
      axios.get(`/backend/api/v1/webinars/${webinarId}`).then(response => {
        if (response.data.success) {
          this.webinar = response.data.webinar
          let additionalUsers = [...response.data.webinar.additional_users]
          let startsAtDate = moment(this.webinar.starts_at)
          this.webinar.starts_at_date = startsAtDate.isValid() ? startsAtDate.format('YYYY-MM-DD') : null
          this.webinar.starts_at_time = startsAtDate.isValid() ? startsAtDate.format('HH:mm') : null
          this.webinar.additional_users = []
          this.$nextTick(() => {
            this.$set(this.webinar, 'additional_users', additionalUsers)
          })
        } else {
          this.message = response.data.error
          this.errorResponse = true
          this.isOpen = false
        }
        this.isLoading = false
      }).catch(() => {
        this.isLoading = false
        this.message = 'Fehler beim Laden der Webinar-Daten'
        this.errorResponse = true
        this.isOpen = false
      })
    },
    save() {
      if (this.isLoading || this.isSaving) {
        return
      }
      this.isSaving = true
      let apiUrl = '/backend/api/v1/webinars/'
      if (this.webinar.id) {
        apiUrl += this.webinar.id
      }
      if(!this.webinar.starts_at_date || !this.webinar.starts_at_time) {
        this.message = 'Bitte wählen Sie einen Startzeitpunkt'
        this.errorResponse = true
        this.isSaving = false
        return
      }
      this.webinar.starts_at = `${this.webinar.starts_at_date} ${this.webinar.starts_at_time}:00`
      axios.post(apiUrl, this.webinar).then(response => {
        if (response.data.success) {
          this.resetReminder = response.data.reset_reminder
          this.successResponse = true
          this.$emit('update', response.data.webinar)
          if (!this.webinar.id) {
            this.webinar.id = response.data.webinar.id
            this.activeTab = 1
          } else {
            this.loadData()
          }
        } else {
          this.message = response.data.error
          this.errorResponse = true
        }
        this.isSaving = false
      }).catch(error => {
        this.message = 'Es ist ein unerwarteter Fehler aufgetreten.'
        this.errorResponse = true
        this.isSaving = false
      })
    },
  },
  components: {
    WebinarRecordings,
    WebinarSettings,
    WebinarUsers,
  },
}
</script>

<style lang="scss" scoped>
.disabled {
  cursor: not-allowed;
  opacity: 0.5;
  pointer-events: none;
}
</style>
