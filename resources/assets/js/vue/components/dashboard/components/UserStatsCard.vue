<template>
  <DashboardCard
    :class="{
      'cursor-progress': !stats.dashboard,
    }">
    <template v-if="stats.dashboard">
      <v-layout row justify-space-between class="mt-2">
        <v-flex grow shrink class="grey--text">
          Administratoren
        </v-flex>
        <v-flex shrink>
          {{ stats.dashboard.usercounts.admins | numberFormat }}
        </v-flex>
      </v-layout>
      <v-layout row justify-space-between class="mt-2">
        <v-flex grow class="grey--text">
          User mit Löschdatum
        </v-flex>
        <v-flex shrink>
          {{ stats.dashboard.usercounts.deletionPending | numberFormat }}
        </v-flex>
      </v-layout>
      <v-layout row justify-space-between class="mt-2">
        <v-flex grow class="grey--text">
          Nutzungsbedingungen nicht akzeptiert
        </v-flex>
        <v-flex shrink>
          {{ stats.dashboard.usercounts.tosNotAccepted | numberFormat }}
        </v-flex>
      </v-layout>
      <v-layout row justify-space-between class="mt-2">
        <v-flex shrink class="grey--text">
          Deaktivierte User
        </v-flex>
        <v-flex shrink>
          {{ stats.dashboard.usercounts.disabled | numberFormat }}
        </v-flex>
      </v-layout>
    </template>
    <template v-else>
      <div class="c-skeletonLoader -line" style="width:40%"/>
      <div class="c-skeletonLoader -line mt-3" style="width:45%"/>
      <div class="c-skeletonLoader -line mt-3" style="width:60%"/>
      <div class="c-skeletonLoader -line mt-3" style="width:43%"/>
    </template>
  </DashboardCard>
</template>

<script>
import {mapGetters} from 'vuex'
import DashboardCard from "./global/DashboardCard"

export default {
  computed: {
    ...mapGetters({
      stats: 'stats/stats',
    }),
  },
  components: {
    DashboardCard,
  }
}
</script>
