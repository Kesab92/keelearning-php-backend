<template>
  <div>
    <div class="headline mb-1">Einstellungen für Tests</div>
    <div class="body-1 mb-5">Diese Einstellungen werden in Echtzeit in der App aktiv.</div>

    <SettingSwitch
      v-for="(setting, settingKey) in testSettings"
      :key="settingKey"
      :is-candy="isCandy"
      type="profileSetting"
      :setting="settingKey"
      :settings="profileSettings"
      :label="setting.label"
      :description="setting.description"
      @updateSetting="updateSetting" />

  </div>
</template>

<script>
  import AreaMixin from './areaMixin'
  import SettingSwitch from '../input-types/SettingSwitch'

  export default {
    mixins: [AreaMixin],
    data() {
      return {
        testSettings: {
          'hide_given_test_answers': {
            label: 'Test-Antworten nach dem Test ausblenden',
            description: 'Ansonsten sieht der Benutzer auf der Test Ergebnisseite nur ob er richtig oder falsch geantwortet hat'
          },
        },
      }
    },
    components: {
      SettingSwitch,
    },
  }
</script>
