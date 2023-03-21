<template>
  <div>
    <div class="headline mb-5">Deaktivierte Bereiche der Adminseite</div>

    <SettingSwitch
      v-for="(setting, settingKey) in hideAreas"
      :key="settingKey"
      type="appSetting"
      :setting="settingKey"
      :settings="appSettings"
      :label="setting.label"
      :is-candy="isCandy"
      :description="setting.description"
      :class="{
        ['ml-' + setting.indent]: !!setting.indent,
      }"
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
        hideAreas: {
          'hide_users': {
            label: 'Benutzerverwaltung deaktivieren',
            description: 'Deaktiviert den Menüeintrag zur Benutzerverwaltung im Adminbereich'
          },
          'hide_user_export': {
            label: 'Benutzerexport deaktivieren',
            description: 'Deaktiviert den Button zum Herunterladen der Benutzer in der Benutzerverwaltung',
          },
          'hide_stats_users': {
            label: 'Benutzer Statistiken deaktivieren',
            description: 'Entfernt den Menüeintrag zu den Benutzer Statistiken im Adminbereich',
          },
          'hide_stats_quiz_challenge': {
            label: 'Quiz-Battle Statistiken deaktivieren',
            description: 'Entfernt den Menüeintrag zu den Quiz Challenge Statistiken im Adminbereich',
          },
          'hide_stats_training': {
            label: 'Training Statistiken deaktivieren',
            description: 'Entfernt den Menüeintrag zu den Trainings Statistiken im Adminbereich',
          },
          'hide_stats_views': {
            label: 'View Statistiken deaktivieren',
            description: 'Entfernt den Menüeintrag zu den Aufruf Statistiken im Adminbereich',
          },
          'hide_stats_wbt': {
            label: 'WBT Statistiken deaktivieren',
            description: 'Entfernt den Menüeintrag zu den WBT Statistiken im Adminbereich',
          },
          'hide_competitions': {
            label: 'Gewinnspiele deaktivieren',
            description: 'Entfernt den Menüeintrag zu den Quiz-Gewinnspiele im Adminbereich',
          },
          'import_questions': {
            label: 'Fragen Import aktivieren',
          },
          'import_index_cards': {
            label: 'Karteikarten Import aktivieren',
          },
          'import_users': {
            label: 'Benutzer Import aktivieren',
          },
          'import_users_delete': {
            label: 'Benutzer Löschfunktion aktivieren',
            indent: 5,
          },
          'has_subpages': {
            label: 'Sub-Seiten aktivieren',
          },
        }
      }
    },
    components: {
      SettingSwitch,
    },
  }
</script>
