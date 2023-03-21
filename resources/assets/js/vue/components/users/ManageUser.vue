<template>
  <div v-if="userData">
    <UserToolbar
      :user-data="userData"
      :is-saving="isSaving"
      @save="save" />

    <h4 class="mx-4 mb-0 mt-4">Passwort zurücksetzen</h4>
    <div class="px-4 pb-4 pt-0">
      <p>
        Hier können Sie ein neues Passwort vergeben und es dem User zusenden.
      </p>

      <ResetPassword
        :user="userData"
        @passwordReset="$emit('dataUpdated')" />

    </div>

    <template v-if="!userRole || !userRole.is_main_admin">
      <v-divider />

      <h4 class="mx-4 mb-2 mt-4">Löschdatum setzen</h4>
      <div class="px-4">
        <p>
          Setzt das Datum, an dem der User gelöscht wird.
        </p>

        <DeletionDate v-model="userData" />
      </div>
    </template>

    <template v-if="userData.deleted_at !== null">
      <v-divider />

      <h4 class="mx-4 mb-2 mt-4">Benutzer wiederherstellen</h4>
      <div class="px-4 pb-4 pt-0">
        <p>
          Stellt diesen Benutzer wieder her, sodass dieser wieder auf die App zugreifen kann.
        </p>

        <v-btn
          class="mx-0"
          color="primary"
          outline
          @click="restoreUser">
          Benutzer wiederherstellen
        </v-btn>
      </div>
    </template>

    <v-divider />

    <h4 class="mx-4 mb-2 mt-4">Eingelöste Voucher</h4>
    <div class="px-4">
      <Vouchers :user="userData" :user-role="userRole" />
    </div>
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import ResetPassword from "./ResetPassword"
import DeletionDate from "./DeletionDate"
import UserToolbar from "./UserToolbar"
import Vouchers from "./Vouchers"

export default {
  props: ["user"],
  data() {
    return {
      userData: null,
      isSaving: false,
    }
  },
  watch: {
    user: {
      handler() {
        this.userData = JSON.parse(JSON.stringify(this.user))
      },
      immediate: true,
    },
  },
  computed: {
    ...mapGetters({
      userRole: 'users/userRole',
    }),
    afterDeletionRedirectURL() {
      return "/users#/users"
    },
  },
  methods: {
    async save() {
      if (this.isSaving) {
        return
      }
      this.isSaving = true
      try {
        await this.$store.dispatch("users/saveUser", {
          id: this.userData.id,
          expires_at: this.userData.expires_at,
        })
        this.$emit('dataUpdated')
      } catch(e) {
        let message = e.response.data.message
        if(!message) {
          message = 'Es ist ein unbekannter Fehler aufgetreten. Bitte versuchen Sie es später erneut.'
        }
        alert(message)
      }
      this.isSaving = false
    },
    restoreUser() {
      return axios.post('/backend/api/v1/users/' + this.userData.id + '/restore').then((response) => {
        this.$store.dispatch('users/loadUser', {
          userId: this.userData.id,
        })
        alert('Der Benutzer wurde wiederhergestellt.')
      })
      .catch(() => {
        alert('Der Benutzer konnte nicht wiederhergestellt werden.')
      })
    },
  },
  components: {
    UserToolbar,
    DeletionDate,
    ResetPassword,
    Vouchers,
  },
}
</script>
