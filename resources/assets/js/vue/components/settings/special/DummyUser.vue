<template>
  <div>
    <div class="s-settingSwitch__label">Platzhalter Benutzer</div>
    <v-layout
      v-if="!isLoading"
      row>
      <v-flex
        shrink
        align-self-center
        class="pr-2">
        <v-tooltip bottom>
          <v-icon slot="activator">admin_panel_settings</v-icon>
          Nur für Superadmins
        </v-tooltip>
      </v-flex>
      <v-flex
        align-self-center
        shrink
        class="pr-4">
        <template v-if="!currentDummyUser">
          Kein Benutzer gesetzt.
        </template>
        <template v-else>
          <strong>{{ currentDummyUser.username }}</strong> (#{{ currentDummyUser.id }})<br>
          {{ currentDummyUser.email }}
        </template>
      </v-flex>
      <v-flex>
        <v-text-field
          hint="Wenn ein regulärer Benutzer gelöscht wird, wird er mit diesem Platzhalter Benutzer ersetzt"
          label="ID von neuem Platzhalter Benutzer"
          persistent-hint
          v-model="newDummyUserId"/>
      </v-flex>
      <v-flex
        xs2
        class="pr-4">
        <v-btn @click="updateDummyUser">Speichern</v-btn>
      </v-flex>
    </v-layout>
    <v-snackbar
      :timeout="10000"
      top
      v-model="showError"
    >
      {{ errorInfo }}
      <v-btn
        @click="showError = false"
        color="pink"
        flat
      >
        Schließen
      </v-btn>
    </v-snackbar>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        isLoading: true,
        currentDummyUser: null,
        newDummyUserId: null,
        showError: false,
        errorInfo: false,
      }
    },
    created() {
      this.loadDummyUser()
    },
    methods: {
      updateDummyUser() {
        axios.post("/backend/api/v1/settings/dummyUser", {
          dummyUserId: this.newDummyUserId,
        }).then(response => {
          if (typeof response.data.error !== "undefined") {
            this.showError = true
            this.errorInfo = response.data.error
          } else {
            this.newDummyUserId = null
            this.loadDummyUser()
          }
        })
      },
      loadDummyUser() {
        axios.get("/backend/api/v1/settings/dummyUser").then(response => {
          this.currentDummyUser = response.data
          this.isLoading = false
        })
      },
    },
  }
</script>
