<template>
  <DashboardCard
    v-if="hasUserRight"
    :class="{
      'cursor-progress': !allStats.dashboard,
    }"
    class="text-xs-center"
    tooltip="Zeigt die letzten Benutzeraktivitäten"
    :no-padding-content="!!allStats.dashboard">
    <template v-slot:title>
      Letzte Aktivitäten
    </template>

    <v-list v-if="allStats.dashboard">
      <v-list-tile
        v-for="activity in activities"
        :key="activity.name"
        avatar
      >
        <v-list-tile-avatar v-if="activity.username">
          <a
            v-if="myRights['users-edit'] || myRights['users-view']"
            :href="`/users#/users/${activity.user_id}/general`">
            <img :src="`/users/${activity.user_id}/avatar`">
          </a>
          <img v-else :src="`/users/${activity.user_id}/avatar`">
        </v-list-tile-avatar>

        <v-list-tile-content class="body-1">
          <v-container class="pa-0">
            <v-layout row fill-height align-center>
              <v-flex xs6 class="grey--text">
                <a
                  v-if="myRights['users-edit'] || myRights['users-view']"
                  :href="`/users#/users/${activity.user_id}/general`"
                  class="grey--text">
                  {{ activity.username || 'User' }}
                </a>
                <template v-else>
                  {{ activity.username || 'User' }}
                </template>
              </v-flex>
              <v-flex grow>
                {{ activity.description }}
              </v-flex>
            </v-layout>
          </v-container>
        </v-list-tile-content>
      </v-list-tile>
    </v-list>
    <template v-else>
      <div class="c-skeletonLoader -bigLine" style="width:40%"/>
      <div class="c-skeletonLoader -bigLine mt-4" style="width:92%"/>
      <div class="c-skeletonLoader -bigLine mt-4" style="width:89%"/>
      <div class="c-skeletonLoader -bigLine mt-4" style="width:90%"/>
      <div class="c-skeletonLoader -bigLine mt-4" style="width:91%"/>
      <div class="c-skeletonLoader -bigLine mt-4" style="width:90%"/>
      <div class="c-skeletonLoader -bigLine mt-4" style="width:88%"/>
    </template>
  </DashboardCard>
</template>

<script>
import {mapGetters} from 'vuex'
import DashboardCard from './global/DashboardCard'

const ACTIVITY_DESCRIPTIONS = {
  2: 'Kurs bestanden',
  3: 'Spiel gestartet',
  4: 'Spiel gestartet',
  9: 'Registriert',
  10: 'Kommentar geschrieben',
  11: 'Feedback gesendet',
  12: 'Frage vorgeschlagen',
  13: 'Test bestanden',
}

export default {
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
      allStats: 'stats/stats',
    }),
    activities() {
      if (!this.allStats.dashboard) {
        return null
      }
      return this.stats.latestActivities.map((activity) => {
        return {
          ...activity,
          description: ACTIVITY_DESCRIPTIONS[activity.type]
        }
      })
    },
    hasUserRight() {
      return this.myRights['users-edit'] || this.myRights['users-view'] || this.myRights['users-stats']
    },
    stats() {
      if (!this.allStats.dashboard) {
        return null
      }
      return this.allStats.dashboard.activities
    },
  },
  components: {
    DashboardCard,
  }
}
</script>

<style lang="scss">
#app .v-avatar img {
  max-width: 40px;
  max-height: 40px;
}
</style>
