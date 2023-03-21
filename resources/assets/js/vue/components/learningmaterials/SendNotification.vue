<template>
  <div v-if="showComponent">
    <div class="subheading">Benachrichtigung</div>
    <div
      v-if="!materialData.notification_sent_at"
    >
      <template v-if="isPublished">
        <v-btn
          class="mx-0"
          outline
          @click="activateNotification"
        >
          Benachrichtigung jetzt senden
        </v-btn>
        <v-btn
          class="ml-0"
          flat
          icon
          href="/mails?edit=LearningMaterialsPublished"
        >
          <v-icon>settings</v-icon>
        </v-btn>
        <p class="grey--text">
          Benachrichtigung an User wird sofort versendet.
        </p>
      </template>
      <template v-else>
        <template v-if="!materialData.send_notification">
          <v-btn
            class="mx-0"
            outline
            @click="activateNotification"
          >
            Benachrichtigung vormerken
          </v-btn>
          <v-btn
            class="ml-0"
            flat
            icon
            href="/mails?edit=LearningMaterialsPublished"
          >
            <v-icon>settings</v-icon>
          </v-btn>
        </template>
        <p
          v-else
          class="grey--text">
          Benachrichtigung an die Benutzer wird am {{ material.published_at | date }} gesendet.
        </p>
      </template>
    </div>
    <v-alert
      type="success"
      outline
      :value="true"
      v-if="materialData.notification_sent_at"
    >
      Benachrichtigung wurde am {{ materialData.notification_sent_at | dateTime }} versendet.
    </v-alert>
  </div>
</template>

<script>
import moment from 'moment'
import { mapGetters } from 'vuex'

export default {
  props: ['value', 'material'],
  computed: {
    ...mapGetters({
      appProfileSettings: 'app/appProfileSettings',
      profiles: 'app/profiles',
    }),
    materialData: {
      get() {
        return this.value
      },
      set(data) {
        this.$emit('input', data)
      },
    },
    showComponent() {
      // show this component only if the relevant
      // notifications are actually active, or
      // if we sent one before
      if (this.material.notification_sent_at) {
        return true
      }
      for (let ii = 0; ii < this.profiles.length; ii++) {
        if (this.appProfileSettings(this.profiles[ii].id).notification_LearningMaterialsPublished_enabled) {
          return true
        }
      }
      return false
    },
    isPublished() {
      if (!this.materialData.published_at) {
        return true
      }
      if (moment(this.materialData.published_at).isBefore(moment())) {
        return true
      }
      return false
    },
  },
  methods: {
    activateNotification() {
      this.$emit('activateNotification')
    },
  },
}
</script>
