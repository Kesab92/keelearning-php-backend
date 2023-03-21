<template>
  <div>
    <div class="headline mb-5">Allgemeine Einstellungen für die Adminseite</div>

    <DummyUser
      v-if="superadmin"
      class="mb-5" />

    <SettingSwitch
      type="appSetting"
      :is-candy="isCandy"
      setting="has_candy_frontend"
      :settings="appSettings"
      label="Die App nutzt primär das neue Candy Frontend"
      description="Dadurch werden neue Einstellungen für normale Admins sichtbar und in Benutzer E-Mails werden die neuen Links genutzt"
      @updateSetting="updateSetting" />

    <SettingSwitch
      type="appSetting"
      :is-candy="isCandy"
      setting="use_subcategory_system"
      :settings="appSettings"
      label="Kategorien in Oberkategorien unterteilen"
      description="Dadurch muss bei jeder Kategorie eine Oberkategorie hinterlegt werden."
      @updateSetting="updateSetting" />

    <SettingSwitch
      type="appSetting"
      :is-candy="isCandy"
      setting="hide_emails_backend"
      :settings="appSettings"
      label="E-Mail Adressen der Nutzer ausblenden (Adminseite)"
      @updateSetting="updateSetting" />

    <SettingSwitch
      type="appSetting"
      :is-candy="isCandy"
      setting="hide_personal_data"
      :settings="appSettings"
      label="Keine personenbezogenen Daten anzeigen"
      description="Blendet auf der Adminseite personenbezogenen Daten aus. Die Qualifikationshistorie wird komplett deaktiviert."
      @updateSetting="updateSetting" />

    <SettingSwitch
      type="appSetting"
      :is-candy="isCandy"
      setting="hide_personal_data_for_external_users"
      :settings="appSettings"
      label="Externe Empfänger - keine personenbezogene Daten senden"
      description="Entfernt personenbezogene Daten aus E-Mails an Adressen, die keinem Adminuser zugeordnet worden sind."
      @updateSetting="updateSetting" />

    <SettingSwitch
      type="appSetting"
      :is-candy="isCandy"
      setting="save_user_ip_info"
      :settings="appSettings"
      label="Herkunftsland der User-IP speichern"
      description="Extrahiert beim Login der Nutzer aus der IP das Herkunftsland"
      @updateSetting="updateSetting" />

    <SettingSwitch
      type="appSetting"
      :is-candy="isCandy"
      setting="show_latex_fields"
      :settings="appSettings"
      label="LaTeX-Felder anzeigen"
      description="Dadurch wird der Latex Editor im Fragenpool aktiv"
      @updateSetting="updateSetting" />

    <SettingSwitch
      type="appSetting"
      :is-candy="isCandy"
      setting="hide_question_time_setting"
      :settings="appSettings"
      label="Individuelle Zeiten pro Frage deaktivieren"
      description="Dadurch kann im Fragenpool nicht mehr pro Frage eingestellt werden wie lange die Benutzer zum beantworten Zeit haben"
      @updateSetting="updateSetting" />

    <SettingSwitch
      type="appSetting"
      :is-candy="isCandy"
      setting="hide_question_attachments"
      :settings="appSettings"
      label="Fragenanhänge deaktivieren"
      description="Dadurch können im Fragenpool keine Anhänge bei Fragen mehr hochgeladen werden"
      @updateSetting="updateSetting" />
  </div>
</template>

<script>
  import AreaMixin from './areaMixin'
  import SettingSwitch from "../input-types/SettingSwitch"
  import DummyUser from "../special/DummyUser"
  export default {
    mixins: [AreaMixin],
    components: {
      SettingSwitch,
      DummyUser,
    },
  }
</script>
