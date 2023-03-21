<template>
  <div v-if="userRoleData">
    <Toolbar
      :user-role-data="userRoleData"
      :is-saving="isSaving"
      :is-valid="isValid"
      @save="save" />
    <div class="pa-4">
      <v-form v-model="isValid">
        <h4>Rollen-Übersicht</h4>
        <p>Benutzer mit einer Rolle sind Administratoren und können die Adminseite aufrufen.</p>
        <v-text-field
          v-model="userRoleData.name"
          browser-autocomplete="chrome-off"
          label="Bezeichnung"
          outline
          required
          :disabled="!!userRoleData.is_main_admin"
          :rules="[
              $rules.minChars(3),
              $rules.maxChars(255),
              $rules.noDuplicate(userRoles.map(userRole => userRole.name)),
            ]" />
        <v-textarea
          v-if="!userRoleData.is_main_admin"
          v-model="userRoleData.description"
          label="Beschreibungstext der Rolle"
          outline />
        <v-alert
          v-if="userRoleData.is_main_admin"
          type="info"
          :value="true">
          Ein Hauptadmin verfügt über alle Rechte und kann anderen Benutzern Rechte zuweisen und entziehen. Es ist nicht möglich einem Hauptadmin TAG-Beschränkungen zuzuweisen.
        </v-alert>
        <div
          v-for="rightsGroup in activeRightGroups"
          :key="rightsGroup.title"
          class="mb-2">
          <b>{{ rightsGroup.title }}</b>
          <ul>
            <li
              v-for="right in rightsGroup.rights"
              :key="right.type">
              {{ right.title }}
            </li>
          </ul>
        </div>
      </v-form>
    </div>
  </div>
</template>

<script>
import rightDefinitions from './rights.js'
import UserRoleSidebarMixin from './mixin.js'

export default {
  mixins: [UserRoleSidebarMixin],
  computed: {
    userRoles: {
      get() {
        return this.$store.state.userRoles.userRoles.filter(existedUserRole => existedUserRole.name !== this.userRole.name)
      },
    },
    rightGroups() {
      return rightDefinitions.filter(rightsGroup => {
        if (!rightsGroup.module) {
          return true
        }
        return this.appSettings[rightsGroup.module] == "1"
      })
    },
    activeRightGroups() {
      return JSON.parse(JSON.stringify(this.rightGroups)).filter(rightGroup => {
        rightGroup.rights = rightGroup.rights.filter(right => {
          return this.userRoleData.rights.includes(right.type)
        })
        return rightGroup.rights.length > 0
      })
    },
  },
  methods: {
    async save() {
      if (this.isSaving) {
        return
      }
      this.isSaving = true
      await this.$store.dispatch('userRoles/saveUserRole', {
        id: this.userRoleData.id,
        name: this.userRoleData.name,
        description: this.userRoleData.description,
      })
      this.isSaving = false
    },
  },
}
</script>
