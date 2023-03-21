<template>
  <DashboardCard class="text-xs-center">
    <div class="display-1 font-weight-medium">
      <template v-if="gamesPerPlayerCurrent">
        {{ gamesPerPlayerCurrent | numberFormat(1) }}
      </template>
      <template v-else>
        -
      </template>
    </div>
    <div class="grey--text mb-2">
      Ø Quiz-Battles<br>
      pro aktivem User
    </div>
    <div class="grey--text">
      <span
        v-if="percentageChange !== null"
        :class="{
          'green--text text--accent-3': percentageChange > 0,
          'red--text text--accent-3': percentageChange < 0,
        }">
        {{ percentageChangeSign }} {{ absolutePercentageChange | numberFormat }}%
      </span>
      <span v-else>
        -
      </span>
      ggü Vorwoche
    </div>
  </DashboardCard>
</template>

<script>
import {mapGetters} from 'vuex'
import DashboardCard from "./global/DashboardCard"

export default {
  computed: {
    ...mapGetters({
      allStats: 'stats/stats',
    }),
    gamesPerPlayerCurrent() {
      if (!this.stats.activePlayersCurrent) {
        return 0
      }
      return this.stats.gamesPerWeek[this.stats.gamesPerWeek.length - 1].count / this.stats.activePlayersCurrent
    },
    gamesPerPlayerLastWeek() {
      if (!this.stats.activePlayersLastWeek) {
        return 0
      }
      return this.stats.gamesPerWeek[this.stats.gamesPerWeek.length - 2].count / this.stats.activePlayersLastWeek
    },
    percentageChange() {
      if (!this.gamesPerPlayerLastWeek || !this.gamesPerPlayerCurrent) {
        return null
      }
      return ((this.gamesPerPlayerCurrent - this.gamesPerPlayerLastWeek) / this.gamesPerPlayerLastWeek * 100)
    },
    absolutePercentageChange() {
      return Math.abs(this.percentageChange)
    },
    percentageChangeSign() {
      return this.percentageChange > 0 ? '+' : '-'
    },
    stats() {
      return this.allStats.dashboard.quizgames
    },
  },
  components: {
    DashboardCard,
  }
}
</script>
