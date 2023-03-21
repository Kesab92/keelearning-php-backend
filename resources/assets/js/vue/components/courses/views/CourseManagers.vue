<template>
  <div class="pa-4">
    <admin-select
      v-model="managers"
      color="blue-grey lighten-2"
      label="Verantwortliche Administratoren"
      multiple
      outline
      placeholder="Kein verantwortlicher Administrator hinterlegt"
      persistent-hint
      :disabled="isReadonly"
      hint="Tragen Sie hier einen verantwortlichen Admin ein, der benachrichtigt werden soll, wenn Benutzer für einen Kurs freigeschaltet werden möchten."
    />
    <v-layout row>
      <v-flex
        xs12
        class="mt-4">
        <v-btn
          color="primary"
          :loading="isSaving"
          :disabled="isReadonly"
          @click="save">
          Kurs Speichern
        </v-btn>
      </v-flex>
    </v-layout>
  </div>
</template>

<script>
import AdminSelect from "../../partials/global/AdminSelect"
import {mapGetters} from "vuex";

export default {
  props: ['course'],
  data() {
    return {
      isSaving: false,
      managers: null,
    }
  },
  watch: {
    course: {
      handler() {
        this.managers = this.course.managers.map(manager => manager.id)
      },
      immediate: true,
    },
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    isReadonly() {
      return !this.myRights['courses-edit']
    },
  },
  methods: {
    save() {
      if(this.isSaving) {
        return
      }
      this.isSaving = true
      axios.post('/backend/api/v1/courses/' + this.course.id + '/managers', {
        managers: this.managers,
      })
        .then(() => {
          this.$emit('saved')
        })
        .catch(() => {
          alert('Die Verantwortlichen konnten leider nicht gespeichert werden. Bitte probieren Sie es später erneut.')
        })
        .finally(() => {
          this.isSaving = false
        })
    }
  },
  components: {
    AdminSelect,
  },
}
</script>

<style lang="scss" scoped>
#app .s-switch {
  &.-dense {
    margin-top: 0;
    padding-top: 0;
  }
}
</style>
