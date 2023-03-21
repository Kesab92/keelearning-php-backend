<template>
  <div v-if="userData && userData.is_admin">
    <UserToolbar
      :user-data="userData"
      :is-saving="isSaving"
      @save="save"/>

    <div class="py-4">
      <div v-if="userData.role && userData.role.is_main_admin">
        <v-alert
          :value="true"
          color="info"
          icon="priority_high"
          outline
          class="mt-4">
          Dieser Benutzer ist ein Hauptadmin und kann daher keiner TAG-Beschränkung unterliegen.<br>
          Bitte wenden Sie sich bei Fragen an <a href="mailto:support@keeunit.de">support@keeunit.de</a>
        </v-alert>
        <div class="px-4 pt-3">
          Ein Hauptadmin verfügt über alle Rechte und kann anderen Benutzern Rechte zuweisen und wieder entziehen
        </div>
      </div>

      <template v-else>
        <h4 class="mx-4">Soll der Administrator nur bestimmte TAGs einsehen können?</h4>
        <h4 class="mx-4 mb-2 mt-4">TAG-Beschränkungen</h4>
        <div class="px-4 pb-2 pt-0">
          <p>
            Wählen Sie die TAGs aus, die der Administrator verwalten darf. Wenn Sie keinen TAG angeben, darf der
            Administrator alle Inhalte bearbeiten.<br>
            Die TAG-Beschränkung gilt nur für die Module: Benutzerverwaltung, Kurse, Tests, Statistiken, News,
            Gewinnspiele
          </p>

          <tag-select
            v-model="userData.tagRights"
            color="blue-grey lighten-2"
            label="TAGs"
            multiple
            outline
            :disabled="!isMainAdmin"
            placeholder="Alle TAGs"
            limit-to-tag-rights
            show-limited-tags
          />

          <div
            v-if="userData.user_role_id && isMainAdmin"
            class="mb-3"
          >
            <h4>Rechte dieses Administrators</h4>
            <router-link
              :to="{name: 'user-roles.edit.general', params: {userRoleId: userData.user_role_id}}">
              ROLLE ÖFFNEN
            </router-link>
          </div>

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
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import TagSelect from "../partials/global/TagSelect"
import UserToolbar from "./UserToolbar"
import rightDefinitions from './roles/sidebar/rights'

export default {
  props: ["user"],
  data() {
    return {
      userData: null,
      isSaving: false,
      deleteDialogOpen: false,
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
      appSettings: 'app/appSettings',
      isMainAdmin: 'app/isMainAdmin',
      userRole: 'users/userRole',
    }),
    rightGroups() {
      return rightDefinitions.filter(rightsGroup => {
        if (!rightsGroup.module) {
          return true
        }
        return this.appSettings[rightsGroup.module] == "1"
      })
    },
    activeRightGroups() {
      if(!this.userRole) {
        return []
      }
      return JSON.parse(JSON.stringify(this.rightGroups)).filter(rightGroup => {
        rightGroup.rights = rightGroup.rights.filter(right => {
          return this.userRole.rights.includes(right.type)
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
      try {
        await this.$store.dispatch("users/saveUser", {
          id: this.userData.id,
          tagRights: this.userData.tagRights,
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
  },
  components: {
    UserToolbar,
    TagSelect,
  },
}
</script>
