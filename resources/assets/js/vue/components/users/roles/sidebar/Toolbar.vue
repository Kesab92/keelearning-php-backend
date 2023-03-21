<template>
  <div>
    <details-sidebar-toolbar>
      <template
        v-if="userRoleData"
        v-slot:default>
        <v-btn
          :loading="isSaving"
          :disabled="!isValid"
          color="primary"
          @click="$emit('save')">
          Speichern
        </v-btn>
        <v-btn
          :to="`/users#/users/%7B%22filter%22:%22role_%20${userRoleData.id}%22%7D`"
          :disabled="!userRoleData.users.length"
        >Benutzer mit dieser Rolle</v-btn>
        <v-spacer/>

        <v-menu
          v-if="actions.length"
          offset-y>
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
                :key="`user-role-action-${index}`"
                @click="doAction(action)"
            >
              <v-list-tile-title>{{ action.title }}</v-list-tile-title>
            </v-list-tile>
          </v-list>
        </v-menu>
      </template>
    </details-sidebar-toolbar>
    <DeleteDialog
      v-if="!userRoleData.users.length"
      v-model="deleteDialogOpen"
      :deletion-url="`/backend/api/v1/user-roles/${userRoleData.id}`"
      :dependency-url="`/backend/api/v1/user-roles/${userRoleData.id}/delete-information`"
      :entry-name="userRoleData.name"
      redirect-url="/users#/user-roles"
      type-label="Benutzerrolle"
      @deleted="handleDeletion"/>
    <BlockedDeletingModal
      v-if="userRoleData.users.length"
      v-model="blockedDeletingDialogOpen"
      :user-role="userRoleData"
      />
  </div>
</template>

<script>
import DeleteDialog from '../../../partials/global/DeleteDialog'
import BlockedDeletingModal from "./BlockedDeletingModal"

export default {
  props: {
    isSaving: {
      type: Boolean,
      required: true,
    },
    userRoleData: {
      type: Object,
      required: true,
    },
    isValid: {
      type: Boolean,
      required: false,
      default: true,
    },
  },
  data() {
    return {
      deleteDialogOpen: false,
      blockedDeletingDialogOpen: false,
    }
  },
  computed: {
    actions() {
      if(this.userRoleData.is_main_admin) {
        return []
      }

      return [
        {
          name: 'clone',
          title: 'Duplizieren'
        },
        {
          name: 'delete',
          title: 'Löschen',
        }
      ]
    },
  },
  methods: {
    doAction(action) {
      switch (action.name) {
        case 'clone':
          this.clone()
          break
        case 'delete':
          this.remove()
          break
      }
    },
    clone() {
      axios.post(`/backend/api/v1/user-roles/${this.userRoleData.id}/clone`, {})
        .then((response) => {
          this.$router.push(`/user-roles/${response.data.user_role_id}/general`)
        })
        .catch((error) => {
          if(error.response.data.message) {
            alert(error.response.data.message)
          } else {
            alert('Es gab einen Fehler beim Speichern der Änderungen.')
          }
        })
    },
    remove() {
      if(this.userRoleData.users.length) {
        this.blockedDeletingDialogOpen = true
      } else {
        this.deleteDialogOpen = true
      }
    },
    handleDeletion() {
      this.$store.dispatch('userRoles/loadUserRoles')
    },
  },
  components: {
    DeleteDialog,
    BlockedDeletingModal,
  },
}
</script>
