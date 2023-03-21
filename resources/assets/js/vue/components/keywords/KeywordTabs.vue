<template>
  <v-tabs
    class="c-moduleTabs"
    color="#cecece"
  >
    <v-tab
      :to="{
        name: 'keywords.index'
      }"
      ripple
      exact
    >
      Schlagwörter
    </v-tab>

    <v-tab
      :to="{
        name: 'keywords.categories'
      }"
      ripple
      exact
    >
      Kategorien
    </v-tab>
  </v-tabs>
</template>
