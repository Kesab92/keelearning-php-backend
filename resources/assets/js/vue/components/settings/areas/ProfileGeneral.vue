<template>
  <div>
    <div class="headline mb-1">Allgemeine Einstellungen der Benutzeransicht</div>
    <div class="body-1 mb-4">
      Diese Einstellungen werden in Echtzeit in der App aktiv.

      <div v-if="currentProfile.tags.length">
        TAGs:
        <v-chip
          :key="tag.id"
          disabled
          small
          v-for="tag in currentProfile.tags">
          {{ tag.label }}
        </v-chip>
      </div>
    </div>

    <v-form
      v-model="formIsValid">
      <div class="body-2 mb-3">App Name</div>
      <v-layout
        row>
        <v-flex
          class="pr-4"
          md6
          xs12>
          <v-text-field
            v-model="config.app_name"
            :counter="45"
            hint="Der App Name der den Nutzern angezeigt wird"
            label="App Name"
            outline>
            <v-tooltip
              v-if="!isCandy"
              slot="prepend"
              bottom>
              <v-icon slot="activator">admin_panel_settings</v-icon>
              Nur für Superadmins
            </v-tooltip>
          </v-text-field>

          <v-text-field
            v-if="superadmin"
            v-model="config.subdomain"
            :counter="30"
            hint="Wenn dieser Wert geändert wird, müssen wir auch die Serverkonfiguration anpassen"
            label="Subdomain"
            outline
            suffix=".keelearning.de">
            <v-tooltip
              slot="prepend"
              bottom>
              <v-icon slot="activator">admin_panel_settings</v-icon>
              Nur für Superadmins
            </v-tooltip>
          </v-text-field>

          <v-text-field
            v-if="superadmin"
            v-model="config.ios_app_id"
            outline
            label="iOS App id"
            hint="Im Format com.company.app">
            <v-tooltip
              slot="prepend"
              bottom>
              <v-icon slot="activator">admin_panel_settings</v-icon>
              Nur für Superadmins
            </v-tooltip>
          </v-text-field>

          <v-text-field
            v-model="config.slug"
            :counter="30"
            :rules="[$rules.minChars(2)]"
            hint="Wenn dieser Wert geändert wird, müssen evtl. alle Nutzer über die neue App ID informiert werden."
            label="App ID"
            outline />
        </v-flex>
        <v-flex
          md6
          xs12>
          <v-text-field
            v-model="config.app_name_short"
            :counter="12"
            hint="Kurzform des App Namens die z.B. auf dem Android Homescreen angezeigt wird"
            label="App Name (Kurzform)"
            outline>
            <v-tooltip
              v-if="!isCandy"
              slot="prepend"
              bottom>
              <v-icon slot="activator">admin_panel_settings</v-icon>
              Nur für Superadmins
            </v-tooltip>
          </v-text-field>
          <v-text-field
            v-if="superadmin"
            v-model="config.external_domain"
            hint="Wenn dieser Wert geändert wird, müssen wir auch die Serverkonfiguration anpassen"
            label="Externe Domain"
            outline
            placeholder="elearning.yourcompany.com">
            <v-tooltip
              slot="prepend"
              bottom>
              <v-icon slot="activator">admin_panel_settings</v-icon>
              Nur für Superadmins
            </v-tooltip>
          </v-text-field>

          <v-text-field
            v-if="superadmin"
            v-model="config.android_app_id"
            outline
            label="Android App id"
            hint="Im Format com.company.app">
            <v-tooltip
              slot="prepend"
              bottom>
              <v-icon slot="activator">admin_panel_settings</v-icon>
              Nur für Superadmins
            </v-tooltip>
          </v-text-field>

          <v-text-field
            v-if="superadmin"
            v-model="config.native_app_schema"
            outline
            label="Native App Schema"
            hint="ohne ://">
            <v-tooltip
              slot="prepend"
              bottom>
              <v-icon slot="activator">admin_panel_settings</v-icon>
              Nur für Superadmins
            </v-tooltip>
          </v-text-field>
        </v-flex>
      </v-layout>

      <div class="body-2 mb-3">Soziale Medien</div>

      <v-layout
        row>
        <v-flex
          class="pr-4"
          md6
          xs12>
          <v-text-field
            v-model="config.facebook_url"
            :rules="[$rules.url]"
            placeholder="https://ihre.domain.com"
            hint="Der Link zu Ihrer Facebook Seite"
            label="Facebook"
            outline/>
          <v-text-field
            v-model="config.instagram_url"
            :rules="[$rules.url]"
            placeholder="https://ihre.domain.com"
            hint="Der Link zu Ihrer Instagram Seite"
            label="Instagram"
            outline/>
        </v-flex>

        <v-flex
          md6
          xs12>
          <v-text-field
            v-model="config.twitter_url"
            :rules="[$rules.url]"
            placeholder="https://ihre.domain.com"
            hint="Der Link zu Ihrem Twitter Account"
            label="Twitter"
            outline/>
          <v-text-field
            v-model="config.youtube_url"
            :rules="[$rules.url]"
            placeholder="https://ihre.domain.com"
            hint="Der Link zu Ihrem YouTube Kanal"
            label="YouTube"
            outline/>
        </v-flex>
      </v-layout>
    <div class="mt-4">
      <SettingSwitch
        :is-candy="isCandy"
        :settings="profileSettings"
        description="Zeigt in der App den Like Button und die Anzahl der Likes an"
        label="Social Media Funktionen"
        setting="enable_social_features"
        type="profileSetting"
        @updateSetting="updateSetting"/>
    </div>
    <div class="headline mb-1">User</div>
      <div class="mt-2">
        <div class="body-1 mb-2">
          Wie viel Tage vorher soll ein User informiert werden, bevor sein Account gelöscht wird?
          <v-btn :href="`/mails?edit=ExpirationReminder`"
            flat
            icon
            color="black">
            <v-icon
              size="18"
              color="#aaa">
              settings
            </v-icon>
          </v-btn>
        </div>
        <v-flex
          md6
          xs12>
        <v-text-field
          v-model="config.days_before_user_deletion"
          placeholder="5"
          label="Anzahl Tage vor dem Löschdatum User informieren"
          type="number"
          outline/>
        </v-flex>
        <SettingSwitch
          :is-candy="isCandy"
          :settings="profileSettings"
          description="Wenn aktiviert, können Benutzer neben den Standard Avataren auch eigene Bilder hochladen"
          label="Individuellen Avatar Upload erlauben"
          setting="allow_custom_avatars"
          type="profileSetting"
          @updateSetting="updateSetting"/>
        <SettingSwitch
          :is-candy="isCandy"
          :settings="profileSettings"
          label="Realname (wenn vorhanden) statt Benutzername in der App verwenden"
          setting="use_real_name_as_displayname_frontend"
          type="profileSetting"
          @updateSetting="updateSetting"/>
      </div>
      <v-btn
        :disabled="isSaving || !formIsValid"
        :loading="isSaving"
        class="ml-0"
        color="primary"
        @click="updateAppConfigItems">
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
import AreaMixin from "./areaMixin"
import SettingSwitch from "../input-types/SettingSwitch"

export default {
  mixins: [AreaMixin],
  data() {
    return {
      formIsValid: true,
      isSaving: false,
      savingSuccess: false,
      config: {
        app_name: null,
        app_name_short: null,
        subdomain: null,
        external_domain: null,
        facebook_url: null,
        twitter_url: null,
        instagram_url: null,
        youtube_url: null,
        slug: null,
        ios_app_id: null,
        android_app_id: null,
        native_app_schema: null,
        days_before_user_deletion:null,
      },
    }
  },
  computed: {
    currentProfile() {
      return this.profiles.find(profile => profile.id === this.profileId)
    },
  },
  components: {
    SettingSwitch,
  },
}
</script>
