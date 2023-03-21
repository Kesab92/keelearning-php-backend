<template>
  <v-tabs
    class="c-moduleTabs"
    color="#cecece">
    <v-tab
      :to="{
        name: 'users.index'
      }"
      ripple
      exact>
      Benutzer
    </v-tab>

    <v-tab
      v-if="isMainAdmin"
      :to="{
        name: 'user-roles.index'
      }"
      ripple
      exact>
      Admin-Rollen
    </v-tab>
  </v-tabs>
</template>

<script>
import {mapGetters} from 'vuex'

export default {
  computed: {
    ...mapGetters({
      isMainAdmin: 'app/isMainAdmin',
    }),
  },
}
</script>
