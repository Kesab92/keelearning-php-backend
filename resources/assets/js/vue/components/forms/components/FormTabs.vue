<template>
  <v-tabs
    class="c-moduleTabs"
    color="#cecece"
  >
    <v-tab
      :to="{
        name: 'forms.index'
      }"
      ripple
      exact
    >
      Formulare
    </v-tab>

    <v-tab
      :to="{
        name: 'forms.categories'
      }"
      ripple
      exact
    >
      Kategorien
    </v-tab>
  </v-tabs>
</template>
