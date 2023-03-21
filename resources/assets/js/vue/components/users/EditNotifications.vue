<template>
  <div v-if="userData">
    <h4 class="mx-4 mb-0 mt-4">Benachrichtigungen</h4>
    <div class="px-4 pb-4 pt-0">
      <p>
        Die Liste zeigt an, welche Benachrichtigungen der Benutzer empfängt.
      </p>

      <v-list
        class="s-notificationList"
        dense>
        <v-list-tile
          v-for="(mailNotificationLabel, mailNotificationType) in availableMailNotifications"
          :key="mailNotificationType">
          <v-list-tile-action>
            <v-icon
              v-if="!userData.mailNotifications[mailNotificationType].enabled"
              class="grey--text">speaker_notes_off</v-icon>
            <v-icon
              v-else-if="!userData.mailNotifications[mailNotificationType].allowedToDeactivate"
              class="grey--text">done</v-icon>
            <template v-else>
              <v-icon
                v-if="!userData.mailNotifications[mailNotificationType].mail_disabled && !userData.mailNotifications['all'].mail_disabled"
                class="green--text">done</v-icon>
              <v-icon
                v-else
                class="red--text">speaker_notes_off</v-icon>
            </template>
          </v-list-tile-action>
          <v-list-tile-content>
            <div
              :class="{
                'red--text': !userData.mailNotifications[mailNotificationType].enabled && userData.mailNotifications[mailNotificationType].allowedToDeactivate,
                'grey--text': !userData.mailNotifications[mailNotificationType].enabled || !userData.mailNotifications[mailNotificationType].allowedToDeactivate,
            }">
              {{ mailNotificationLabel }}
            </div>
          </v-list-tile-content>
        </v-list-tile>
      </v-list>
    </div>
  </div>
</template>

<script>
import {mapGetters} from "vuex"

export default {
  props: ["user"],
  data() {
    return {
      userData: null,
    }
  },
  computed: {
    ...mapGetters({
      availableMailNotifications: 'users/availableMailNotifications',
    }),
  },
  watch: {
    user: {
      handler() {
        this.userData = JSON.parse(JSON.stringify(this.user))
      },
      immediate: true,
    },
  },
}
</script>


<style lang="scss" scoped>
#app .s-notificationList ::v-deep .v-list__tile__action {
  min-width: 36px;
}
</style>
