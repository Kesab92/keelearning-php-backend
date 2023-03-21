<template>
  <v-layout
    row
    align-center
    class="s-resetPassword">
    <v-flex
      shrink
      class="mr-4">
      <v-btn
        class="mx-0"
        color="primary"
        outline
        @click="resetPassword">
        Jetzt neues Passwort setzen
      </v-btn>
    </v-flex>
    <v-flex grow>
      <v-layout
        v-if="newPassword"
        align-center
        row>
        <v-flex
          class="mr-2"
          shrink>
          <v-icon class="green--text">done</v-icon>
        </v-flex>
        <v-flex shrink>
          Das neue Passwort
        </v-flex>
        <v-flex shrink>
          <v-text-field
            v-model="newPassword"
            :append-icon="showPassword ? 'visibility' : 'visibility_off'"
            :type="showPassword ? 'text' : 'password'"
            class="s-passwordInput mx-2"
            height="30"
            hide-details
            readonly
            @click:append="showPassword = !showPassword"/>
        </v-flex>
        <v-flex shrink>
          wurde an den Nutzer geschickt.
        </v-flex>
      </v-layout>
    </v-flex>
  </v-layout>
</template>

<script>
export default {
  props: ["user"],
  data() {
    return {
      resetLoading: false,
      newPassword: null,
      showPassword: false,
    }
  },
  methods: {
    resetPassword() {
      this.resetLoading = true
      this.newPassword = null
      axios.post("/backend/api/v1/users/" + this.user.id + "/resetPassword").then(response => {
        this.newPassword = response.data.password
        this.$emit('passwordReset')
        this.$store.dispatch('users/loadUser', {
          userId: this.user.id,
        })
      }).catch(e => {
        alert("Das Passwort konnte nicht zurückgesetzt werden. Bitte wenden Sie sich an den Support")
        console.log(e)
      }).finally(() => {
        this.resetLoading = false
      })
    },
  },
}
</script>

<style lang="scss" scoped>
#app {
  .s-resetPassword ::v-deep .s-passwordInput {
    padding-top: 0;
    width: 150px;
  }
}

</style>
