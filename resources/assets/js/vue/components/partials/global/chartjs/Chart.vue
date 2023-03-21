<template>
  <canvas
    ref="canvas"
    :height="height"
    width="400" />
</template>

<script>
import Chart from 'chart.js/auto'
import { cloneDeep } from 'lodash'

export default {
  props: {
    chartData: {
      type: Object,
      default: null
    },
    height: {
      type: Number,
      required: false,
      default: 200,
    },
    options: {
      type: Object,
      default: null
    },
    type: {
      type: String,
      required: true,
    },
  },
  data () {
    return {
      chart: null,
    }
  },
  mounted() {
    this.generateChart()
  },
  watch: {
    chartData: {
      handler: function () {
        this.updateChart()
      },
      deep: true,
    },
    options: {
      handler: function () {
        this.updateChart()
      },
      deep: true,
    },
  },
  methods: {
    setCustomTooltipPosition() {
      const tooltipPlugin = Chart.registry.getPlugin('tooltip');
      tooltipPlugin.positioners.bottom = function(elements, eventPosition) {
        const chart = this._chart;

        return {
          x: eventPosition.x,
          y: chart.chartArea.bottom,
        };
      };
    },
    generateChart(animate = true) {
      this.setCustomTooltipPosition()
      // We have to deep clone the object, because chartjs apparently changes the data internally which
      // would lead to an infinite update loop in the updateChart method
      const options = cloneDeep(this.options)
      if(!animate) {
        options.animation = false
      }
      this.chart = new Chart(this.$refs.canvas.getContext('2d'), {
          type: this.type,
          data: cloneDeep(this.chartData),
          options,
        }
      )
    },
    updateChart() {
      // We have to recreate the chart instead of updating it, because the data array doesn't change
      // when it get's updated, but is replaced completely, which throws chartjs off balance.
      this.chart.destroy()
      this.generateChart(false)
    },
  },
}
</script>
