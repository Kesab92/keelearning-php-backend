<template>
  <DashboardCard
    v-bind="$attrs"
    class="s-weeklyCard">
    <template v-slot:title>
      <div>
        {{ title }} <span class="grey--text">pro Woche</span>
      </div>
    </template>
    <BarChart
      v-if="hasData"
      :chart-colors="chartColors"
      :data="chartEntries"
      :labels="chartLabels"
      :options="options"
    />
    <template v-else>
      <div class="s-empty grey lighten-5 grey--text">
        <p>
          Noch keine Daten vorhanden.
        </p>
        <p>
          <span
            class="s-emptyLink"
            @click.prevent="emptyModalOpen = true">
            Warum werden noch keine Daten angezeigt?
          </span>
        </p>
      </div>
      <v-dialog
        v-model="emptyModalOpen"
        max-width="640px"
        width="80%">
        <v-card>
          <v-toolbar>
            <v-toolbar-title>
              Keine Daten vorhanden
            </v-toolbar-title>
          </v-toolbar>
          <v-card-text>
            <p>
              Dies kann folgende Gründe haben:
            </p>
            <ul>
              <li>Es wurden noch keine Inhalte in dieser Kategorie erstellt</li>
              <li>
                Die erstellten Inhalte wurden noch nicht von den Usern abgerufen.
                Dies kann daran liegen, dass die Inhalte noch nicht sichtbar geschaltet sind.
                Stellen Sie sicher, dass sie die Inhalte im Menüpunkt angeschaltet haben
              </li>
              <li>
                Das gesamte Modul ist in der App evtl noch nicht aktiviert worden
              </li>
            </ul>
          </v-card-text>
          <v-card-actions>
            <v-spacer/>
            <v-btn
              color="primary"
              flat
              @click="emptyModalOpen = false">
              Schließen
            </v-btn>
            <v-spacer/>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </template>
    <slot
      v-for="(slot, name) in $slots"
      :name="name"
      :slot="name" />
  </DashboardCard>
</template>

<script>
import colors from "vuetify/es5/util/colors"
import moment from 'moment'
import DashboardCard from "./global/DashboardCard"
import BarChart from "../../partials/global/chartjs/BarChart"

export default {
  props: {
    title: {
      type: String,
      required: true,
    },
    stats: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      emptyModalOpen: false,
    }
  },
  computed:{
    chartColors() {
      let chartColors = Array(this.chartEntries.length).fill(colors.cyan.lighten1)
      if(chartColors.length) {
        chartColors[chartColors.length - 1] = colors.cyan.lighten4
      }
      return chartColors
    },
    chartEntries() {
      return this.stats.map(entry => entry.count)
    },
    chartLabels() {
      return this.stats.map((entry) => {
        return moment(entry.week).format('MMM')
      })
    },
    chartLabelVisibility() {
      return this.stats.map((entry) => {
        // only show a label on the first week of each month,
        // which we define as the week starting with the first
        // monday in a month
        const entryMonday = moment(entry.week)
        const firstMondayOfMonth = moment(entryMonday)
          .date(1)
          .day(8) // means "next monday"
        // if that is not in the first 7 days of the month,
        // go back one week
        if (firstMondayOfMonth.date() > 7) {
          firstMondayOfMonth.subtract(1, 'week')
        }
        return entryMonday.isSame(firstMondayOfMonth, 'day')
      })
    },
    hasData() {
      return this.chartEntries.some(entry => entry > 0)
    },
    options() {
      let maxValue = Math.max(...this.chartEntries)
      if (maxValue % 2 != 0) {
        maxValue += 1
      }
      maxValue = Math.max(maxValue, 10)
      let that = this
      return {
        scales: {
          x: {
            ticks: {
              callback(val, index) {
                return that.chartLabelVisibility[index] ? that.chartLabels[index] : ''
              },
            },
          },
          y: {
            min: 0,
            max: maxValue,
            ticks: {
              beginAtZero: false,
              stepSize: maxValue / 2,
            },
          },
        },
        maintainAspectRatio: false,
      }
    },
  },
  components: {
    DashboardCard,
    BarChart,
  }
}
</script>

<style lang="scss" scoped>
#app {
  .s-empty {
    background-image: url('/img/empty-state.svg');
    background-position: bottom right;
    background-repeat: no-repeat;
    background-size: 90px auto;
    border-radius: 8px;
    padding: 24px 120px 0 16px;
    height: 100%;
  }

  .s-emptyLink {
    cursor: pointer;
    text-decoration: underline;

    &:hover {
      text-decoration: none;
    }
  }

  .s-weeklyCard {
    height: 300px;
  }
}
</style>
