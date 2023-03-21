<template>
  <div v-if="warnings.length">
    <v-alert
      v-for="warning in warnings"
      :key="warning"
      :value="true"
      color="warning"
      icon="priority_high"
      outline
    >
      {{ warning }}
    </v-alert>
  </div>
</template>

<script>
export default {
  props: {
    warnings: {
      type: Array,
      required: true,
    },
  },
}
</script>
