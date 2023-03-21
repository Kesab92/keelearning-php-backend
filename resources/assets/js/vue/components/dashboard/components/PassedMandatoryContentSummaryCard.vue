<template>
  <DashboardCard class="text-xs-center">
    <div v-if="stats.userMandatoryStats.userCount">
      <span class="display-1 font-weight-medium">
        {{ stats.userMandatoryStats.passedUserCount | numberFormat }}
      </span>
      <span class="grey--text">
        von {{ stats.userMandatoryStats.userCount | numberFormat }} Usern
      </span>
      <div class="grey--text mb-3">
        haben alle ihre Pflichtinhalte absolviert
      </div>
      <v-layout row wrap align-center justify-center>
        <v-flex shrink>
          <div style="max-width:40px;">
            <DoughnutChart
              id="doughnut-passedMandatoryContentSummaryCard"
              :labels="chartData.labels"
              :data="chartData.data"
              :chart-colors="chartData.colors"
            />
          </div>
        </v-flex>
        <v-flex shrink class="ml-1">
          {{ finishedUsersPercentage }}%
        </v-flex>
      </v-layout>
    </div>
    <div
      v-else
      class="grey--text text-xs-center">
      Noch keine Daten vorhanden.
    </div>
  </DashboardCard>
</template>

<script>
import colors from "vuetify/es5/util/colors"
import {mapGetters} from 'vuex'
import DashboardCard from "./global/DashboardCard"
import DoughnutChart from "../../partials/global/chartjs/DoughnutChart"

export default {
  computed: {
    ...mapGetters({
      meta: 'stats/meta',
      allStats: 'stats/stats',
    }),
    chartData() {
      return {
        colors: [colors.cyan.lighten1, colors.grey.lighten3],
        data: [this.finishedUsersPercentage, 100 - this.finishedUsersPercentage],
        labels: ['Absolviert', 'Offen'],
      }
    },
    finishedUsersPercentage() {
      if (!this.stats.userMandatoryStats.userCount) {
        return 100
      }
      return Math.round(this.stats.userMandatoryStats.passedUserCount / this.stats.userMandatoryStats.userCount * 100)
    },
    stats() {
      return this.allStats.dashboard.mandatorycontent
    },
  },
  components: {
    DashboardCard,
    DoughnutChart,
  }
}
</script>
