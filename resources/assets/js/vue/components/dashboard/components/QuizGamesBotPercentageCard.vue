<template>
  <DashboardCard>
    <v-layout row wrap align-center justify-center class="mb-3">
      <v-flex shrink>
        <div style="max-width:50px;">
          <DoughnutChart
            v-if="chartData"
            id="doughnut-quizGameStatsCard"
            :labels="labels"
            :data="chartData"
            :chart-colors="chartColors" />
          <div
            v-else
            class="display-1 font-weight-medium">
            -
          </div>
        </div>
      </v-flex>
    </v-layout>
    <v-layout row justify-space-between>
      <v-flex grow class="grey--text">
        <v-layout class="s-percentageType">
          <v-flex>
            <div class="s-dot cyan lighten-1"></div>
            User vs User
          </v-flex>
          <v-flex shrink>
            👫
          </v-flex>
        </v-layout>
      </v-flex>
      <v-flex shrink>
        <template v-if="gamePercentageHuman !== null">
          {{ gamePercentageHuman }}%
        </template>
        <template v-else>
          -
        </template>
      </v-flex>
    </v-layout>
    <v-layout row justify-space-between>
      <v-flex grow class="grey--text">
        <v-layout class="s-percentageType">
          <v-flex>
            <div class="s-dot yellow accent-3"></div>
            User vs Bot
          </v-flex>
          <v-flex shrink>
            🤖
          </v-flex>
        </v-layout>
      </v-flex>
      <v-flex shrink>
        <template v-if="gamePercentageBot !== null">
          {{ gamePercentageBot }}%
        </template>
        <template v-else>
          -
        </template>
      </v-flex>
    </v-layout>
  </DashboardCard>
</template>

<script>
import colors from 'vuetify/es5/util/colors'
import {mapGetters} from 'vuex'

import DashboardCard from './global/DashboardCard'
import DoughnutChart from '../../partials/global/chartjs/DoughnutChart'

export default {
  data() {
    return {
      labels: ['User vs User', 'User vs Bot'],
      chartColors: [colors.cyan.lighten1, colors.yellow.accent4]
    }
  },
  computed: {
    ...mapGetters({
      allStats: 'stats/stats',
    }),
    chartData() {
      if (!this.stats.gameCountHumans && !this.stats.gameCountBots) {
        return null
      }
      return [
        this.stats.gameCountHumans,
        this.stats.gameCountBots,
      ]
    },
    gamePercentageBot() {
      if (!this.stats.gameCountHumans && !this.stats.gameCountBots) {
        return null
      }
      return Math.round(this.stats.gameCountBots / (this.stats.gameCountBots + this.stats.gameCountHumans) * 100)
    },
    gamePercentageHuman() {
      if (!this.stats.gameCountHumans && !this.stats.gameCountBots) {
        return null
      }
      return Math.round(this.stats.gameCountHumans / (this.stats.gameCountBots + this.stats.gameCountHumans) * 100)
    },
    stats() {
      return this.allStats.dashboard.quizgames
    },
  },
  components: {
    DashboardCard,
    DoughnutChart,
  }
}
</script>

<style lang="scss" scoped>
#app {
  .s-dot{
    width: 4px;
    height: 4px;
    margin-right: 4px;
    display: inline-block;
    vertical-align: middle;
    border-radius: 50%;
  }

  .s-percentageType {
    max-width: 120px;
  }
}

</style>
