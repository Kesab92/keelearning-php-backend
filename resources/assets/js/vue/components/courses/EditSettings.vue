<template>
  <div v-if="courseData">
    <CourseToolbar
      :course="courseData"
      :is-saving="isSaving"
      :is-valid="isValid"
      @save="save"
      @updateVisibility="updateVisibility"
      @updateNewCourseNotification="updateNewCourseNotification"
    />
    <v-form v-model="isValid">
      <div class="py-4">
        <div class="mb-section px-4">
          <h4 class="sectionHeader">Wer ist für den Kurs verantwortlich?</h4>
          <admin-select
            v-model="courseData.managers"
            color="blue-grey lighten-2"
            label="Verantwortliche Administratoren"
            multiple
            outline
            placeholder="Kein verantwortlicher Administrator hinterlegt"
            persistent-hint
            :disabled="isReadonly"
            hint="Tragen Sie hier einen verantwortlichen Admin ein, der benachrichtigt werden soll, wenn Benutzer für einen Kurs freigeschaltet werden möchten."
          />
        </div>
        <div
          v-if="courseData.is_template"
          class="pb-section">
          <v-divider/>
          <div class="px-4">
            <h4 class="mt-section sectionHeader">Kurs automatisch wiederholen?</h4>
            <v-layout row wrap>
              <v-flex xs6>
                <v-select
                  v-model="courseData.is_repeating"
                  :items="repeatingTypes"
                  :disabled="isReadonly"
                  item-text="text"
                  item-value="value"
                  label="Vorlagentyp"
                  hide-details
                />
              </v-flex>
              <v-spacer class="ml-3"/>
            </v-layout>
            <v-layout row wrap>
              <v-flex xs6>
                <div
                  v-if="courseData.is_repeating"
                  class="my-4">
                  <div class="caption s-datePicker__label">Erster Start</div>
                  <DatePicker
                    class="s-datePicker"
                    v-model="courseData.available_from"
                    :value-type="datePickerOptions.internalFormat"
                    :disabled="isReadonly"
                    :format="datePickerOptions.displayFormat"/>
                </div>
                <v-layout
                  v-if="courseData.is_repeating"
                  row>
                  <v-text-field
                    v-model="courseData.repetition_interval"
                    label="Wiederholung nach"
                    type="number"
                    min="1"
                    hide-details
                    class="mb-3"
                    :disabled="isReadonly"
                    outline/>
                  <v-select
                    v-model="courseData.repetition_interval_type"
                    :items="intervals"
                    :disabled="isReadonly"
                    class="ml-2"
                    outline
                    item-text="text"
                    item-value="value"
                    label="Zeitraum"
                    hide-details
                  />
                </v-layout>
              </v-flex>
              <v-flex
                v-if="courseData.is_repeating"
                xs6>
                <v-alert
                  outline
                  type="info"
                  :value="showNextRepetitionDate"
                  class="ml-3">
                  <div
                    v-if="courseData.repetition_count > 0 && courseData.current_repetition_count >= courseData.repetition_count">
                    Dieser Kurs wurde bereits {{ courseData.current_repetition_count }} Mal erstellt und wird daher nicht
                    mehr automatisch wiederholt.
                  </div>
                  <div v-else>
                    <template v-if="courseData.latestRepeatedCourseCreatedAt">
                      Die nächste Wiederholung
                    </template>
                    <template v-else>
                      Der erste Kurs
                    </template>
                    startet am {{ nextRepetitionDate | date }}.<br>
                    <template v-if="timeLimitText">
                      Dann wird der Kurs "{{ courseData.title }}" aus dieser Vorlage erstellt und kann für {{ timeLimitText }}
                      abgelegt werden. Nach dieser Zeit ist es nicht mehr möglich, sich für den Kurs einzuschreiben.
                    </template>
                    <template v-else>
                      Dann wird der Kurs "{{ courseData.title }}" aus dieser Vorlage erstellt.
                    </template>
                    <br><br>
                    <a href="https://helpdesk.keelearning.de/de/articles/5570575-wiederholung-von-kursen" target="_blank">Mehr erfahren</a>
                  </div>
                </v-alert>
              </v-flex>
            </v-layout>
            <v-layout row wrap>
              <v-flex xs6>
                <v-text-field
                  v-if="courseData.is_repeating"
                  v-model="courseData.repetition_count"
                  label="Anzahl der Wiederholungen"
                  :placeholder="!courseData.repetition_count ? 'Unbegrenzt' : ''"
                  type="number"
                  min="1"
                  hide-details
                  :disabled="isReadonly"
                  outline/>
              </v-flex>
              <v-flex xs6>
                <v-layout
                  v-if="courseData.is_repeating"
                  row
                  class="ml-3">
                  <v-text-field
                    v-model="courseData.time_limit"
                    label="Bearbeitungszeit"
                    :placeholder="!courseData.time_limit ? 'Unbegrenzt' : ''"
                    type="number"
                    min="1"
                    hide-details
                    :disabled="isReadonly"
                    outline/>
                  <v-select
                    v-model="courseData.time_limit_type"
                    :items="intervals"
                    :disabled="isReadonly || !courseData.time_limit"
                    class="ml-2"
                    outline
                    item-text="text"
                    item-value="value"
                    label="Zeitraum"
                    hide-details
                  />
                </v-layout>
              </v-flex>
            </v-layout>
            <v-alert
              v-if="courseData.is_repeating"
              :value="true"
              class="mt-3"
              type="info">
              Der neu erstellte Kurs trägt den Kursnamen der Vorlage + aktuelle Woche/Jahr
            </v-alert>
          </div>
        </div>
        <template v-if="!courseData.is_template">
          <v-divider class="mb-section"/>
          <div class="mb-section px-4">
            <h4 class="sectionHeader">Kursdauer</h4>
            <v-layout class="mb-4" row wrap>
              <v-flex xs6>
                <v-select
                  v-model="courseData.duration_type"
                  :items="durationTypes"
                  :disabled="isReadonly"
                  item-text="text"
                  item-value="value"
                  label="Zeitliche Limitierung"
                  hide-details />
              </v-flex>
              <v-spacer class="ml-3"/>
            </v-layout>

            <v-layout
              row
              wrap>
              <v-flex xs6 class="mr-1">
                <div class="caption s-datePicker__label">Kurs Start</div>
                <DatePicker
                  class="s-datePicker"
                  v-model="courseData.available_from"
                  :value-type="datePickerOptions.internalFormat"
                  :disabled="isReadonly"
                  :format="datePickerOptions.displayFormat" />
              </v-flex>
              <v-flex
                v-show="courseData.duration_type == $constants.COURSES.DURATION_TYPES.FIXED">
                <div class="caption s-datePicker__label">Kurs Ende</div>
                <DatePicker
                  class="s-datePicker"
                  v-model="courseData.available_until"
                  :value-type="datePickerOptions.internalFormat"
                  :disabled="isReadonly"
                  :format="datePickerOptions.displayFormat" />
              </v-flex>
            </v-layout>
            <v-layout
              v-show="courseData.duration_type == $constants.COURSES.DURATION_TYPES.DYNAMIC"
              class="mt-4"
              row
              wrap>
              <v-flex xs6>
                <v-text-field
                  v-model="courseData.participation_duration"
                  :disabled="isReadonly"
                  label="Zeitlimit"
                  type="number"
                  min="1"
                  hide-details
                  class="mb-3 mr-2"
                  outline />
              </v-flex>
              <v-flex xs6>
                <v-select
                  v-model="courseData.participation_duration_type"
                  :items="participationDurationTypes"
                  :disabled="isReadonly"
                  item-text="text"
                  item-value="value"
                  label="Zeitraum"
                  outline
                  hide-details />
              </v-flex>
            </v-layout>
          </div>
        </template>
        <div
          v-if="courseData.parent"
          class="mb-section px-4">
          <v-alert
            outline
            type="info"
            :value="true">
            Dieser Kurs wurde am {{ courseData.created_at | date }} automatisch aus der Vorlage
            "{{ courseData.parent.title }}" erstellt.
          </v-alert>
        </div>
        <v-divider class="mb-section"/>
        <div class="mb-section px-4">
          <h4 class="sectionHeader">Sollen Administratoren und/oder Benutzer automatisch benachrichtigt werden?</h4>
          <p>Benachrichtigungen</p>
          <toggle
            :disabled="isDisabledCourseBeginEmail || isReadonly"
            v-model="courseData.send_new_course_notification"
            label="E-Mail zum Kursbeginn an mögliche Teilnehmer versenden">
            <template v-slot:append>
              <v-btn
                href="/mails?edit=NewCourseNotification"
                flat
                icon
                color="black"
                class="ma-0">
                <v-icon>settings</v-icon>
              </v-btn>
            </template>
          </toggle>
          <toggle
            v-model="courseData.send_passed_course_mail"
            label="Kursergebnis an Benutzer senden">
            <template v-slot:append>
              <v-btn
                href="/mails?edit=PassedCourse"
                flat
                icon
                color="black"
                class="ma-0">
                <v-icon>settings</v-icon>
              </v-btn>
            </template>
          </toggle>
          <toggle
            v-if="courseData.is_repeating"
            v-model="courseData.send_repetition_course_reminder"
            label="Kursverantwortliche vor Kurswiederholung informieren">
            <template v-slot:append>
              <v-btn
                href="/mails?edit=RepetitionCourseReminder"
                flat
                icon
                color="black"
                class="ma-0">
                <v-icon>settings</v-icon>
              </v-btn>
            </template>
          </toggle>
        </div>
        <v-divider class="mb-section"/>
        <Reminders class="mb-section px-4" :course="courseData"/>
        <v-divider class="mb-section"/>
        <div class="my-section px-4">
          <h4 class="sectionHeader">Vorschau für diesen Kurs aktivieren?</h4>
          <Toggle
            v-model="courseData.preview_enabled"
            :disabled="isDisabledPreviewSwitcher || isReadonly"
            label="Kursvorschau für Benutzer ohne Kurs-Zugriffsrechte"
            :hint="previewEnabledHint" />
          <tag-select
            v-if="courseData.preview_enabled"
            v-model="courseData.preview_tags"
            :invisible-items="courseData.tags"
            label="Vorschau für folgende Benutzergruppen"
            placeholder="Alle"
            :disabled="isReadonly"
            limit-to-tag-rights
            show-limited-tags
            multiple/>
          <v-layout
            v-if="courseData.preview_enabled"
            row>
            <v-flex shrink>
              <v-switch
                class="s-switch -dense"
                hide-details
                height="30"
                :disabled="isReadonly"
                v-model="showRequestAccessLink"/>
            </v-flex>
            <v-flex align-self-center>
              Bei Zugriffsanfrage auf externe URL verweisen
            </v-flex>
          </v-layout>
          <v-text-field
            v-if="showRequestAccessLink"
            v-model="courseData.request_access_link"
            label="URL für Zugriffsanfragen"
            persistent-hint
            hint="Diese URL wird geöffnet, wenn ein User Zugriff auf einen Kurs beantragen möchte."
            :rules="[$rules.url]"
            :disabled="isReadonly"
            placeholder="https://ihre.domain.com"
            outline/>
        </div>
        <v-divider class="mb-section"/>
        <div class="mt-section px-4">
          <h4 class="sectionHeader">Was soll passieren, wenn der Kurs bestanden wurde?</h4>
          <tag-select
            v-model="courseData.award_tags"
            :disabled="isReadonly"
            color="blue-grey lighten-2"
            label="Beim Bestehen folgende TAGs zuweisen"
            multiple
            outline
            limitToTagRights
            show-limited-tags
          />
          <tag-select
            v-model="courseData.retract_tags"
            :disabled="isReadonly"
            color="blue-grey lighten-2"
            label="Beim Bestehen folgende TAGs entfernen"
            multiple
            outline
            limitToTagRights
            show-limited-tags
          />
        </div>
      </div>
    </v-form>
  </div>
</template>

<script>
import ClickOutside from 'vue-click-outside'
import DatePicker from 'vue2-datepicker'
import 'vue2-datepicker/index.css'
import {isPast} from "date-fns"
import {mapGetters} from 'vuex'
import constants from '../../logic/constants'

import helpers from '../../logic/helpers'
import CourseToolbar from './CourseToolbar'
import TagSelect from '../partials/global/TagSelect'
import Reminders from './components/Reminders'
import AdminSelect from '../partials/global/AdminSelect'

const datePickerOptions = {
  displayFormat: 'DD.MM.YYYY',
  internalFormat: 'YYYY-MM-DD HH:mm:ss',

}

export default {
  props: ["course"],
  data() {
    return {
      datePickerOptions,
      courseData: null,
      showRequestAccessLink: false,
      isValid: false,
      repeatingTypes: [
        {
          value: 1,
          text: 'wiederholend'
        },
        {
          value: 0,
          text: 'nicht wiederholend'
        },
      ],
      durationTypes: [
        {
          value: constants.COURSES.DURATION_TYPES.FIXED,
          text: 'Start- und End-Datum',
        },
        {
          value: constants.COURSES.DURATION_TYPES.DYNAMIC,
          text: 'Zeitlimit ab Teilnahmebeginn',
        },
      ],
      participationDurationTypes: [
        {
          value: 0,
          text: 'Tage',
        },
        {
          value: 1,
          text: 'Wochen',
        },
        {
          value: 2,
          text: 'Monate',
        },
      ],
    }
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
      reminderEmails: 'courses/reminderEmails',
    }),
    isSaving: {
      get() {
        return this.$store.state.courses.isSaving
      },
      set(data) {
        this.$store.commit('courses/setIsSaving', data)
      },
    },
    isReadonly() {
      return !this.myRights['courses-edit']
    },
    isDisabledPreviewSwitcher() {
      if(this.courseData.has_individual_attendees || this.courseData.tags.length) {
        return false
      }
      return true
    },
    previewEnabledHint() {
      if(this.courseData.has_individual_attendees || this.courseData.tags.length) {
        return ''
      }
      return 'Dieses Feature kann nur bei einem Kurs mit TAG aktiviert werden.'
    },
    isDisabledCourseBeginEmail() {
      const courseAvailableDate = new Date(this.courseData.available_from)
      if(this.courseData.available_from && isPast(courseAvailableDate))
      {
        return true
      }
      else if(!this.courseData.available_from && this.courseData.visible){
        return true
      }
      return false
    },
    intervals() {
      return [
        {
          value: this.$constants.COURSES.INTERVAL_TYPES.WEEKLY,
          text: 'Wochen'
        },
        {
          value: this.$constants.COURSES.INTERVAL_TYPES.MONTHLY,
          text: 'Monate'
        },
      ]
    },
    timeLimitText() {
      if(!this.courseData.time_limit) {
        return ''
      }

      if(this.courseData.time_limit_type === this.$constants.COURSES.INTERVAL_TYPES.WEEKLY) {
        if(this.courseData.time_limit == 1) {
          return '1 Woche'
        } else {
          return `${this.courseData.time_limit} Wochen`
        }
      }
      if(this.courseData.time_limit_type === this.$constants.COURSES.INTERVAL_TYPES.MONTHLY) {
        if(this.courseData.time_limit == 1) {
          return '1 Monat'
        } else {
          return `${this.courseData.time_limit} Monate`
        }
      }
      return ''
    },
    nextRepetitionDate() {
      return helpers.nextRepetitionCourseDate(this.courseData)
    },
    showNextRepetitionDate() {
      return (
        this.courseData.is_repeating
        && this.courseData.available_from
        && this.courseData.repetition_interval
        && this.courseData.repetition_interval_type != null
      )
    }
  },
  watch: {
    course: {
      handler() {
        this.courseData = JSON.parse(JSON.stringify(this.course))
        if(this.courseData.repetition_count == 0) {
          this.courseData.repetition_count = null
        }
        if(this.courseData.time_limit == 0) {
          this.courseData.time_limit = null
        }
        this.showRequestAccessLink = !!this.courseData.request_access_link

        if(!this.courseData.tags.length && !this.courseData.has_individual_attendees) {
          this.courseData.preview_enabled = false
        }
      },
      immediate: true,
    },
    'courseData.is_repeating': function (newVal, oldVal){
      if (newVal && !this.courseData.repetition_interval) {
        this.courseData.repetition_interval = 12
        this.courseData.repetition_interval_type = this.$constants.COURSES.INTERVAL_TYPES.MONTHLY
      }
    },
    showRequestAccessLink: function (val){
      if (!val) {
        this.courseData.request_access_link = null
      }
    },
  },
  methods: {
    save() {
      if (this.isSaving || !this.isValid) {
        return
      }

      if(this.courseData.is_repeating && !this.courseData.available_from) {
        alert('Bitte geben Sie für wiederholende Kurse ein Startdatum an.')
        return
      }

      if(this.courseData.is_repeating && !this.courseData.repetition_interval) {
        alert('Bitte geben Sie ein gültiges Wiederholungs-Intervall an.')
        return
      }
      if(helpers.getFirstInvalidMail(this.reminderEmails.split(',').map(email => email.trim()))) {
        alert('Ungültige E-Mail')
        return
      }

      let dataToSave = {
        available_from: this.courseData.available_from,
        award_tags: this.courseData.award_tags,
        duration_type: this.courseData.duration_type,
        id: this.courseData.id,
        managers: this.courseData.managers,
        participation_duration: this.courseData.participation_duration,
        participation_duration_type: this.courseData.participation_duration_type,
        preview_enabled: this.courseData.preview_enabled,
        preview_tags: this.courseData.preview_tags,
        reminderEmails: this.reminderEmails,
        retract_tags: this.courseData.retract_tags,
        request_access_link: this.courseData.request_access_link,
        send_new_course_notification: this.courseData.send_new_course_notification,
        send_passed_course_mail: this.courseData.send_passed_course_mail,
        send_repetition_course_reminder: this.courseData.send_repetition_course_reminder,
        visible: this.courseData.visible,
      }

      if (this.courseData.is_template) {
        dataToSave.is_repeating = this.courseData.is_repeating
        dataToSave.repetition_count = this.courseData.repetition_count || null
        dataToSave.repetition_interval = this.courseData.repetition_interval
        dataToSave.repetition_interval_type = this.courseData.repetition_interval_type
        dataToSave.time_limit = this.courseData.time_limit || null
        dataToSave.time_limit_type = this.courseData.time_limit_type
      } else {
        dataToSave.available_until = this.courseData.available_until
      }

      this.$store.dispatch("courses/saveCourse", dataToSave).catch((error) => {
        if(error.response.data.message) {
          alert(error.response.data.message)
        } else {
          alert('Ein unbekannter Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.')
        }
      }).finally(() => {
        this.isSaving = false
      })
    },
    updateVisibility(visible) {
      this.courseData.visible = visible
    },
    updateNewCourseNotification(sendNotification) {
      this.courseData.send_new_course_notification = sendNotification
    },
  },
  components: {
    CourseToolbar,
    TagSelect,
    AdminSelect,
    DatePicker,
    Reminders,
  },
  directives: {
    ClickOutside,
  },
}
</script>

<style lang="scss" scoped>
#app .s-datePicker {
  width: 100%;
}

.s-datePicker__label {
  color: rgba(0, 0, 0, 0.54);
}

#app .s-switch {
  &.-dense {
    margin-top: 0;
    padding-top: 0;
  }
}
</style>
