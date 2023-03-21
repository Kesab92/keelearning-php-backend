<template>
  <p
    class="mb-0 grey--text"
    v-if="!isLoading && userRoles.length === 0">Es wurden noch keine Rolle angelegt.</p>
  <v-autocomplete
    v-else
    :items="userRoles"
    dense
    item-text="name"
    item-value="id"
    :label="label"
    :placeholder="placeholderText"
    outline
    clearable
    :allow-overflow="false"
    :disabled="disabled"
    no-data-text="Keine Rolle gefunden"
    v-model="selectedRoles">
  </v-autocomplete>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  props: {
    value: {
      type: [Array, Number, null],
      default: null,
      required: false,
    },
    label: {
      type: String,
      default: 'Rolle',
      required: false,
    },
    placeholder: {
      type: String,
      default: null,
      required: false,
    },
    disabled: {
      type: Boolean,
      default: false,
      required: false,
    },
    hasTagRights: {
      type: Boolean,
      default: false,
      required: false,
    },
  },
  data() {
    return {
      isLoading: false,
    }
  },
  created() {
    this.isLoading = true
    Promise.all([
      this.$store.dispatch('userRoles/loadUserRoles')
    ]).then(() => {
      this.isLoading = false
    })
  },
  computed: {
    userRoles() {
      let userRoles = JSON.parse(JSON.stringify(this.$store.getters['userRoles/userRoles']))
      if(!userRoles) {
        return []
      }
      if(!this.hasTagRights) {
        return userRoles
      }
      return userRoles.map(userRole => {
        if(userRole.is_main_admin) {
          userRole.disabled = true
          userRole.name = userRole.name + ' (Benutzern mit TAG-Beschränkung kann diese Rolle nicht zugewiesen werden)'
        }
        return userRole
      })
    },
    selectedRoles: {
      get() {
        return this.value
      },
      set(pages) {
        if(typeof pages === "undefined") {
          pages = null
        }
        this.$emit('input', pages)
      },
    },
    placeholderText() {
      if(this.isLoading) {
        return 'Lade...'
      }
      return this.placeholder
    },
  },
}
</script>
