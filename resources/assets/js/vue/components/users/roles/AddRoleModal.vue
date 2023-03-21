<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
    <v-form
      v-model="isValid"
      @submit.prevent="createRole">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Neue Admin-Rolle
        </v-card-title>
        <v-card-text>
          <v-text-field
            v-model="name"
            autofocus
            box
            class="my-3"
            counter="255"
            label="Bezeichnung"
            required
            :rules="[
              $rules.minChars(3),
              $rules.maxChars(255),
              $rules.noDuplicate(userRoles.map(userRole => userRole.name)),
            ]" />
          <v-alert
            outline
            type="info"
            :value="true">
            Benutzer werden über TAGs einer Benutzergruppe zugewiesen.
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-btn
            @click="closeModal"
            flat>
            Abbrechen
          </v-btn>
          <v-spacer />
          <v-btn
            :loading="isLoading"
            :disabled="isLoading || !isValid"
            color="primary"
            type="submit"
            flat>
            Rolle erstellen
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-dialog>
</template>

<script>
import {mapGetters} from 'vuex'

export default {
  props: ['value'],
  data() {
    return {
      isLoading: false,
      isValid: false,
      name: '',
    }
  },
  methods: {
    createRole() {
      if(this.isLoading) {
        return
      }
      this.isLoading = true
      axios.post('/backend/api/v1/user-roles', {
        name: this.name,
      }).then(response => {
        this.$store.dispatch('userRoles/loadUserRoles')
        this.closeModal()
        this.$router.push({
          name: 'user-roles.edit.general',
          params: {
            userRoleId: response.userRoleId,
          },
        })
      }).catch(e => {
        alert('Die Rolle konnte leider nicht angelegt werden.')
      }).finally(() => {
        this.isLoading = false
      })
    },
    closeModal() {
      this.name = ''
      this.dialog = false
    },
  },
  computed: {
    ...mapGetters({
      userRoles: 'userRoles/userRoles',
    }),
    dialog: {
      set(value) {
        this.$emit('input', value)
      },
      get() {
        return this.value
      }
    },
  },
}
</script>
