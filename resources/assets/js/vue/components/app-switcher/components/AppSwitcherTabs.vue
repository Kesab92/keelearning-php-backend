<template>
  <v-tabs
    class="c-moduleTabs"
    color="#cecece"
  >
    <v-tab
      :to="{
        name: 'appswitcher'
      }"
      ripple
      exact
    >
      Suche
    </v-tab>

    <v-tab
      :to="{
        name: 'appswitcher.details'
      }"
      ripple
      exact
    >
      Details
    </v-tab>
  </v-tabs>
</template>
