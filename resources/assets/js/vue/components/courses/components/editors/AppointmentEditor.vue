<template>
  <div
    class="mb-4">
    <translated-input
      v-model="value.title"
      :translations="value.translations"
      :placeholder="currentAppointment.name"
      attribute="title"
      label="Name vom Kursinhalt"
      hide-details
      :readOnly="isReadonly"/>
    <v-autocomplete
      v-model="value.foreign_id"
      :items="availableAppointments"
      label="Termin"
      item-text="name"
      item-value="id"
      hide-details
      :disabled="isReadonly"
      clearable
      class="mb-1"
    >
      <template v-slot:selection="data">
        {{ data.item ? data.item.name : '' }}
      </template>
    </v-autocomplete>
    <div class="mb-3">
      <a
        v-if="!isReadonly && myRights['appointments-edit']"
        target="_blank"
        href="/appointments?#/appointments">Neuen Termin erstellen</a>
    </div>

    <tag-select
      v-model="value.tags"
      label="Sichtbar für folgende User"
      placeholder="Alle"
      class=" mt-section"
      limit-to-tag-rights
      :disabled="isReadonly"
      multiple/>
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import {isPast} from "date-fns"
import TagSelect from "../../../partials/global/TagSelect"

export default {
  props: [
    'course',
    'value',
  ],
  created() {
    this.loadAllAppointments()
  },
  watch: {
    value: {
      handler() {
        this.$emit('input', this.value)
      },
      deep: true,
    },
  },
  computed: {
    ...mapGetters({
      allAppointments: 'appointments/allAppointments',
      myRights: 'app/myRights',
    }),
    availableAppointments() {
      if (!this.allAppointments.length) {
        return []
      }
      return this.allAppointments.filter(appointment => {
        if (appointment.id === this.value.foreign_id) {
          return true
        }

        const endDate = new Date(appointment.end_date)

        return !appointment.is_cancelled && !appointment.is_draft && !isPast(endDate)
      })
    },
    currentAppointment() {
      if (!this.value.foreign_id || !this.availableAppointments.length) {
        return {}
      }
      return this.availableAppointments.find(appointment => appointment.id === this.value.foreign_id)
    },
    isReadonly() {
      return !this.myRights['courses-edit']
    },
  },
  methods: {
    loadAllAppointments() {
      this.$store.dispatch('appointments/loadAllAppointments')
    },
    save() {
      return axios.post(`/backend/api/v1/courses/${this.course.id}/content/${this.value.id}`, {
        foreign_id: this.value.foreign_id,
        tags: this.value.tags,
        title: this.value.title,
        visible: this.value.visible,
      })
    },
  },
  components: {
    TagSelect,
  },
}
</script>


<style lang="scss" scoped>
#app .s-contentImage {
  max-width: 200px;
  max-height: 230px;
  object-fit: cover;
}
</style>
