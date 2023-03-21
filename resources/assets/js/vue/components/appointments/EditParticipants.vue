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
        <h4 class="sectionHeader">Wer soll alles an dem Termin teilnehmen?</h4>
        <tag-select
          v-model="appointmentData.tags"
          color="blue-grey lighten-2"
          label="Benutzergruppen"
          multiple
          outline
          placeholder="Alle"
          :disabled="isReadonly"
          :limitToTagRights="true"
          show-limited-tags
        />
      </div>
    </v-form>
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import DatePicker from "../partials/global/Datepicker"
import AppointmentToolbar from "./AppointmentToolbar"
import ImageUploader from "../partials/global/ImageUploader"
import TagSelect from "../partials/global/TagSelect"
import {format, parse} from "date-fns"

export default {
  props: ["appointment"],
  data() {
    return {
      appointmentData: null,
      isSaving: false,
      isValid: false,
    }
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    isReadonly() {
      return !this.myRights['appointments-edit']
    },
  },
  watch: {
    appointment: {
      handler() {
        this.appointmentData = JSON.parse(JSON.stringify(this.appointment))

        if(this.appointmentData.published_at) {
          this.appointmentData.published_at = format(parse(this.appointmentData.published_at, 'yyyy-MM-dd HH:mm:ss', new Date()), 'yyyy-MM-dd')
        }
      },
      immediate: true,
    },
  },
  methods: {
    publishAction() {
      this.appointmentData.is_draft = false
      this.save()
    },
    saveAction() {
      this.save()
    },
    save() {
      if (this.isSaving) {
        return
      }

      this.isSaving = true
      this.$store.dispatch("appointments/saveAppointment", {
        id: this.appointmentData.id,
        is_draft: this.appointmentData.is_draft,
        tags: this.appointmentData.tags,
      }).catch(() => {
        alert('Beim Speichern ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.')
      }).finally(() => {
        this.isSaving = false
      })
    },
  },
  components: {
    ImageUploader,
    AppointmentToolbar,
    DatePicker,
    TagSelect,
  },
}
</script>
