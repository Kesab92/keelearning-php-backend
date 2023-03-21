<template>
  <div>
    <div class="headline mb-1">Aktivierte Module in der Benutzeransicht</div>
    <div class="body-1 mb-5">Diese Einstellungen werden in Echtzeit in der App aktiv.</div>

    <SettingSwitch
      v-for="(setting, settingKey) in modules"
      :key="settingKey"
      type="profileSetting"
      :setting="settingKey"
      :settings="profileSettings"
      :label="setting.label"
      :description="setting.description"
      :disabled="setting.disabled"
      :is-candy="isCandy"
      @updateSetting="updateSetting" />

  </div>
</template>

<script>
  import AreaMixin from './areaMixin'
  import SettingSwitch from "../input-types/SettingSwitch"
  export default {
    mixins: [AreaMixin],
    data() {
      return {
        modules: {
          'module_news': {
            label: 'News aktivieren',
            disabled: !this.availableModules.includes('module_news'),
          },
          'module_learningmaterials': {
            label: 'Mediathek aktivieren',
            disabled: !this.availableModules.includes('module_learningmaterials'),
          },
          'module_powerlearning': {
            label: 'Powerlearning aktivieren',
            disabled: !this.availableModules.includes('module_powerlearning'),
          },
          'module_indexcards': {
            label: 'Karteikarten aktivieren (veraltet)',
            disabled: !this.availableModules.includes('module_index_cards'),
          },
          'module_quiz': {
            label: 'Quiz aktivieren',
            disabled: !this.availableModules.includes('module_quiz'),
          },
          'module_quiz_teams': {
            label: 'Quiz-Teams aktivieren',
            disabled: !this.availableModules.includes('module_quiz'),
          },
          'module_suggested_questions': {
            label: 'User eingereichte Fragen aktivieren',
            disabled: !this.availableModules.includes('module_suggested_questions'),
          },
          'module_competitions': {
            label: 'Gewinnspiele aktivieren',
            disabled: !this.availableModules.includes('module_quiz') || !this.availableModules.includes('module_competitions'),
          },
          'module_tests': {
            label: 'Tests aktivieren',
            disabled: !this.availableModules.includes('module_tests'),
          },
          'module_webinars': {
            label: 'Webinare aktivieren',
            disabled: !this.availableModules.includes('module_webinars'),
          },
          'module_courses': {
            label: 'Kurse aktivieren',
            disabled: !this.availableModules.includes('module_courses'),
          },
          'module_vouchers': {
            label: 'Vouchers aktivieren',
            disabled: !this.availableModules.includes('module_vouchers'),
          },
          'module_advertisements': {
            label: 'Banner (Anzeigen) aktivieren',
            disabled: !this.availableModules.includes('module_advertisements'),
          },
          'module_keywords': {
            label: 'Schlagwörter aktivieren',
            disabled: !this.availableModules.includes('module_keywords'),
          },
          'module_comments': {
            label: 'Kommentare aktivieren',
            disabled: !this.availableModules.includes('module_comments'),
          },
          'module_appointments': {
            label: 'Termine aktivieren',
            disabled: !this.availableModules.includes('module_appointments'),
          },
          'module_todolists': {
            label: 'Globale Aufgaben aktivieren',
            disabled: !this.availableModules.includes('module_courses'),
          },
        },
      }
    },
    components: {
      SettingSwitch,
    },
  }
</script>
