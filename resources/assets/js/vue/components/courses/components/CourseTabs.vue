<template>
  <v-tabs
    class="c-moduleTabs"
    color="#cecece"
  >
    <v-tab
      :to="{
        name: 'courses.index'
      }"
      ripple
      exact
    >
      Kurse
    </v-tab>

    <v-tab
      :to="{
        name: 'courses.categories'
      }"
      ripple
      exact
    >
      Kategorien
    </v-tab>

    <v-tab
      :to="{
        name: 'courses.templates'
      }"
      ripple
      exact
    >
      Vorlagen
    </v-tab>
  </v-tabs>
</template>
