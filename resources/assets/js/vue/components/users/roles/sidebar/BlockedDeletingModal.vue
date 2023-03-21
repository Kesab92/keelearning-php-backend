<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Löschen der Rolle nicht möglich
        </v-card-title>
        <v-card-text>
          <p class="orange--text">
            Die Rolle '{{ userRole.name }}' kann nicht gelöscht werden, solange diese Benutzern zugewiesen ist.
          </p>
          <p>Benutzer: <span class="blue--text">{{ userRole.users.length }}</span></p>
          <v-divider class="my-3" />
          <p>Tipp:</p>
          <v-alert
            type="info"
            :value="true"
          >
            Alle Benutzer mit der Rolle 'Redakteur' können
            Sie über den Filter 'Admins' der Benutzerverwaltung ausfindig machen.
          </v-alert>
          <v-divider class="mt-3" />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            @click="dialog = false"
            color="primary"
            flat>
            Abbrechen
          </v-btn>
        </v-card-actions>
      </v-card>
  </v-dialog>
</template>
<script>

export default {
  props: ['value', 'userRole'],
  computed: {
    dialog: {
      set(value) {
        this.$emit('input', value)
      },
      get() {
        return this.value
      }
    }
  },
}
</script>
