<template>
  <v-btn
    color="white"
    depressed
    block
    class="elevation-1 ml-0 py-4 cyan--text text--lighten-1 text-none"
    v-on="$listeners"
    v-bind="$attrs"
  >
    <slot></slot>
  </v-btn>
</template>
