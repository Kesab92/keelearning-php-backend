<template>
  <div>
    <div class="headline mb-1">Registrierung</div>
    <div class="body-1 mb-4">
      Hier können Sie festlegen, welche Daten bei der Registrierung vom Benutzer angegeben werden.
    </div>

    <SettingSwitch
      type="profileSetting"
      :is-candy="isCandy"
      setting="signup_enabled"
      :settings="profileSettings"
      label="Selbstregistrierung"
      description="Erlaubt es, den Benutzern die reguläre Registrierungsseite zu verwenden."
      class="mt-4 mb-2"
      @updateSetting="updateSetting"/>
    <v-layout
      v-for="setting in signupSettings"
      :key="setting.key"
      class="s-signupRow"
      :class="{
        '-disabled': !profileSettings.signup_enabled.value,
      }"
      row>
      <v-flex
        xs3
        align-self-center
        class="pr-4">
        <SettingSwitch
          type="profileSetting"
          :setting="setting.key"
          :is-candy="isCandy"
          :settings="profileSettings"
          :label="setting.label"
          class="mb-0"
          @updateSetting="updateSetting"/>
      </v-flex>
      <v-flex xs3>
        <SettingSelect
          v-if="profileSettings[`${setting.key}_mandatory`]"
          type="profileSetting"
          :is-candy="isCandy"
          :setting="`${setting.key}_mandatory`"
          :settings="profileSettings"
          :items="mandatorySelectItems"
          :label="getSettingLabel(`${setting.key}_mandatory`)"
          @updateSetting="updateSetting"/>
      </v-flex>
    </v-layout>

    <div class="headline mt-5">Weitere Registrierungsoptionen</div>

    <SettingSwitch
      type="profileSetting"
      :is-candy="isCandy"
      setting="signup_has_temporary_accounts"
      :settings="profileSettings"
      label="Gast-Accounts aktivieren"
      description="Aktiviert eine Registrierungsseite für Gast-User. Gast-User können sich anonymisiert, nur mit einem Benutzernamen, für keelearning registrieren."
      class="mt-3"
      @updateSetting="updateSetting"/>

    <SettingSwitch
      type="profileSetting"
      :is-candy="isCandy"
      setting="signup_force_password_reset"
      :settings="profileSettings"
      label="Passwortwechsel des initialen Passworts erzwingen"
      class="mt-3"
      @updateSetting="updateSetting"/>

    <SettingSwitch
      type="profileSetting"
      :is-candy="isCandy"
      setting="allow_username_change"
      :settings="profileSettings"
      label="Benutzer dürfen Benutzernamen anpassen"
      class="mt-3"
      @updateSetting="updateSetting"/>

    <SettingSwitch
      type="profileSetting"
      :is-candy="isCandy"
      setting="allow_email_change"
      :settings="profileSettings"
      label="Benutzer dürfen E-Mail Adresse anpassen"
      class="mt-3"
      @updateSetting="updateSetting"/>

    <v-layout row>
      <v-flex align-self-center xs4 class="s-settingSwitch__label">
        Login-Fallback-Sprache
      </v-flex>
      <v-flex align-self-center xs6>
        <SettingSelect
          type="profileSetting"
          :is-candy="isCandy"
          setting="signup_default_language"
          :settings="profileSettings"
          :items="availableLanguages"
          :placeholder="availableLanguages[0].label"
          description="Wenn die App die Browser-Sprache nicht unterstützt, wird die Fallback-Sprache verwendet."
          @updateSetting="updateSetting"/>
      </v-flex>
    </v-layout>

    <v-form
      v-model="formIsValid"
      class="my-5">
      <template v-if="allAppSettings.has_login_limiations == '1'">
        <div class="headline mb-1">Login beschränken</div>
        <div class="body-1 mb-3">
          Legt fest, wie viele gleichzeitige Logins ein Benutzer haben kann. Bei Überschreitung wird der älteste Login beendet.
        </div>
        <v-layout
          row>
          <v-flex
            class="pr-4"
            md6
            xs12>
            <v-text-field
              v-model="config.max_concurrent_logins"
              hint="Anzahl an Geräte/Browser, mit denen ein User gleichzeitig eingeloggt sein darf"
              label="Gleichzeitige Logins"
              min="0"
              outline
              placeholder="Unbegrenzt"
              step="1"
              type="number">
            </v-text-field>
          </v-flex>
        </v-layout>
      </template>
      <div class="headline mt-3 mb-1">Single Sign-on Authentifikation</div>
      <div class="body-1 mb-3">
        Erlaubt es Benutzern, sich über ein Drittsystem in der Anwendung zu authentifizieren
        <v-btn
          flat
          small
          color="white"
          href="https://helpdesk.keelearning.de/de/articles/5487002-single-sign-on-third-party-login"
          target="_blank"
          class="ml-0"
        >
          <v-icon
            small
            color="primary"
            class="mr-1">
            help
          </v-icon>
          <span class="primary--text">Anleitung öffnen</span>
        </v-btn>
      </div>
      <SettingSwitch
        type="profileSetting"
        :is-candy="isCandy"
        setting="openid_enabled"
        :settings="profileSettings"
        label="OpenID aktivieren"
        description="Aktiviert den Login via OpenID"
        class="mt-4 mb-3"
        @updateSetting="updateSetting"/>
      <v-text-field
        v-model="config.openid_title"
        hint="z.B. 'Login mit Firmen-Account'"
        label="Login Button Label"
        :disabled="!profileSettings.openid_enabled.value"
        outline/>
      <v-text-field
        v-model="config.openid_authority_url"
        hint="zB https://login.microsoftonline.com/common/v2.0"
        label="OpenID Connect Authority URL"
        :disabled="!profileSettings.openid_enabled.value"
        outline/>
      <v-text-field
        v-model="config.openid_client_id"
        label="OpenID Connect Client ID"
        :disabled="!profileSettings.openid_enabled.value"
        outline/>
      <v-text-field
        v-model="config.openid_claims"
        label="Scope (optional)"
        placeholder="openid profile email"
        :disabled="!profileSettings.openid_enabled.value"
        append-icon="help"
        @click:append="isScopeHelpOpen=!isScopeHelpOpen"
        outline/>
      <v-dialog
        v-model="isScopeHelpOpen"
        max-width="740px">
        <v-card>
          <v-toolbar>
            <v-toolbar-title>
              Optionale Information (wird nicht für Azure OpenID Connect benötigt)
            </v-toolbar-title>
          </v-toolbar>
          <v-card-text class="body-1">
            Über das Scope-Feld können Sie konfigurieren welche Permission-Scopes von keelearning SSO angefragt werden.
          </v-card-text>
          <v-spacer/>
          <v-card-text class="body-1">
            Beispiel - AD FS Server (Werte sind durch Leerzeichen getrennt):
          </v-card-text>
          <v-card-text class="body-1">
            <code>openid profile email allatclaims</code>
          </v-card-text>
          <v-card-actions>
            <v-spacer/>
            <v-btn
              color="primary"
              flat
              @click="isScopeHelpOpen=!isScopeHelpOpen">
              Schließen
            </v-btn>
            <v-spacer/>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <SettingSwitch
        type="profileSetting"
        :is-candy="isCandy"
        setting="enable_sso_registration"
        :settings="profileSettings"
        label="Registrierung neuer Benutzer über Single Sign-on erlauben"
        description="Wenn diese Option nicht aktiv ist, können sich nur existierende Benutzer über SSO anmelden."
        class="mb-2"
        :disabled="!profileSettings.openid_enabled.value"
        @updateSetting="updateSetting"/>
      <SettingSwitch
        type="profileSetting"
        :is-candy="isCandy"
        setting="sso_is_default_login"
        :settings="profileSettings"
        label="SSO zum Standard-Login machen"
        description="Blendet die Login-Maske aus und macht Single-Sign-On zum angezeigten Standard"
        class="mb-2"
        :disabled="!profileSettings.openid_enabled.value"
        @updateSetting="updateSetting"/>

      <v-btn
        :disabled="isSaving || !formIsValid"
        :loading="isSaving"
        class="mt-3 ml-0"
        color="primary"
        @click="updateSignupSettings">
        Speichern
        <template
          v-if="savingSuccess"
          v-slot:loader>
          <v-icon light>done</v-icon>
        </template>
      </v-btn>
    </v-form>
  </div>
</template>

<script>
  import {mapGetters} from 'vuex'
  import AreaMixin from './areaMixin'
  import SettingSwitch from "../input-types/SettingSwitch"
  import SettingSelect from "../input-types/SettingSelect"

  export default {
    mixins: [AreaMixin],
    data() {
      return {
        config: {
          max_concurrent_logins: null,
          openid_title: null,
          openid_authority_url: null,
          openid_client_id: null,
          openid_claims: null,
        },
        mandatorySelectItems: [
          {
            id: '',
            label: 'Optional'
          },
          {
            id: 'mandatory',
            label: 'Verpflichtend',
          },
        ],
        isScopeHelpOpen: false
      }
    },
    computed: {
      ...mapGetters({
        allAppLanguages: 'app/languages',
        allAppSettings: 'app/appSettings',
      }),
      availableLanguages() {
        return Object.entries(this.allAppLanguages).map((language) => {
          return {
            id: language[0],
            label: language[1],
          }
        })
      },
      signupSettings() {
        return [
          {
            key: 'signup_show_firstname',
            label: 'Vorname',
          },
          {
            key: 'signup_show_lastname',
            label: 'Nachname',
          },
          {
            key: 'signup_show_email',
            label: 'E-Mail',
          },
          {
            key: 'signup_show_voucher',
            label: 'Voucher',
          },
          /* Meta info is currently hardcoded, so we have to configure it there (in src/Models/App.php)
          {
            key: 'signup_show_meta',
            label: 'Meta',
          },*/
        ].filter(setting => !!this.profileSettings[setting.key])
      },
    },
    methods: {
      getSettingLabel(setting) {
        switch(setting) {
          case 'signup_show_email_mandatory':
            return 'AuthWelcome nicht vergessen!'
          default:
            return null
        }
      },
      updateSignupSettings() {
        this.config.max_concurrent_logins = parseInt(this.config.max_concurrent_logins, 10)
        if (this.config.max_concurrent_logins <= 0) {
          this.config.max_concurrent_logins = null
        }
        this.updateAppConfigItems()
      },
    },
    components: {
      SettingSwitch,
      SettingSelect,
    },
  }
</script>

<style lang="scss" scoped>
  #app .s-signupRow {
    height: 64px;
    margin-left: 20px;

    &.-disabled {
      filter: grayscale(100%);
      opacity: 0.4;
      pointer-events: none;
    }
  }
</style>
