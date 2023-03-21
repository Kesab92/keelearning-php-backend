<template>
  <DashboardCard
    :class="{
      'cursor-progress': !stats.dashboard,
    }"
    class="text-xs-center">
    <template v-if="stats.dashboard">
      <div
        :class="{
          'warning--text': licenseUsagePercentage >= 95 && licenseUsagePercentage < 100,
          'error--text': licenseUsagePercentage >= 100,
        }"
        class="display-1 font-weight-medium">
        {{ licenseUsagePercentage }}%
      </div>
      <div class="mb-2">
        der Lizenzen<br>
        ausgeschöpft
      </div>
      <div class="grey--text">
        Max. {{ stats.dashboard.usercounts.licenses | numberFormat }} User
      </div>
    </template>
    <template v-else>
      <div class="c-skeletonLoader -circle mx-auto"/>
      <div class="c-skeletonLoader -line mx-auto mt-2" style="width:60%"/>
      <div class="c-skeletonLoader -line mx-auto mt-2" style="width:50%"/>
    </template>
    <template
      v-slot:help>
      <h2 class="mb-4">Lizenzberechnung</h2>
      <p>Nur aktive User werden in der Berechnung der Lizenzen gezählt.</p>
      <p class="mb-0">Als "aktiv" zählen User:</p>
      <ul>
        <li>mit akzeptierten Nutzungsbedingungen</li>
        <li>die in der Benutzerverwaltung auf "aktiv" geschaltet sind</li>
      </ul>
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
    licenseUsagePercentage() {
      if (!this.stats.dashboard) {
        return null
      }
      return this.stats.dashboard.usercounts.licenses ?
        Math.floor(this.stats.dashboard.usercounts.active / this.stats.dashboard.usercounts.licenses * 100)
        : 100
    },
  },
  components: {
    DashboardCard,
  },
}
</script>
