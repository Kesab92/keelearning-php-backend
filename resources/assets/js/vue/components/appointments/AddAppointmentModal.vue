<template>
  <v-dialog
    max-width="600"
    persistent
    v-model="dialog">
    <v-form
      v-model="isValid"
      @submit.prevent="createAppointment">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Neuen Termin erstellen
        </v-card-title>
        <v-card-text>
          <v-text-field
            v-model.trim="name"
            autofocus
            label="Bezeichnung"
            required
            :rules="[
              $rules.required,
              $rules.minChars(3)
            ]"
            class="mb-3 mt-2"
            box />
          <tag-select
            v-if="!this.isFullAdmin"
            v-model="tags"
            color="blue-grey lighten-2"
            label="Benutzergruppen"
            multiple
            outline
            placeholder="Alle"
            limitToTagRights
          />
          <v-layout row style="gap: 5px;">
            <v-flex shrink>
              <DatePicker
                v-model="appointmentStartDate"
                label="Startdatum"
                :clearable="false"/>
            </v-flex>
            <v-flex>
              <v-text-field
                v-model="appointmentStartTime"
                mask="time"
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
                v-model="appointmentEndTime"
                mask="time"
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
        </v-card-text>
        <v-card-actions>
          <v-btn
            @click="closeModal"
            flat>
            Abbrechen
          </v-btn>
          <v-spacer />
          <v-btn
            :loading="isLoading"
          :disabled="isLoading || !isValid"
            color="primary"
            type="submit"
            flat>
            Termin erstellen
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
    <v-snackbar
      :color="snackbar.type"
      :top="true"
      v-model="snackbar.active"
    >
      {{ snackbar.message }}
    </v-snackbar>
  </v-dialog>
</template>
<script>
import {mapGetters} from "vuex"
import {format, isBefore, parse} from "date-fns"
import TagSelect from "../partials/global/TagSelect"
import DatePicker from "../partials/global/Datepicker"

export default {
  props: ['value'],
  data() {
    return {
      isValid: false,
      isLoading: false,
      name: '',
      tags: [],
      appointmentStartDate: null,
      appointmentStartTime: null,
      appointmentEndTime: null,
      snackbar: {
        active: false,
        type: null,
        message: null,
      },
    }
  },
  created() {
    this.reset()
  },
  computed: {
    ...mapGetters({
      isFullAdmin: 'app/isFullAdmin',
    }),
    dialog: {
      set(value) {
        this.$emit('input', value)
      },
      get() {
        return this.value
      }
    }
  },
  methods: {
    reset() {
      this.appointmentStartDate = format(new Date(), 'yyyy-MM-dd')
      this.appointmentStartTime = format(new Date(), 'HH:mm')
      this.appointmentEndTime = format(new Date(), 'HH:mm')
    },
    createAppointment() {
      if(this.isLoading) {
        return
      }
      if(!this.tags.length && !this.isFullAdmin) {
        this.handleSnackbar('error', 'Bitte wählen Sie mindestens einen TAG')
        return
      }
      
      const startTime = parse(this.appointmentStartTime, 'HH:mm', new Date())
      const endTime = parse(this.appointmentEndTime, 'HH:mm', new Date())

      if(isBefore(endTime, startTime)) {
        this.handleSnackbar('error', 'Die "Bis" Zeit muss nach der "Von" Zeit liegen')
        return
      }

      this.isLoading = true

      axios.post('/backend/api/v1/appointments', {
        name: this.name,
        tags: this.tags,
        start_date: `${this.appointmentStartDate} ${this.appointmentStartTime}`,
        end_date: `${this.appointmentStartDate} ${this.appointmentEndTime}`,
      }).then(response => {
        this.$router.push({
          name: 'appointments.edit.general',
          params: {
            appointmentId: response.data.appointment.id,
          },
        })
        this.$store.dispatch('appointments/loadAppointments')
        this.closeModal()
      }).catch(e => {
        console.log(e)
        alert('Der Termin konnte leider nicht erstellt werden')
      }).finally(() => {
        this.isLoading = false
      })
    },
    closeModal() {
      this.name = ''
      this.tags = []
      this.reset()

      this.dialog = false
    },
    handleSnackbar(type, message) {
      this.snackbar.active = true
      this.snackbar.type = type
      this.snackbar.message = message
    },
  },
  components: {
    TagSelect,
    DatePicker,
  },
}
</script>
