<template>
  <div v-if="showComponent">
    <div
      v-if="!newsData.notification_sent_at">
      <div
        v-if="isPublished || newsData.published_at !== null"
        class="subheading">Benachrichtigung
      </div>
      <template v-if="isPublished">
        <v-btn
          :loading="isNotificationSending"
          color="primary"
          class="ml-0"
          @click="notify"
        >
          Benachrichtigung senden
        </v-btn>
        <v-btn
          class="ml-0"
          flat
          icon
          href="/mails?edit=NewsPublishedInfo"
        >
          <v-icon>settings</v-icon>
        </v-btn>
        <p class="grey--text">
          Benachrichtigung an User wird sofort versendet.
        </p>
      </template>
      <template v-else>
        <template v-if="newsData.published_at !== null">
          <template v-if="!newsData.send_notification">
            <v-btn
              :loading="isNotificationSending"
              color="primary"
              class="ml-0"
              @click="notify"
            >
              Benachrichtigung vormerken
            </v-btn>
            <v-btn
              class="ml-0"
              flat
              icon
              href="/mails?edit=NewsPublishedInfo"
            >
              <v-icon>settings</v-icon>
            </v-btn>
          </template>
          <p
            v-else
            class="grey--text">
            Benachrichtigung an die Benutzer wird am {{ newsData.published_at | date }} gesendet.
          </p>
        </template>
      </template>
    </div>
    <v-alert
      type="success"
      outline
      :value="true"
      v-if="newsData.notification_sent_at"
    >
      Benachrichtigung wurde am {{ newsData.notification_sent_at | dateTime }} versendet.
    </v-alert>
  </div>
</template>

<script>
import moment from 'moment'
import { mapGetters } from 'vuex'

export default {
  props: ["newsEntry"],
  data() {
    return {
      newsData: null,
      isNotificationSending: false,
    }
  },
  watch: {
    newsEntry: {
      handler() {
        this.newsData = JSON.parse(JSON.stringify(this.newsEntry))
      },
      immediate: true,
    },
  },
  computed: {
    ...mapGetters({
      appProfileSettings: 'app/appProfileSettings',
      profiles: 'app/profiles',
    }),
    isPublished() {
      if (!this.newsData.published_at) {
        return false
      }
      if (moment(this.newsData.published_at).isBefore(moment())) {
        return true
      }
      return false
    },
    showComponent() {
      // show this component only if the relevant
      // notifications are actually active, or
      // if we sent one before
      if (this.newsData.notification_sent_at) {
        return true
      }
      for (let ii = 0; ii < this.profiles.length; ii++) {
        if (this.appProfileSettings(this.profiles[ii].id).notification_NewsPublishedInfo_enabled) {
          return true
        }
      }
      return false
    },
  },
  methods: {
    async notify() {
      if (this.isNotificationSending || this.newsData.send_notification || this.newsData.notification_sent_at) {
        return
      }
      this.isNotificationSending = true
      await this.$store.dispatch("news/notify", {
        id: this.newsData.id,
      })
      this.isNotificationSending = false
    },
  }
}
</script>
