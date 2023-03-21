<template>
  <v-app>
    <v-snackbar
      v-model="showSnackbar"
      top
      :color="snackbarColor">
      {{ snackbarMessage }}
    </v-snackbar>
    <slot/>
  </v-app>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  computed: {
    ...mapGetters({
      snackbarColor: 'snackbar/color',
      snackbarMessage: 'snackbar/message',
    }),
    showSnackbar: {
      get() {
        return this.$store.getters['snackbar/visible']
      },
      set(visible) {
        this.$store.dispatch('snackbar/setVisible', visible)
      },
    },
  },
}
</script>

<style>
  #app {
    background-color: #fafafa;
  }
</style>
