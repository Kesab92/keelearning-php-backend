<template>
  <div>
    <v-toolbar
      v-if="$slots.default"
      color="elevation-1">
      <template v-slot:default>
        <slot name="default" />
      </template>
    </v-toolbar>
    <div class="s-alerts">
      <slot name="alerts"/>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  #app {
    .s-alerts {
      margin-top: -4px;
    }
  }
</style>
