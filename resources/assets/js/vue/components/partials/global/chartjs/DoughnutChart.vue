<template>
  <Chart
    :id="id"
    :chartData="chartData"
    :options="options"
    :height = height
    type="doughnut"
  />
</template>

<script>
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
      required: true,
    },
    id: {
      type: String,
      required: true,
    },
    height: {
      type: Number,
      required: false,
      default: 200,
    },
  },
  computed: {
    options() {
      return {
        aspectRatio: 1,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            enabled: false,
          },
        },
      }
    },
    chartData() {
      return {
        labels: this.labels,
        datasets: [
          {
            data: this.data,
            backgroundColor: this.chartColors,
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
