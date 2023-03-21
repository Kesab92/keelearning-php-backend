<template>
  <v-tabs
    class="c-moduleTabs"
    color="#cecece"
  >
    <v-tab
      :to="{
        name: 'tags.index'
      }"
      ripple
      exact
    >
      TAGs
    </v-tab>

    <v-tab
      :to="{
        name: 'tags.categories'
      }"
      ripple
      exact
    >
      Kategorien
    </v-tab>
  </v-tabs>
</template>
