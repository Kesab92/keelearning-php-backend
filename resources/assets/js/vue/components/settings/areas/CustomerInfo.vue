<template>
  <div>
    <div class="headline mb-1">Einstellungen zum Kunden</div>
    <div class="body-1 mb-5">Diese Informationen sind nur für Superadmins sichtbar</div>

    <v-layout
      row>
      <v-flex
        shrink
        class="pr-5">
        <div>
          <SettingSwitch
            v-for="(setting, settingKey) in modules"
            :key="settingKey"
            :is-candy="isCandy"
            type="appSetting"
            :setting="settingKey"
            :settings="appSettings"
            :label="setting.label"
            :description="setting.description"
            :class="{
              ['ml-' + setting.indent]: !!setting.indent,
            }"
            @updateSetting="updateSetting" />
        </div>
      </v-flex>
      <v-flex
        grow
        style="max-width: 450px">
        <v-alert
          :value="true"
          color="info"
          icon="info"
          outline
        >
          {{ customerInfo.active_users }} aktive Benutzer in den letzten 6 Monaten
        </v-alert>
        <v-alert
          :value="true"
          color="info"
          icon="info"
          outline
          class="mb-3"
        >
          {{ customerInfo.running_games }} laufende Duelle
        </v-alert>

        <v-text-field
          label="Telefonsupport-Nummer"
          outline
          type="text"
          v-model="customerInfo.support_phone_number"/>

        <v-text-field
          label="Gekaufte Lizenzen"
          outline
          hint="Aktuell nur für Dokumentationszwecke"
          type="number"
          v-model="customerInfo.user_licences"/>

        <v-textarea
          label="Interne Notizen"
          hint="Diese Notizen sind nur für Superadmins sichtbar"
          outline
          auto-grow
          v-model="customerInfo.internal_notes"/>

        <v-autocomplete
          :items="allApps"
          deletable-chips
          dense
          persistent-hint
          item-text="app_name"
          item-value="id"
          label="Mandanten Vorlagen"
          hint="Für diese Apps können Kursvorlagen freigeschalten werden"
          multiple
          small-chips
          placeholder="Keine Mandanten Vorlagen"
          outline
          :allow-overflow="false"
          v-model="templateInheritanceChildren" />

        <v-text-field
          disabled
          outline
          v-model="customerInfo.defaultLanguage"
          label="Default Sprache" />

        <v-select
          multiple
          chips
          outline
          v-model="customerInfo.languages"
          label="Sprachen"
          :items="languages"
          />

        <v-btn
          color="primary"
          class="ml-0"
          :loading="isSaving"
          :disabled="isSaving"
          @click="saveData">
          Speichern
          <template
            v-if="savingSuccess"
            v-slot:loader>
            <v-icon light>done</v-icon>
          </template>
        </v-btn>
      </v-flex>
    </v-layout>

  </div>
</template>

<script>
  import AreaMixin from "./areaMixin"
  import SettingSwitch from "../input-types/SettingSwitch"

  export default {
    mixins: [AreaMixin],
    data() {
      return {
        customerInfo: {
          support_phone_number: '',
          internal_notes: '',
          user_licences: '',
          active_users: null,
          running_games: null,
        },
        languages: [
          {
            text: 'Deutsch (Du)',
            value: 'de',
          },
          {
            text: 'Albanisch',
            value: 'al',
          },
          {
            text: 'Bulgarisch',
            value: 'bg',
          },
          {
            text: 'Tschechisch',
            value: 'cs',
          },
          {
            text: 'Deutsch (Sie)',
            value: 'de_formal',
          },
          {
            text: 'Englisch',
            value: 'en',
          },
          {
            text: 'Spanisch',
            value: 'es',
          },
          {
            text: 'Französisch',
            value: 'fr',
          },
          {
            text: 'Kroatisch',
            value: 'hr',
          },
          {
            text: 'Ungarisch',
            value: 'hu',
          },
          {
            text: 'Italienisch',
            value: 'it',
          },
          {
            text: 'Japanisch',
            value: 'jp',
          },
          {
            text: 'Niederländisch',
            value: 'nl',
          },
          {
            text: 'Norwegisch',
            value: 'no',
          },
          {
            text: 'Polnisch',
            value: 'pl',
          },
          {
            text: 'Portugiesisch',
            value: 'pt',
          },
          {
            text: 'Rumänisch',
            value: 'ro',
          },
          {
            text: 'Russisch',
            value: 'ru',
          },
          {
            text: 'Serbisch',
            value: 'sr',
          },
          {
            text: 'Türkisch',
            value: 'tr',
          },
          {
            text: 'Chinesisch',
            value: 'zh',
          },
        ],
        templateInheritanceChildren: [],
        allApps: [],
        isSaving: false,
        savingSuccess: false,
        modules: {
          'module_quiz': {
            label: 'Quizmodul aktivieren',
            description: 'Aktiviert Bots & Teams',
          },
          'module_questions': {
            label: 'Fragenpool aktivieren',
          },
          'module_suggested_questions': {
            label: 'User Eingereichte Fragen aktivieren',
          },
          'module_learningmaterials': {
            label: 'Mediathek aktivieren',
          },
          'module_powerlearning': {
            label: 'Powerlearning aktivieren',
          },
          'module_index_cards': {
            label: 'Karteikarten aktivieren (veraltet)',
          },
          'module_tests': {
            label: 'Tests aktivieren',
          },
          'module_news': {
            label: 'News aktivieren',
          },
          'module_webinars': {
            label: 'Webinare aktivieren',
          },
          'module_vouchers': {
            label: 'Vouchers aktivieren',
          },
          'module_courses': {
            label: 'Kurse aktivieren',
          },
          'module_advertisements': {
            label: 'Banner (Anzeigen) aktivieren',
          },
          'module_keywords': {
            label: 'Schlagwörter aktivieren',
          },
          'module_comments': {
            label: 'Kommentare aktivieren',
          },
          'module_competitions': {
            label: 'Gewinnspiele aktivieren',
          },
          'module_homepage_components': {
            label: 'Homepage-Builder aktivieren',
          },
          'module_appointments': {
            label: 'Termine aktivieren',
          },
          'module_forms': {
            label: 'Formulare aktivieren',
          },
          'has_login_limiations': {
            label: 'Anzahl gleichzeitiger Logins beschränken können',
          },
          'hide_intercom_chat': {
            label: 'Support-Chat deaktivieren',
          },
        },
      }
    },
    created() {
      this.fetchData()
    },
    methods: {
      fetchData() {
        Promise.all([
          this.getCustomerInfo(),
          this.getTemplateInheritances(),
        ]).catch(() => {
          alert('Die Einstellungen konnten leider nicht geladen werden. Bitte probieren Sie es später erneut.')
        })
      },
      saveData() {
        this.savingSuccess = false
        this.isSaving = true
        Promise.all([
          this.updateCustomerInfo(),
          this.updateTemplateInheritances(),
        ]).catch(() => {
          alert('Die Einstellungen konnten leider nicht gespeichert werden. Bitte probieren Sie es später erneut.')
          this.isSaving = false
        }).then(() => {
          this.savingSuccess = true
          setTimeout(() => (this.isSaving = false), 1000)
        })
      },
      getCustomerInfo() {
        return axios.get('/backend/api/v1/settings/customerInfo').then(response => {
          this.customerInfo = response.data
        })
      },
      updateCustomerInfo() {
        return axios.post('/backend/api/v1/settings/customerInfo', {
          support_phone_number: this.customerInfo.support_phone_number,
          internal_notes: this.customerInfo.internal_notes,
          user_licences: this.customerInfo.user_licences,
          languages: this.customerInfo.languages,
        })
      },
      getTemplateInheritances() {
        return axios.get('/backend/api/v1/settings/templateInheritances').then(response => {
          this.templateInheritanceChildren = response.data.templateInheritanceChildren
          this.allApps = response.data.apps
        })
      },
      updateTemplateInheritances() {
        return axios.post('/backend/api/v1/settings/templateInheritances', {
          children: this.templateInheritanceChildren,
        })
      },
    },
    components: {
      SettingSwitch,
    },
  }
</script>
