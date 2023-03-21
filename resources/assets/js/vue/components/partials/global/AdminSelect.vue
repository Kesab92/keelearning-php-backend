<template>
  <p
    class="mb-0 grey--text"
    v-if="!isLoading && admins.length === 0">Aktuell gibt es keine Administratoren.</p>
  <v-autocomplete
    v-else
    :items="items"
    deletable-chips
    dense
    item-text="username"
    item-value="id"
    :label="label"
    :multiple="multiple"
    small-chips
    :placeholder="placeholderText"
    :outline="outline"
    :allow-overflow="false"
    :disabled="disabled"
    :persistent-hint="persistentHint"
    :hint="hint"
    v-model="selectedAdmins">
    <template v-slot:item="data">
      <v-list-tile-content>
        <v-list-tile-title>{{ data.item.username }}</v-list-tile-title>
        <v-list-tile-sub-title
          v-if="!data.item.isMaillessAccount && data.item.email"
          class="grey--text">
          {{ data.item.email }}
        </v-list-tile-sub-title>
        <v-list-tile-sub-title
          v-else
          class="grey--text">
          -
        </v-list-tile-sub-title>
      </v-list-tile-content>
    </template>
  </v-autocomplete>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  props: {
    value: {
      type: [Array, Number],
      required: true,
    },
    label: {
      type: String,
      required: true,
    },
    placeholder: {
      type: String,
      default: null,
      required: false,
    },
    multiple: {
      type: Boolean,
      default: false,
      required: false,
    },
    outline: {
      type: Boolean,
      default: false,
      required: false,
    },
    disabled: {
      type: Boolean,
      default: false,
      required: false,
    },
    persistentHint: {
      type: Boolean,
      default: false,
      required: false,
    },
    hint: {
      type: String,
      default: null,
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
    this.$store.dispatch('users/updateAdmins').then(() => {
      this.isLoading = false
    })
  },
  computed: {
    ...mapGetters({
      admins: 'users/admins',
    }),
    selectedAdmins: {
      get() {
        return this.value
      },
      set(admins) {
        this.$emit('input', admins)
      },
    },
    items() {
      if(!this.admins) {
        return []
      }
      let admins = this.admins
      admins = [...admins].sort((a, b) => {
        return a.username.localeCompare(b.username)
      })
      return admins
    },
    placeholderText() {
      if(this.isLoading) {
        return 'Lade...'
      } else {
        return this.placeholder
      }
    },
  },
}
</script>
