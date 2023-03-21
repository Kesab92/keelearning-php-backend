<template>
  <div>
    <ModuleIntro>
      <template slot="title">
        Rollen
      </template>
      <template slot="description">
        Erstellen und verwalten Sie Rollen für Ihre Administratoren.
      </template>
      <template slot="links">
        <v-btn
          flat
          color="primary"
          small
          href="https://helpdesk.keelearning.de/de/articles/5658238-uber-admin-rollen"
          target="_blank">
          <v-icon
            small
            class="mr-1">
            help
          </v-icon>
          Anleitung öffnen
        </v-btn>
      </template>
    </ModuleIntro>
    <UserTabs />
    <AddRoleModal v-model="addRoleModalOpen" />
    <v-layout row>
      <v-btn
        color="primary"
        @click="addRoleModalOpen = true">
        <v-icon
          dark
          left>
          add
        </v-icon>
        Neue Rolle erstellen
      </v-btn>
      <v-spacer />
      <v-flex xs4 pr-2>
        <v-text-field
          append-icon="search"
          clearable
          placeholder="Bezeichnung"
          single-line
          v-model="search"/>
      </v-flex>
    </v-layout>
    <v-layout
      row wrap mt-2>
      <v-flex
        v-if="isLoading"
        xs12 md6 pa-2>
        <v-card>
          <div class="pa-3 text-xs-center">
            <v-progress-circular
              indeterminate
              color="primary"
            />
          </div>
        </v-card>
      </v-flex>
      <template v-else>
        <v-flex
          v-for="userRole in filteredUserRoles"
          :key="userRole.id"
          xs12 md6 pa-2>
          <v-card height="100%">
            <v-card-title primary-title>
              <v-layout row>
                <h3 class="headline">
                  {{ userRole.name }}
                </h3>
                <template v-if="userRole.is_main_admin">
                  <v-spacer />
                  <v-icon right>lock</v-icon>
                </template>
              </v-layout>
            </v-card-title>
            <v-card-text>
              {{ userRole.description }}
            </v-card-text>
            <v-card-actions>
              <div class="mx-2">
                {{ userRole.users_count }} Benutzer
              </div>
              <v-btn
                :to="{
                  name: 'user-roles.edit.general',
                  params: {
                    userRoleId: userRole.id,
                  },
                }"
                flat
                color="info">
                <template v-if="userRole.is_main_admin">
                  Ansehen
                </template>
                <template v-else>
                  Bearbeiten
                </template>
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-flex>
      </template>
    </v-layout>
    <UserRoleSidebar />
  </div>
</template>

<script>
import {mapGetters} from 'vuex'
import AddRoleModal from './AddRoleModal'
import ModuleIntro from '../../partials/global/ModuleIntro'
import UserRoleSidebar from './UserRoleSidebar'
import UserTabs from '../UserTabs'

export default {
  data() {
    return {
      addRoleModalOpen: false,
      search: '',
    }
  },
  created() {
    this.loadData()
  },
  computed: {
    ...mapGetters({
      isLoading: 'userRoles/isLoading',
      userRoles: 'userRoles/userRoles',
    }),
    filteredUserRoles() {
      return this.userRoles
        .filter((role) => {
          if (!this.search) {
            return true
          }
          return role.name.toUpperCase().includes(this.search.toUpperCase()) ||  role.description.toUpperCase().includes(this.search.toUpperCase())
        })
        .sort((roleA, roleB) => {
          if (roleA.is_main_admin && !roleB.is_main_admin) {
            return -1
          }
          if (!roleA.is_main_admin && roleB.is_main_admin) {
            return 1
          }
          const nameA = roleA.name.toUpperCase()
          const nameB = roleB.name.toUpperCase()
          if (nameA < nameB) {
            return -1
          }
          if (nameA > nameB) {
            return 1
          }
          return 0
        })
    },
  },
  methods: {
    loadData() {
      this.$store.dispatch('userRoles/loadUserRoles')
    },
  },
  components: {
    AddRoleModal,
    ModuleIntro,
    UserRoleSidebar,
    UserTabs,
  },
}
</script>
