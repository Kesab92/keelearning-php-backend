<template>
  <Chart
    :chartData="chartData"
    :options="mergedOptions"
    :height = height
    type="bar" />
</template>

<script>
import colors from "vuetify/es5/util/colors"
import { merge } from 'lodash'
import Chart from "./Chart"

export default {
  props: {
    labels: {
      type: Array,
      required: true,
    },
    data: {
      type: Array,
      required: true,
    },
    chartColors: {
      type: Array,
      default: []
    },
    height: {
      type: Number,
      required: false,
      default: 200,
    },
    options: {
      type: Object,
      required: false,
      default: {},
    },
  },
  computed: {
    mergedOptions() {
      const defaultOptions = {
        backgroundColor: colors.cyan.lighten1,
        borderRadius: Number.MAX_VALUE,
        borderSkipped: false,
        barPercentage: 0.4,
        animation: {
          delay: 200,
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            position: 'bottom',
          },
        },
        scales: {
          x: {
            grid: {
              display: false,
              borderWidth:0,
            },
          },
          y: {
            position: 'right',
            grid: {
              drawBorder:0,
            },
          },
        },
      }
      return merge(defaultOptions, this.options)
    },
    chartData() {
      return {
        labels: this.labels,
        datasets: [
          {
            data: this.data,
            backgroundColor: this.chartColors.length ? this.chartColors : undefined,
            minBarLength: 10,
          },
        ]
      }
    }
  },
  components: {
    Chart,
  }
}
</script>
