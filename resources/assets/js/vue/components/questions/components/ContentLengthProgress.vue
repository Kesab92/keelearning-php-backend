<template>
  <v-progress-linear
    v-model="percentages"
    :color="color"
    height="3"
  ></v-progress-linear>
</template>

<script>
export default {
  props: {
    length: {
      type: Number,
      required: true,
    },
    maxLength: {
      type: Number,
      default: 300,
    },
  },
  computed: {
    percentages() {
      return this.length / this.maxLength * 100
    },
    color() {
      if(this.percentages === 100) {
        return 'red'
      }
      if(this.percentages > 50) {
        return 'orange'
      }
      return 'primary'
    }
  }
}
</script>
