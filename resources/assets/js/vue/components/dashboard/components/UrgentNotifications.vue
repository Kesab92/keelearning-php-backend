<template>
  <div
    class="scroll-y"
    style="max-height:300px">
    <template v-if="allStats.dashboard">
      <v-alert
        v-for="(notification, index) in notifications"
        :key="index"
        :value="true"
        :type="notification.type"
        :class="{
          'clickable': !!notification.url,
        }"
        @click="open(notification.url)">
        {{ notification.message }}
        <div
          v-if="notification.users && notification.users[0].username"
          class="s-users">
          <div
            v-for="user in take10(notification.users)"
            :key="user.id"
            class="s-userEntry">
            <img
              :src="`/users/${user.id}/avatar`"
              class="s-avatar">
            {{ user.username }}
          </div>
          <div v-if="notification.users.length > 10" class="s-userEntry">
            ... und {{ notification.users.length - 10 }} weitere
          </div>
        </div>
      </v-alert>
      <v-alert
        v-if="!notifications.length"
        :value="true"
        color="grey lighten-4 grey--text text--darken-1">
        Keine akuten Meldungen vorhanden
      </v-alert>
    </template>
    <template v-else>
      <v-alert
        color="grey lighten-4"
        class="cursor-progress"
        :value="true">
        <div class="c-skeletonLoader -bigLine" style="width:75%"/>
        <div class="c-skeletonLoader -bigLine mt-4" style="width:40%"/>
      </v-alert>
      <v-alert
        color="grey lighten-4"
        class="cursor-progress mt-4"
        :value="true">
        <div class="c-skeletonLoader -bigLine" style="width:60%"/>
      </v-alert>
    </template>
  </div>
</template>

<script>
import {mapGetters} from 'vuex'

export default {
  computed: {
    ...mapGetters({
      allStats: 'stats/stats',
      myRights: 'app/myRights',
    }),
    notifications() {
      if (!this.allStats.dashboard) {
        return null
      }
      if (!this.stats) {
        return []
      }
      let notifications = []
      if (this.stats.lockedUsers.length) {
        notifications.push({
          type: 'error',
          url: '/users#/users/%7B"filter":"failed_login"%7D',
          message: `${this.stats.lockedUsers.length} User gesperrt - neues PW benötigt`,
          users: this.stats.lockedUsers,
        })
      }
      if (this.stats.usersWithoutPlayableCategories.length) {
        notifications.push({
          type: 'error',
          url: '/users#/users/%7B"filter":"without_category"%7D',
          message: `${this.stats.usersWithoutPlayableCategories.length} User ohne spielbare Kategorie`,
          users: this.stats.usersWithoutPlayableCategories,
        })
      }
      if (this.stats.reportedComments && this.stats.reportedComments.count) {
        notifications.push({
          type: 'error',
          url: '/comments#/comments/%7B"selectedFilters":%5B"status_unresolved"%5D%7D',
          message: `${this.stats.reportedComments.count} ${this.stats.reportedComments.count > 1 ? 'Kommentare wurden' : 'Kommentar wurde'} gemeldet`,
        })
      }
      if (this.stats.appointmentsWithoutParticipant && this.stats.appointmentsWithoutParticipant.count && this.myRights['appointments-edit']) {
        notifications.push({
          type: 'error',
          url: '/appointments#/appointments%7B"filter":"active_without_participants"%7D',
          message: `${this.stats.appointmentsWithoutParticipant.count} ${this.stats.appointmentsWithoutParticipant.count > 1 ? 'Termine haben' : 'Termin hat'} keine Teilnehmer`,
        })
      }
      return notifications
    },
    stats() {
      if (!this.allStats.dashboard) {
        return null
      }
      return this.allStats.dashboard.urgentnotifications || null
    },
  },
  methods: {
    open(url) {
      if (url) {
        window.location = url
      }
    },
    take10(list) {
      return list.slice(0, 10)
    },
  },
}
</script>

<style lang="scss" scoped>
$avatarSize: 30px;

#app {
   .s-users {
    max-height: 120px;
    overflow-y: auto;
  }

  .s-userEntry {
    line-height: $avatarSize;
    margin-top: 10px;

    &::after {
      clear: both;
      content: '';
      display: block;
    }
  }

  .s-avatar {
    border-radius: 50%;
    float: left;
    height: $avatarSize;
    margin-right: 10px;
    width: $avatarSize;
  }
}
</style>
