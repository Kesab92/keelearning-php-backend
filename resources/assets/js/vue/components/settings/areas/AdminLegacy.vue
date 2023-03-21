<template>
  <div>
    <div class="headline mb-5">Veraltete Einstellungen</div>

    <SettingSwitch
      v-for="(setting, settingKey) in hideAreas"
      :key="settingKey"
      type="appSetting"
      :is-candy="isCandy"
      :setting="settingKey"
      :settings="appSettings"
      :label="setting.label"
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
          'wbt_enabled': {
            label: 'WBT-Funktion aktivieren',
            description: 'Erlaubt den Upload von WBTs in der Mediathek',
          },
          'scorm_wbts_enabled': {
            label: 'SCORM WBTs aktivieren',
            description: 'Erlaubt das Ausspielen von SCORM WBTs. Vor der Aktivierung müssen dem Kunden die Einschränkungen kommuniziert werden.',
          },
          'hide_multiple_questions_types': {
            label: 'Alternative Fragetypen deaktivieren',
            description: 'Erlaubt dann nur noch das Erstellen von Single Choice Fragen',
          },
          'hide_question_feedback': {
            label: 'Fragen Feedback deaktivieren',
            description: 'Blendet beim Bearbeiten von Fragen das Feedback Feld aus',
          },
          'hide_tag_groups': {
            label: 'TAG-Gruppen deaktivieren',
            description: 'Deaktiviert den Menüeintrag zu den TAG-Gruppen im Adminbereich',
          },
          'sort_tests_alphabetically': {
            label: 'Tests alphabetisch sortieren',
            description: 'Sortiert in alten Frontends die Tests alphabetisch',
          },
          'sort_categories_alphabetically': {
            label: 'Kategorien alphabetisch sortieren',
            description: 'Sortiert in alten Frontends die Kategorien alphabetisch',
          },
          'sort_learning_materials_alphabetically': {
            label: 'Lernmaterialien alphabetisch sortieren',
            description: 'Sortiert in alten Frontends die Lernmaterialien alphabetisch',
          },
        }
      }
    },
    components: {
      SettingSwitch,
    },
  }
</script>
