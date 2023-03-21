<template>
  <div v-if="userData">
    <UserToolbar
      :user-data="userData"
      :is-saving="isSaving"
      @save="save" />

    <div class="pa-4">
      <v-text-field
        v-if="showPersonalData('users')"
        v-model="userData.username"
        :disabled="isReadonly"
        browser-autocomplete="chrome-off"
        label="Benutzername"
        :hint="appSettings.module_quiz ? 'Sichtbar für alle Benutzer der App im Quiz-Battle' : ''"
        outline />

      <v-text-field
        v-if="showEmails('users')"
        v-model="userData.email"
        :disabled="isReadonly"
        browser-autocomplete="chrome-off"
        label="E-Mail"
        outline />

      <tag-select
        v-model="userData.tags"
        color="blue-grey lighten-2"
        label="TAGs"
        multiple
        outline
        :disabled="isReadonly"
        placeholder="Keine TAGs"
        limit-to-tag-rights
        show-limited-tags
      />

      <v-select
        v-if="languages.length > 1"
        v-model="userData.language"
        color="blue-grey lighten-2"
        label="Sprache"
        :items="languages"
        :disabled="isReadonly"
        outline />

      <Toggle
        v-model="userData.active"
        :disabled="isReadonly"
        label="Aktiv"
        hint="Inaktive Benutzer können nicht auf die App zugreifen und nicht herausgefordert werden" />

      <Toggle
        v-if="isSuperAdmin"
        v-model="userData.is_keeunit"
        :disabled="isReadonly"
        label="Keeunit Mitarbeiter"
        superAdminOnly/>

      <template v-if="isMainAdmin">
        <h4 class="sectionHeader mt-section">Soll der Benutzer ein Administrator sein?</h4>
        <p>Benutzer mit einer Rolle sind Administratoren und können die Adminseite aufrufen.</p>
        <user-role-select
          v-model="userData.user_role_id"
          color="blue-grey lighten-2"
          label="Administrations-Rolle"
          outline
          :has-tag-rights="userData.tagRights.length > 0"
          placeholder="Kein Administrator"
        />
      </template>
    </div>

    <v-divider />

    <h4 class="mx-4 my-3">Sonstiges</h4>
    <div class="px-4 pb-4 pt-0">
      <v-text-field
        v-if="showPersonalData('users')"
        v-model="userData.firstname"
        browser-autocomplete="chrome-off"
        label="Vorname"
        :disabled="isReadonly"
        hint="Ist nur für Administratoren sichtbar (Verwendung in: Zertifikaten, Statistiken)"
        outline />
      <v-text-field
        v-if="showPersonalData('users')"
        v-model="userData.lastname"
        browser-autocomplete="chrome-off"
        label="Nachname"
        :disabled="isReadonly"
        hint="Ist nur für Administratoren sichtbar (Verwendung in: Zertifikaten, Statistiken)"
        outline />
    </div>

    <template v-if="Object.keys(metaFields).length">
      <v-divider />

      <h4 class="mx-4 my-3">Meta Informationen</h4>
      <div class="px-4 pb-4 pt-0">
        <v-text-field
          v-for="(metaLabel, metaKey) in metaFields"
          :key="metaKey"
          v-model="userData.meta[metaKey]"
          browser-autocomplete="chrome-off"
          :label="metaLabel"
          :disabled="isReadonly"
          outline />
      </div>
    </template>

    <v-layout
      class="mb-4 mx-4"
      align-center
      row>
      <template v-if="userData.tos_accepted">
        <v-flex
          class="mr-2"
          shrink>
          <v-icon class="green--text">done</v-icon>
        </v-flex>
        <v-flex grow>
          Nutzungsbedingungen akzeptiert
        </v-flex>
      </template>
      <template v-else>
        <v-flex
          class="mr-2"
          shrink>
          <v-icon class="red--text">error</v-icon>
        </v-flex>
        <v-flex grow>
          <strong class="red--text">
            Nutzungsbedingungen noch nicht akzeptiert
          </strong>
        </v-flex>
      </template>
    </v-layout>

    <div
      v-if="appProfiles.length > 1"
      class="mb-4 mx-4"
    >
      <div class="grey--text text--darken-2">App-Profil:</div>
      <span class="subheading">
        {{ appProfile.name }}
      </span>
      <span
        v-if="appProfile.is_default"
        class="grey--text">
        (default)
      </span>
    </div>
  </div>
</template>

<script>
import TagSelect from "../partials/global/TagSelect"
import UserRoleSelect from "../partials/global/UserRoleSelect"
import UserToolbar from "./UserToolbar"
import {mapGetters} from "vuex"

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
      appSettings: 'app/appSettings',
      appProfiles: 'app/profiles',
      isSuperAdmin: 'app/isSuperAdmin',
      isMainAdmin: 'app/isMainAdmin',
      metaFields: 'users/metaFields',
      myRights: 'app/myRights',
      showEmails: 'app/showEmails',
      showPersonalData: 'app/showPersonalData',
    }),
    isReadonly() {
      return !this.myRights['users-edit']
    },
    languages() {
      const availableLanguages = this.$store.getters['app/languages']
      const languages = []
      Object.keys(availableLanguages).forEach(languageKey => {
        languages.push({
          text: availableLanguages[languageKey],
          value: languageKey,
        })
      })
      return languages
    },
    appProfile() {
      return this.appProfiles.find(profile => profile.id === this.userData.app_profile_id)
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
          username: this.userData.username,
          email: this.userData.email,
          tags: this.userData.tags,
          language: this.userData.language,
          active: this.userData.active,
          firstname: this.userData.firstname,
          lastname: this.userData.lastname,
          meta: this.userData.meta,
          is_keeunit: this.userData.is_keeunit,
          user_role_id: this.userData.user_role_id,
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
    UserRoleSelect,
  },
}
</script>
