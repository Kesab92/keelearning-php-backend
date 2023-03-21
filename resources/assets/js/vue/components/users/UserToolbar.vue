<template>
  <div>
    <details-sidebar-toolbar>
      <template
        v-if="myRights['users-edit']"
        v-slot:default>
        <v-btn
          :loading="isSaving"
          color="primary"
          @click="$emit('save')"
        >
          Speichern
        </v-btn>

        <v-spacer/>

        <v-btn
          :loading="isSaving"
          color="red"
          outline
          @click="remove"
        >
          Löschen
        </v-btn>
      </template>
      <template v-slot:alerts>
        <v-alert
          v-if="userData.login_suspended"
          type="warning"
          icon="lock"
          :value="true">
          Login gesperrt.
          <template v-if="myRights['users-edit']">
            Setzen Sie das Passwort im Tab <router-link :to="{name: 'users.edit.management',params:{userId: userData.id}}">Verwaltung</router-link> zurück, um den Nutzer freizuschalten.
          </template>
        </v-alert>
        <v-alert
          v-if="!myRights['users-edit']"
          type="info"
          :value="true">
          Zum Editieren eines Benutzers fehlt Ihnen die Berechtigung.
        </v-alert>
      </template>
    </details-sidebar-toolbar>

    <DeleteDialog
      v-model="deleteDialogOpen"
      :deletion-url="`/backend/api/v1/users/${userData.id}`"
      :dependency-url="`/backend/api/v1/users/${userData.id}/delete-information`"
      :entry-name="userData.username"
      :redirect-url="afterDeletionRedirectURL"
      type-label="Benutzer">
      <div slot="info">
        Dieser Benutzer kann jetzt gelöscht werden.
      </div>
    </DeleteDialog>
  </div>
</template>

<script>
  import DeleteDialog from "../partials/global/DeleteDialog"
  import {mapGetters} from "vuex"
  export default {
    props: [
      'userData',
      'isSaving',
    ],
    data() {
      return {
        deleteDialogOpen: false,
      }
    },
    computed: {
      ...mapGetters({
        myRights: 'app/myRights',
      }),
      afterDeletionRedirectURL() {
        return "/users#/users"
      },
    },
    methods: {
      remove() {
        this.deleteDialogOpen = true
      },
    },
    components: {
      DeleteDialog,
    },
  }
</script>
