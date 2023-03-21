<template>
  <div v-if="appointmentData">
    <AppointmentToolbar
      :appointment-data="appointmentData"
      :is-saving="isSaving"
      :is-valid="isValid"
      @save="saveAction"
      @publish="publishAction"
    />
    <v-form v-model="isValid">
      <div class="pa-4">
        <v-layout
          row
          class="mb-2">
          <v-flex xs6>
            <translated-input
              v-model.trim="appointmentData.name"
              :translations="appointmentData.translations"
              :readOnly="isReadonly"
              :rules="[$rules.minChars(3)]"
              attribute="name"
              class="mb-2"
              label="Bezeichnung"/>

            <h4 class="sectionHeader">Was für einen Termin möchten Sie erstellen?</h4>

            <v-select
              v-model="appointmentData.type"
              :items="appointmentTypes"
              :disabled="isReadonly"
              required
              label="Termintyp"
              outline
            />
          </v-flex>

          <v-flex xs6 class="ml-4">
            <ImageUploader
              :current-image="appointmentData.cover_image_url"
              name="Coverbild"
              width="100%"
              height="auto"
              :url="`/backend/api/v1/appointments/${appointmentData.id}/cover`"
              :isReadOnly="isReadonly"
              @newImage="handleNewImage"/>
          </v-flex>
        </v-layout>

        <TextEditor
          attribute="description"
          placeholder="Beschreibungstext des Termins"
          :disabled="isReadonly"
          v-model="appointmentData.description" />

        <h4 class="sectionHeader mt-4">Wann findet der Termin statt?</h4>

        <v-layout row style="gap: 5px;">
          <v-flex shrink>
            <DatePicker
              v-model="startDate"
              :disabled="isReadonly"
              label="Startdatum"
              :clearable="false"/>
          </v-flex>
          <v-flex>
            <v-text-field
              v-model="startTime"
              mask="time"
              :disabled="isReadonly"
              :rules="[
                $rules.required,
                $rules.time,
              ]"
              label="Von"
              return-masked-value
              outline
              required
            />
          </v-flex>
          <v-flex>
            <v-text-field
              v-model="endTime"
              mask="time"
              :disabled="isReadonly"
              :rules="[
                $rules.required,
                $rules.time,
                ]"
              label="Bis"
              return-masked-value
              outline
              required
            />
          </v-flex>
        </v-layout>

        <Toggle
          v-model="appointmentData.has_reminder"
          :disabled="isReadonly"
          label="Erinnerung"/>

        <v-layout
          v-if="appointmentData.has_reminder"
          row
          style="gap: 5px;">
          <v-flex xs4>
            <v-text-field
              v-model.number="appointmentData.reminder_time"
              :disabled="isReadonly"
              type="number"
              min="0"
              step="1"
              label="Vor Termin"
              outline
            />
          </v-flex>
          <v-flex xs4>
            <v-select
              v-model="appointmentData.reminder_unit_type"
              :items="reminderUnitTypes"
              :disabled="isReadonly"
              required
              label="Einheit"
              outline
            />
          </v-flex>
        </v-layout>

        <v-text-field
          v-model.trim="appointmentData.location"
          :disabled="isReadonly"
          label="Ort / Meeting URL"
          outline
        />

        <h4 class="sectionHeader">Wann soll dieser Termin sichtbar sein?</h4>

        <PublishedAtInput
          :published-at.sync="appointmentData.published_at"
          :publish-type.sync="publishType"
          :isReadonly="isReadonly"
        />
        Benachrichtigung
        <v-btn
          href="/mails?edit=NewAppointment"
          flat
          icon
          color="black"
          class="ma-0">
          <v-icon>settings</v-icon>
        </v-btn>
        <br>
        <v-alert
          type="success"
          outline
          :value="true"
          v-if="appointmentData.last_notification_sent_at"
        >
          Benachrichtigung wurde am {{ appointmentData.last_notification_sent_at | dateTime }} versendet.
        </v-alert>
        <template v-if="!appointmentData.is_cancelled">
          <v-btn
            v-if="canNotifyNow"
            @click="notify"
            color="primary"
            :isReadonly="isReadonly"
            class="ml-0">
            Benachrichtigung jetzt senden
          </v-btn>
          <Toggle
            v-else
            v-model="appointmentData.send_notification"
            :disabled="isReadonly"
            label="Benachrichtigung vormerken"/>
        </template>
      </div>
    </v-form>
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import {format, parse, startOfDay, isPast, isBefore} from 'date-fns'
import DatePicker from "../partials/global/Datepicker"
import constants from "../../logic/constants"
import AppointmentToolbar from "./AppointmentToolbar"
import ImageUploader from "../partials/global/ImageUploader"
import TextEditor from '../partials/global/TextEditor'
import PublishedAtInput from "../partials/global/PublishedAtInput";

export default {
  props: ["appointment"],
  data() {
    return {
      appointmentData: null,
      startDate: null,
      startTime: null,
      endTime: null,
      isSaving: false,
      isValid: false,
      publishType: null,
      appointmentTypes: [
        {
          text: 'Präsenztermin',
          value: constants.APPOINTMENTS.TYPE_IN_PERSON,
        },
        {
          text: 'Online',
          value: constants.APPOINTMENTS.TYPE_ONLINE,
        },
      ],
      reminderUnitTypes: [
        {
          text: 'Minuten',
          value: constants.APPOINTMENTS.REMINDER_TIME_UNIT_MINUTES,
        },
        {
          text: 'Stunden',
          value: constants.APPOINTMENTS.REMINDER_TIME_UNIT_HOURS,
        },
        {
          text: 'Tage',
          value: constants.APPOINTMENTS.REMINDER_TIME_UNIT_DAYS,
        },
      ],
    }
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    canNotifyNow() {
      if(this.appointmentData.is_draft) {
        return false
      }
      const publishedAt = parse(this.appointmentData.published_at, 'yyyy-MM-dd', new Date())
      if(this.appointmentData.published_at && !isPast(publishedAt)) {
        return false
      }
      return true
    },
    isReadonly() {
      return !this.myRights['appointments-edit']
    },
  },
  watch: {
    appointment: {
      handler() {
        this.appointmentData = JSON.parse(JSON.stringify(this.appointment))
        this.startDate = format(parse(this.appointmentData.start_date, 'yyyy-MM-dd HH:mm:ss', new Date()), 'yyyy-MM-dd')
        this.startTime = format(parse(this.appointmentData.start_date, 'yyyy-MM-dd HH:mm:ss', new Date()), 'HH:mm')
        this.endTime = format(parse(this.appointmentData.end_date, 'yyyy-MM-dd HH:mm:ss', new Date()), 'HH:mm')

        if(!this.appointmentData.published_at) {
          this.publishType = this.$constants.PUBLISHED_AT_TYPES.IMMEDIATELY
        } else {
          this.publishType = this.$constants.PUBLISHED_AT_TYPES.PLANNED
          this.appointmentData.published_at = format(parse(this.appointmentData.published_at, 'yyyy-MM-dd HH:mm:ss', new Date()), 'yyyy-MM-dd')
        }
      },
      immediate: true,
    },
    'appointmentData.has_reminder': {
      handler() {
        if(this.appointmentData.has_reminder) {
          if(!this.appointmentData.reminder_time) {
            this.appointmentData.reminder_time = 15
          }
          if(!this.appointmentData.reminder_unit_type) {
            this.appointmentData.reminder_unit_type = constants.APPOINTMENTS.REMINDER_TIME_UNIT_MINUTES
          }
        }
      },
      immediate: true,
    },
  },
  methods: {
    handleNewImage(image) {
      this.appointmentData.cover_image_url = image
    },
    validate() {
      const publishedAt = startOfDay(parse(this.appointmentData.published_at, 'yyyy-MM-dd', new Date()))
      const startDate = parse(this.startDate, 'yyyy-MM-dd', new Date())
      const startTime = parse(this.startTime, 'HH:mm', new Date())
      const endTime = parse(this.endTime, 'HH:mm', new Date())

      if(this.publishType === this.$constants.PUBLISHED_AT_TYPES.PLANNED && !this.appointmentData.published_at) {
        alert('Bitte setzen Sie ein Veröffentlichungsdatum.')
        return false
      }

      if(this.publishType === this.$constants.PUBLISHED_AT_TYPES.PLANNED && this.appointmentData.published_at && publishedAt > startDate) {
        alert('Die Veröffentlichung muss vor dem Startdatum liegen!')
        return false
      }

      if(this.appointmentData.has_reminder && !this.appointmentData.reminder_time) {
        alert('Bitte legen Sie fest, wann die Nutzer an den Termin erinnert werden sollen.')
        return false
      }

      if(this.appointmentData.has_reminder && !this.appointmentData.reminder_unit_type) {
        alert('Bitte legen Sie fest, wann die Nutzer an den Termin erinnert werden sollen.')
        return false
      }

      if(isBefore(endTime, startTime)) {
        alert('Die "Bis" Zeit muss nach der "Von" Zeit liegen')
        return false
      }

      return true
    },
    publishAction() {
      if (this.validate()) {
        this.appointmentData.is_draft = false
        this.save()
      }
    },
    saveAction() {
      if (this.validate()) {
        this.save()
      }
    },
    save() {
      if (this.isSaving) {
        return
      }

      if(this.publishType === this.$constants.PUBLISHED_AT_TYPES.IMMEDIATELY) {
        this.appointmentData.published_at = null
      }

      this.isSaving = true
      return this.$store.dispatch("appointments/saveAppointment", {
        id: this.appointmentData.id,
        name: this.appointmentData.name,
        is_draft: this.appointmentData.is_draft,
        description: this.appointmentData.description,
        type: this.appointmentData.type,
        cover_image_url: this.appointmentData.cover_image_url,
        has_reminder: this.appointmentData.has_reminder,
        reminder_time: this.appointmentData.reminder_time,
        reminder_unit_type: this.appointmentData.reminder_unit_type,
        location: this.appointmentData.location,
        published_at: this.appointmentData.published_at,
        start_date: `${this.startDate} ${this.startTime}`,
        end_date: `${this.startDate} ${this.endTime}`,
        send_notification: this.appointmentData.send_notification,
      }).catch(() => {
        alert('Beim Speichern ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.')
      }).finally(() => {
        this.isSaving = false
      })
    },
    async notify() {
      if (!this.validate()) {
        return
      }

      await this.save()

      if (this.isSaving) {
        return
      }

      this.isSaving = true
      this.$store.dispatch("appointments/notifyAboutAppointment", {
        id: this.appointmentData.id,
      }).catch(() => {
        alert('Beim Speichern ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.')
      }).finally(() => {
        this.isSaving = false
      })
    },
  },
  components: {
    PublishedAtInput,
    ImageUploader,
    AppointmentToolbar,
    DatePicker,
    TextEditor,
  },
}
</script>
