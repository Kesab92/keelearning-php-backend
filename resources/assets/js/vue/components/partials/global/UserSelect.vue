<template>
  <v-autocomplete
    :items="users"
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
    @blur="clearResults"
    cache-items
    :search-input.sync="search"
    :loading="isLoading"
    v-model="selectedUsers">
    <template v-slot:item="data">
      <v-list-tile-content v-if="data.item.readonly !== true">
        <v-list-tile-title>{{ data.item.username }}</v-list-tile-title>
        <template v-if="data.item.email !== null">
          <v-list-tile-sub-title class="grey--text">
            {{ data.item.email || '-' }}
          </v-list-tile-sub-title>
        </template>
      </v-list-tile-content>
    </template>
    <template v-slot:selection="data">
      <v-chip
        v-if="data.item.readonly || disabled"
        v-bind="data.attrs"
        :input-value="data.selected"
        disabled
      >
        {{ data.item.username }}
      </v-chip>
      <v-chip
        v-else
        v-bind="data.attrs"
        :input-value="data.selected"
        close
        @input="remove(data.item)"
      >
        {{ data.item.username }}
      </v-chip>
    </template>
    <template slot="no-data">
      <div class="s-noUserFound">
        <template v-if="search">Es wurden keine User zu "{{ search }}" gefunden.</template>
        <template v-else>Suchen Sie nach Benutzernamen<template v-if="canSeeEmails"> / E-Mail</template></template>
      </div>
    </template>
  </v-autocomplete>
</template>

<script>
import {mapGetters} from "vuex"

export default {
  props: {
    value: {
      type: [Array, Number],
      required: true,
    },
    module: {
      type: String,
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
      users: [],
      isLoading: false,
      axiosCancel: null,
      search: null,
    }
  },
  created() {
    this.loadInitialValues()
  },
  methods: {
    clearResults() {
      this.users = []
    },
    loadInitialValues() {
      if(!this.selectedUsers.length) {
        return
      }
      axios.get(`/backend/api/v1/search/users/${this.module}/from-ids?${this.selectedUsers.map(id => `userIds[]=${id}`).join('&')}`).then(response => {
        this.users = response.data.results
      })
    },
    remove (item) {
      const index = this.selectedUsers.indexOf(item.id)
      if (index >= 0) this.selectedUsers.splice(index, 1)
    },
  },
  watch: {
    search (query) {
      if (this.axiosCancel) {
        this.axiosCancel()
      }
      if(!query) {
        return
      }
      this.isLoading = true
      let cancelToken = new axios.CancelToken(c => {
        this.axiosCancel = c
      })
      axios.get(`/backend/api/v1/search/users/${this.module}?q=${encodeURIComponent(query)}`, {
        cancelToken,
      }).then(response => {
        this.users = response.data.results
        this.isLoading = false
      }).catch((e) => {
        if (e instanceof axios.Cancel) {
          return
        }
        this.isLoading = false
        alert('Es ist ein unerwarteter Fehler aufgetreten. Bitte versuchen Sie es später erneut.')
      })
    },
  },
  computed: {
    ...mapGetters({
      showEmails: 'app/showEmails',
    }),
    selectedUsers: {
      get() {
        return this.value
      },
      set(users) {
        this.$emit('input', users)
      },
    },
    placeholderText() {
      if(this.isLoading) {
        return 'Lade...'
      } else {
        return this.placeholder
      }
    },
    canSeeEmails() {
      return this.showEmails(this.module)
    }
  },
}
</script>

<style lang="scss" scoped>
#app .s-noUserFound {
  padding: 16px;
}
</style>
