<template>
  <div>
    <div class="headline mb-1">Design der App</div>
    <div class="body-1 mb-5">Diese Einstellungen werden in Echtzeit in der App aktiv.</div>

    <v-layout
      row
      class="pa-0 mb-4">
      <v-flex
        shrink
        class="mr-4">
        <ImageUpload
          label="App Icon"
          description="Wird als Favicon angezeigt"
          :profile-id="profileId"
          setting="app_icon"
          :max-width="80"
          :height="170"
          :settings="profileSettings"
          :is-candy="isCandy"
          @setSetting="setSetting"
          @updateSetting="updateSetting"/>
        <div class="grey--text text-lighten-2 mt-2">
          512x512px
        </div>
      </v-flex>
      <v-flex
        shrink
        class="mr-4">
        <ImageUpload
          label="App Icon iOS"
          description="App Icon ohne Transparenz das auf iOS Geräten angezeigt wird"
          :profile-id="profileId"
          setting="app_icon_no_transparency"
          :max-width="80"
          :height="170"
          :settings="profileSettings"
          :is-candy="isCandy"
          :deleteable="true"
          @setSetting="setSetting"
          @updateSetting="updateSetting" />
        <div class="grey--text text-lighten-2 mt-2">
          512x512px (nicht transparent)
        </div>
      </v-flex>
      <v-flex
        shrink
        class="mr-4">
        <ImageUpload
          label="Mobile Logo"
          description="Wird in der mobilen App im Header angezeigt."
          :profile-id="profileId"
          setting="app_logo"
          :max-width="200"
          :height="170"
          :settings="profileSettings"
          :is-candy="isCandy"
          @setSetting="setSetting"
          @updateSetting="updateSetting" />
        <div class="grey--text text-lighten-2 mt-2">
          max. 200x60px
        </div>
      </v-flex>
      <v-flex
        shrink
        class="mr-4">
        <ImageUpload
          label="Desktop Logo"
          description="Wird in der Desktop App über dem Menü angezeigt"
          :profile-id="profileId"
          :max-width="178"
          :height="170"
          :image-background="desktopLogoBackground"
          setting="app_logo_inverse"
          :settings="profileSettings"
          :is-candy="isCandy"
          :deleteable="true"
          @setSetting="setSetting"
          @updateSetting="updateSetting" />
        <div class="grey--text text-lighten-2 mt-2">
          max. 200x60px
        </div>
      </v-flex>
      <v-flex
        shrink
        class="mr-4">
        <ImageUpload
          label="Logo Loginseite"
          description="Wird in der Desktop App auf der Login Seite angezeigt"
          :profile-id="profileId"
          :max-width="178"
          :height="170"
          setting="app_logo_auth"
          :settings="profileSettings"
          :is-candy="isCandy"
          :deleteable="true"
          @setSetting="setSetting"
          @updateSetting="updateSetting" />
        <div class="grey--text text-lighten-2 mt-2">
          max. 400x100px
        </div>
      </v-flex>
    </v-layout>
    <v-layout
      row
      class="pa-0 mb-4">
      <v-flex
        xs12
        md4
        class="pr-4">
        <div class="body-2">Hauptfarben</div>
        <div class="caption mb-3">Diese Farben sollten Sie an Ihre CI anpassen.</div>
        <ColorSetting
          v-for="(setting, settingKey) in mainColors"
          :key="settingKey"
          :setting="settingKey"
          :settings="profileSettings"
          :label="setting.label"
          :description="setting.description"
          :is-candy="isCandy"
          @updateSetting="updateSetting" />

        <SettingSwitch
          type="profileSetting"
          :is-candy="isCandy"
          setting="tablet_light_background"
          :settings="profileSettings"
          label="Light-Theme auf dem Desktop aktivieren"
          description="Auf mobilen Geräten wird immer das Light-Theme verwendet"
          @updateSetting="updateSetting" />
      </v-flex>
      <v-flex
        v-if="superadmin"
        xs12
        md4
        class="pr-4">
        <div class="body-2">Spezielle Farben</div>
        <div class="caption mb-3">Diese Farben werden an wenigen Stellen verwendet und müssen normalerweise nicht angepasst werden.</div>
        <ColorSetting
          v-for="(setting, settingKey) in secondaryColors"
          :key="settingKey"
          :setting="settingKey"
          :settings="profileSettings"
          :label="setting.label"
          :description="setting.description"
          :is-candy="isCandy"
          @updateSetting="updateSetting" />
      </v-flex>
      <v-flex
        v-if="superadmin"
        xs12
        md4>
        <div class="body-2">Layout Farben</div>
        <div class="caption mb-3">Diese Farben sind bereits genau aufeinander abgestimmt und sollten möglichst nicht angepasst werden.</div>
        <ColorSetting
          v-for="(setting, settingKey) in appColors"
          :key="settingKey"
          :setting="settingKey"
          :settings="profileSettings"
          :label="setting.label"
          :description="setting.description"
          :is-candy="isCandy"
          @updateSetting="updateSetting" />
      </v-flex>
    </v-layout>

    <v-layout
      row
      class="pa-0 mb-4">
      <v-flex xs8>
        <ImageUpload
          label="Login Bild"
          description="Wird auf den Login / Registrieren Seiten angezeigt. Wird in einem 550x450px großen Bereich angezeigt"
          :profile-id="profileId"
          setting="auth_background_image"
          :settings="profileSettings"
          :is-candy="isCandy"
          :max-width="500"
          :deleteable="true"
          @setSetting="setSetting"
          @updateSetting="updateSetting" />
      </v-flex>
    </v-layout>
  </div>
</template>

<script>
  import AreaMixin from "./areaMixin"
  import ColorSetting from "../input-types/ColorSetting"
  import ImageUpload from "../input-types/ImageUpload"
  import SettingSwitch from "../input-types/SettingSwitch"

  export default {
    mixins: [AreaMixin],
    data() {
      return {
        mainColors: {
          'color_primary': {
            label: 'Primär',
            description: 'Wird unter anderem für wichtige Buttons und Grafiken verwendet',
          },
          'color_secondary': {
            label: 'Sekundär',
            description: 'Wird für weniger wichtige Buttons und Grafiken verwendet',
          },
          'color_success': {
            label: 'Erfolg',
            description: 'Wird für positive / bestandene Aktionen verwendet',
          },
          'color_medium_success': {
            label: 'Mittlerer Fortschritt',
            description: 'Wird verwendet für Aktionen die noch nicht abgeschlossen sind, aber schon begonnen wurden.',
          },
          'color_error': {
            label: 'Misserfolg',
            description: 'Wird für negative / nicht bestandene Aktionen verwendet',
          },
        },
        secondaryColors: {
          'color_gold': {
            label: '1. Platz',
            description: 'Wird verwendet um dem Nutzer zu gratulieren, z.B. beim Gewinnen eines Quiz',
          },
          'color_silver': {
            label: '2. Platz',
            description: 'Wird verwendet um dem Nutzer zu gratulieren wenn er den zweiten Platz erreicht',
          },
          'color_bronze': {
            label: '3. Platz',
            description: 'Wird verwendet um dem Nutzer zu gratulieren wenn er den dritten Platz erreicht',
          },
          'color_highlight': {
            label: 'Hervorhebung',
            description: 'Wird zum Hervorheben, z.B. bei Suchergebnissen verwendet',
          },
        },
        appColors: {
          'color_dark': {
            label: 'Textfarbe',
            description: 'Wird an den meisten Stellen als Textfarbe verwendet',
          },
          'color_dark_medium_emphasis': {
            label: 'Text (dunkel, hervorgehoben)',
            description: 'Wird für dunklen Text verwendet der hervorgehoben sein soll',
          },
          'color_dark_light_emphasis': {
            label: 'Text (dunkel, hervorgehoben, heller)',
            description: 'Wird für dunklen Text verwendet der heller hervorgehoben sein soll',
          },
          'color_white': {
            label: 'Hintergrund',
            description: 'Wird primär als App Hintergrundfarbe oder für andere sehr helle Bereiche verwendet',
          },
          'color_soft_highlight': {
            label: 'Hervorgehobener Hintergrund',
            description: 'Wird für Elemente verwendet die sich etwas von der Hintergrundfarbe abheben sollen',
          },
          'color_divider': {
            label: 'Trennlinien',
            description: 'Wird primär für Abtrennungen auf hellem Hintergrund verwendet',
          },
        },
      }
    },
    computed: {
      desktopLogoBackground() {
        if(this.profileSettings.tablet_light_background.value) {
          return 'rgb(255, 255, 255)'
        }
        return 'rgb(31, 31, 49)'
      },
    },
    components: {
      ColorSetting,
      ImageUpload,
      SettingSwitch,
    },
  }
</script>
