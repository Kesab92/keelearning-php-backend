<template>
  <DashboardCard class="text-xs-center">
    <template v-if="stats.userMandatoryStats.userCount">
      <v-layout row wrap align-center justify-center>
        <v-flex shrink>
          <div style="max-width:50px;">
            <DoughnutChart
              id="doughnut-mandatoryContentSummaryCard"
              :labels="chartData.labels"
              :data="chartData.data"
              :chart-colors="chartData.colors" />
          </div>
        </v-flex>
        <v-flex shrink class="display-1 font-weight-medium ml-2">
          {{ finishedContentPercentage }}<span class="headline font-weight-medium">%</span>
        </v-flex>
      </v-layout>
      <div class="mt-3">
        der Pflichtinhalte absolviert
      </div>
    </template>
    <div
      v-else
      class="grey--text text-xs-center">
      Noch keine Daten vorhanden.
    </div>
    <template
      v-if="stats.userMandatoryStats.userCount"
      v-slot:help>
      <p>
        Zeigt, wie viel Prozent der jemals sichtbaren Pflichtinhalte absolviert wurden.<br>
        Dabei werden abgelaufene und archivierte Kurse mitgezählt.<br>
        Unsichtbare Kurse werden nicht gezählt.
      </p>
    </template>
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
        data: [this.finishedContentPercentage, 100 - this.finishedContentPercentage],
        labels: ['Absolviert', 'Offen'],
      }
    },
    finishedContentPercentage() {
      return Math.round(this.stats.finishedContentPercentage * 100)
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
