<template>
  <div>
    <details-sidebar-toolbar>
      <template
        v-if="appointmentData"
        v-slot:default>
        <v-btn
          :loading="isSaving"
          :disabled="isReadonly || !isValid"
          color="primary"
          @click="$emit('save')"
        >
          {{ saveButtonText }}
        </v-btn>
        <v-btn
          v-if="appointmentData.is_draft"
          :loading="isSaving"
          :disabled="isReadonly || !isValid"
          @click="$emit('publish')"
        >
          {{ publishButtonText }}
        </v-btn>

        <v-spacer/>

        <v-menu offset-x offset-y>
          <v-btn
            slot="activator"
            flat
          >
            Aktionen
            <v-icon right>arrow_drop_down</v-icon>
          </v-btn>
          <v-list>
            <v-list-tile
              v-for="(action, index) in actions"
              :key="`appointment-action-${index}`"
              :disabled="isReadonly"
              @click="doAction(action)"
            >
              <v-list-tile-title>{{ action.title }}</v-list-tile-title>
            </v-list-tile>
          </v-list>
        </v-menu>
      </template>
    </details-sidebar-toolbar>
    <v-alert
      outline
      type="info"
      color="grey"
      class="mb-4"
      :value="appointmentData.is_cancelled">
      Dieser Termin wurde abgesagt.
    </v-alert>
    <DeleteDialog
      v-model="deleteDialogOpen"
      :deletion-url="`/backend/api/v1/appointments/${appointmentData.id}`"
      :dependency-url="`/backend/api/v1/appointments/${appointmentData.id}/delete-information`"
      :entry-name="appointmentData.name"
      :redirect-url="afterDeletionRedirectURL"
      type-label="Termine"
      @deleted="handleAppointmentDeleted">
    </DeleteDialog>
    <CancelDialog
      v-model="cancelDialogOpen"
      :appointment="appointmentData"
      />
  </div>
</template>

<script>

import {mapGetters} from "vuex"
import format from "date-fns/format"
import parse from "date-fns/parse"
import DeleteDialog from "../partials/global/DeleteDialog"
import CancelDialog from "./CancelDialog"

export default {
  props: {
    appointmentData: {
      type: Object,
      required: true,
    },
    isValid: {
      type: Boolean,
      required: false,
      default: true,
    },
    isSaving: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data () {
    return {
      deleteDialogOpen: false,
      cancelDialogOpen: false,
    }
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    actions() {
      const actions = [
        {
          name: 'delete',
          title: 'Löschen',
        },
      ]

      if(!this.appointmentData.is_draft) {
        actions.push({
          name: 'convert-to-draft',
          title: 'Veröffentlichung zurücknehmen',
        })
      }

      if (!this.appointmentData.is_draft && !this.appointmentData.is_cancelled) {
        actions.push({
          name: 'cancel',
          title: 'Absagen',
        })
      }

      return actions
    },
    isReadonly() {
      return !this.myRights['appointments-edit']
    },
    afterDeletionRedirectURL() {
      return "/appointments#/appointments"
    },
    saveButtonText() {
      if(this.appointmentData.is_draft) {
        return 'Entwurf speichern'
      }
      return `Speichern`
    },
    publishButtonText() {
      if(!this.appointmentData.published_at) {
        return 'Veröffentlichen'
      }
      return `Am ${format(parse(this.appointmentData.published_at, 'yyyy-MM-dd', new Date()), 'dd.MM.yyyy')} veröffentlichen`
    },
  },
  methods: {
    doAction(action) {
      switch (action.name) {
        case 'delete':
          this.deleteDialogOpen = true
          break
        case 'convert-to-draft':
          this.convertToDraft()
          break
        case 'cancel':
          this.cancelDialogOpen = true
          break
      }
    },
    handleAppointmentDeleted() {
      this.$store.commit("appointments/deleteAppointment", this.appointmentData.id)
      this.$store.dispatch("appointments/loadAppointments")
    },
    convertToDraft() {
      if (this.isSaving) {
        return
      }
      this.$store.dispatch("appointments/convertAppointmentToDraft", {
        id: this.appointmentData.id,
      })
    },
  },
  components: {
    CancelDialog,
    DeleteDialog,
  },
}
</script>
