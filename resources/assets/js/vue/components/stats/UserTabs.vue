<template>
  <v-tabs
    class="c-moduleTabs"
    color="#cecece"
  >
    <v-tab
      :to="{
        name: 'stats.users'
      }"
      ripple
      exact
    >
      Statistik
    </v-tab>

    <v-tab
      :to="{
        name: 'stats.users.reportings'
      }"
      ripple
      exact
    >
      E-Mail Reporting
    </v-tab>
  </v-tabs>
</template>
