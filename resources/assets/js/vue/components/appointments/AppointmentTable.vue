<template>
  <div>
    <v-data-table
      :headers="headers"
      :items="appointments"
      :loading="isLoading"
      :rows-per-page-items="[50, 100, 200]"
      :pagination.sync="pagination"
      class="elevation-1"
      item-key="id">
      <tr
        @click="editAppointment(props.item.id)"
        class="clickable"
        slot="items"
        slot-scope="props">
        <td class="pa-2 pr-0">
          <img
            v-if="props.item.cover_image_url"
            class="s-appointments__coverImage"
            :src="props.item.cover_image_url"/>
          <img
            v-else
            class="s-appointments__coverImage"
            src="/img/no-connection.svg"
            style="object-fit: contain"/>
        </td>
        <td>
          {{ props.item.name }}
        </td>
        <td>
          {{ getTypeText(props.item.type) }}
        </td>
        <td>
          <TagDisplay :tags="props.item.tags" />
        </td>
        <td>
          {{ props.item.start_date | date }}<br>
          {{ props.item.start_date | time }} bis {{ props.item.end_date | time }}
        </td>
        <td>
          {{ props.item.participant_count }}
          <v-icon
            v-if="!props.item.participant_count"
            small
            color="orange">
            warning_amber
          </v-icon>
        </td>
        <td>
          <v-chip
            disabled
            small
            :text-color="getStatus(props.item).textColor"
            :color="getStatus(props.item).color">
            {{ getStatus(props.item).status }}
          </v-chip>
        </td>
      </tr>
    </v-data-table>
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import moment from "moment"
import TagDisplay from "../partials/global/TagDisplay"

export default {
  components: {TagDisplay},
  data() {
    return {
      headers: [
        {
          text: "",
          value: "image",
          width: "110px",
          sortable: false,
        },
        {
          text: "Name",
          value: "name",
        },
        {
          text: "Typ",
          value: "type",
        },
        {
          text: "Benutzergruppen",
          value: "tags",
          sortable: false,
        },
        {
          text: "Termin",
          value: "start_date",
        },
        {
          text: "Teilnehmer",
          value: "participant_count",
          sortable: false,
        },
        {
          text: "Sichtbar",
          value: "status",
          sortable: false,
        },
      ],
    }
  },
  computed: {
    ...mapGetters({
      appointmentCount: 'appointments/appointmentCount',
      appointments: 'appointments/appointments',
      isLoading: 'appointments/listIsLoading'
    }),
    pagination: {
      get() {
        return this.$store.state.appointments.pagination
      },
      set(data) {
        this.$store.commit('appointments/setPagination', data)
      },
    },
  },
  watch: {
    pagination: {
      handler() {
        this.loadData()
      },
      deep: true,
    },
  },
  methods: {
    editAppointment(appointmentId) {
      this.$router.push({
        name: 'appointments.edit.general',
        params: {
          appointmentId: appointmentId,
        },
      })
    },
    getStatus(appointment) {
      const publishedAt = moment(appointment.published_at).startOf('day')
      const appointmentEndDate = moment(appointment.end_date)
      const now = moment()

      if(appointment.is_draft) {
        return {
          color: 'yellow',
          textColor: 'dark',
          status: 'Entwurf',
        }
      }

      if(appointment.published_at !== null && now.isBefore(publishedAt)) {
        return {
          color: 'light-green',
          textColor: 'white',
          status: `am: ${publishedAt.format('DD.MM.YYYY')}`,
        }
      }

      if(now.isAfter(appointmentEndDate)) {
        return {
          color: 'gray',
          textColor: 'dark',
          status: 'Abgeschlossen',
        }
      }

        return {
          color: 'green',
          textColor: 'white',
          status: 'Sichtbar',
        }
    },
    getTypeText(type) {
      switch (type) {
        case this.$constants.APPOINTMENTS.TYPE_ONLINE:
          return 'Online'
        case this.$constants.APPOINTMENTS.TYPE_IN_PERSON:
          return 'Präsenztermin'
      }
    },
    loadData() {
      this.$store.dispatch('appointments/loadAppointments')
    },
  },
}
</script>

<style scoped>
#app .s-appointments__coverImage {
  width: 110px;
  display: block;
  height: 100%;
  min-height: 70px;
  max-height: 150px;
  object-fit: cover;
}
</style>
