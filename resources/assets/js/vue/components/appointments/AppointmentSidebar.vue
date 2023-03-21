<template>
  <details-sidebar
    :root-url="{
      name: 'appointments.index',
    }"
    :drawer-open="typeof $route.params.appointmentId !== 'undefined'"
    data-action="appointments/loadAppointment"
    :data-getter="(params) => $store.getters['appointments/appointment'](params.appointmentId)"
    :data-params="{appointmentId: $route.params.appointmentId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: appointment, refresh }">
      <router-view
        :appointment="appointment"
        @refresh="refresh"
      />
    </template>
    <template v-slot:headerTitle="{ data: appointment }">
      {{ appointment.name }}
      <div class="blue--text text--darken-3 body-1">{{ appointment.start_date | date }} - {{ appointment.start_date | time }} bis {{ appointment.end_date | time }} Uhr</div>
    </template>
    <template v-slot:headerExtension="{ data: appointment }">
      <DraftLabel
        class="mb-2"
        :is-draft="!!appointment.is_draft"/>
      Typ: {{ getTypeText(appointment.type) }}<br>
      Bearbeitet: {{ appointment.updated_at | date }}<template v-if="appointment.last_updated_by">, {{ appointment.last_updated_by.username }}</template><br>
      Teilnehmer: {{ appointment.participant_count}}<br>
      Erstellt am: {{ appointment.created_at | date }}<template v-if="appointment.created_by">, {{ appointment.created_by.username }}</template>
    </template>

  </details-sidebar>
</template>

<script>
import DraftLabel from "../partials/global/DraftLabel"

export default {
  methods: {
    getLinks(appointment) {
      return [
        {
          label: 'Allgemein',
          to: {
            name: 'appointments.edit.general',
            params: {
              appointmentId: appointment.id,
            },
          },
        },
        {
          label: 'Teilnehmer',
          to: {
            name: 'appointments.edit.participants',
            params: {
              appointmentId: appointment.id,
            },
          },
        },
      ]
    },
    getTypeText(type) {
      switch (type) {
        case this.$constants.APPOINTMENTS.TYPE_ONLINE:
          return 'Online'
        case this.$constants.APPOINTMENTS.TYPE_IN_PERSON:
          return 'Präsenztermin'
      }
    },
  },
  components: {
    DraftLabel,
  },
}
</script>
