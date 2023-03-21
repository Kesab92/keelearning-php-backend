<template>
  <div>
    <div class="headline mb-2">Notifications</div>
    <div class="body-1 mb-3">
      Unser System versendet diverse Notifications an User und Admins. Über den Schieberegel auf der linken Seite haben Sie die Möglichkeit, die Notifications zu aktivieren bzw. zu deaktivieren. In der rechten Spalte "Usergesteuert" können Sie einen Haken setzen, wenn Sie möchten, dass Ihre User selbst darüber entscheiden, ob sie Notifications erhalten wollen. Einige Notifications sind systemrelevant und können nicht konfiguriert werden.
    </div>
    <div
      v-for="settingsGroup in notificationGroups"
      :key="settingsGroup.title">
      <v-layout
        v-if="settingsGroup.notifications.length"
        row
        class="s-settingNotifications__layout mb-2 mt-4 ">
        <v-flex>
          <h4 class="sectionHeader">
            {{ settingsGroup.title }}
          </h4>
        </v-flex>
        <div>Usergesteuert</div>
      </v-layout>
      <v-layout
        v-for="notification in settingsGroup.notifications"
        :key="notification.mailTemplate"
        row
        class="s-settingNotifications__layout">

        <SettingSwitch
          v-if="notification.editable && profileSettings['notification_' + notification.mailTemplate + '_enabled']"
          type="profileSetting"
          :setting="`notification_${notification.mailTemplate}_enabled`"
          :settings="profileSettings"
          :label="notification.title"
          :description="notification.description"
          :is-candy="isCandy"
          class="mb-2"
          style="pointer-events: auto;"
          @updateSetting="updateSetting">
          <template v-slot:append_label>
            <v-btn
              v-if="myRights['mails-edit']"
              :href="`/mails?edit=${notification.mailTemplate}`"
              flat
              icon
              color="black"
              @click.stop=""
              class="s-settingNotifications__settingButton mr-0 mb-0">
              <v-icon
                size="18"
                color="#aaa">
                settings
              </v-icon>
            </v-btn>
          </template>
        </SettingSwitch>
        <LockedSetting
          v-else
          :label="notification.title"
          :hint="notification.description"
          class="mb-2"
        >
          <template v-slot:append_label>
            <v-btn
              v-if="myRights['mails-edit']"
              :href="`/mails?edit=${notification.mailTemplate}`"
              flat
              icon
              color="black"
              class="s-settingNotifications__settingButton mr-0 mb-0">
              <v-icon
                size="18"
                color="#aaa">
                settings
              </v-icon>
            </v-btn>
          </template>
        </LockedSetting>
        <div class="s-settingNotifications__checkboxContainer">
          <v-checkbox
            v-if="notification.editable"
            v-model="profileSettings['notification_' + notification.mailTemplate + '_user_manageable'].value"
            :disabled="!profileSettings['notification_' + notification.mailTemplate + '_enabled'].value"
            class="d-inline-block mt-0"
            @change="updateUserManageableSetting(notification.mailTemplate, $event)" />
        </div>
      </v-layout>
    </div>
  </div>
</template>

<script>
import AreaMixin from './areaMixin'
import notificationDefinitions from '../notifications'
import LockedSetting from "../LockedSetting";
import SettingSwitch from "../input-types/SettingSwitch"
import {mapGetters} from "vuex";

export default {
  components: {SettingSwitch, LockedSetting},
  mixins: [AreaMixin],
  computed: {
    ...mapGetters({
      allAppSettings: 'app/appSettings',
      allAppProfileSettings: 'app/appProfileSettings',
      myRights: 'app/myRights',
    }),
    notificationGroups() {
      return notificationDefinitions.filter(settingsGroup => {
        if (settingsGroup.module && !this.availableModules.includes(settingsGroup.module)) {
          return false
        }
        return true
      }).map(settingsGroup => {
        const notifications = settingsGroup.notifications.filter((notification) => {
          if (notification.requiresOneOf && !this.hasOneOfSettings(notification.requiresOneOf)) {
            return false
          }
          return true
        })
        return {
          ...settingsGroup,
          notifications,
        }
      })
    },
  },
  methods: {
    updateUserManageableSetting(mailTemplate, value) {
      this.$emit('updateSetting', {
        type: 'profileSetting',
        setting: `notification_${mailTemplate}_user_manageable`,
        value: value
      })
    },
    hasSetting(setting) {
      if (!Object.keys(this.allAppSettings).length) {
        return false
      }
      if(this.allAppSettings[setting] !== undefined) {
        return this.allAppSettings[setting] == '1'
      }
      return this.allAppProfileSettings(this.profileId)[setting] == '1'
    },
    hasOneOfSettings(settings) {
      return settings.some((setting) => this.hasSetting(setting))
    },
  }
}
</script>

<style lang="scss">
  #app .s-settingNotifications__layout{
    max-width:750px;
  }
  #app .s-settingNotifications__settingButton.v-btn{
    position: absolute;
    margin-top:-6px;
    &:hover{
      position: absolute;
      margin-top:-6px;
    }
  }
  #app .s-settingNotifications__checkboxContainer{
    min-width:106px;
    text-align: center;
  }
</style>
