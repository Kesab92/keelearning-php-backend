<template>
  <div>
    <v-btn color="primary" dark class="mb-2" @click="dialog = true">Benutzer hinzufügen</v-btn>
    <v-dialog v-model="dialog" max-width="500px">
      <v-card>
        <v-card-title>
          <span class="headline">Benutzer hinzufügen</span>
        </v-card-title>

        <v-card-text>
          <v-autocomplete
            v-model="select"
            :loading="loading"
            :items="items"
            :search-input.sync="search"
            cache-items
            class="mx-3"
            hide-no-data
            item-text="title"
            item-value="id"
            :label="searchLabel"
            autofocus
            ref="autocomplete"
          >
            <template v-slot:item="data">
              <v-list-tile-avatar v-if="data.item.image">
                <img :src="data.item.image">
              </v-list-tile-avatar>
              <v-list-tile-content>
                <v-list-tile-title>{{ data.item.title }}</v-list-tile-title>
              </v-list-tile-content>
            </template>
          </v-autocomplete>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="blue darken-1"
            flat
            @click="closeModal">
            Abbrechen
          </v-btn>
          <v-btn
            color="blue darken-1"
            flat
            :disabled="!this.select"
            @click="save">
            Benutzer hinzufügen
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import {mapGetters} from "vuex"

export default {
  data() {
    return {
      dialog: false,
      loading: false,
      items: [],
      search: null,
      select: null,
      cachedUsers: {},
    }
  },
  watch: {
    search(val) {
      if( val && val !== this.select) {
        this.querySelections(val)
      }
    },
    dialog(isOpen) {
      if (isOpen) {
        this.$nextTick(() => {
          this.$refs.autocomplete.$el.querySelector('input').focus()
        })
      }
    },
  },
  computed: {
    ...mapGetters({
      showEmails: 'app/showEmails',
    }),
    searchLabel() {
      if (!this.showEmails('quizteams')) {
        return 'Benutzername'
      }
      return 'Benutzername / E-Mail'
    },
  },
  methods: {
    querySelections(query) {
      this.loading = true
      axios.get(`/backend/api/v1/search/users/quizteams?q=${encodeURIComponent(query)}`).then(response => {
        this.items = response.data.results
        // Cache users so we can access them later on
        if (this.items.length) {
          this.items.forEach(item => {
            this.cachedUsers[item.id] = item
          })
        }
      }).finally(() => {
        this.loading = false
      })
    },
    save() {
      const member = this.cachedUsers[this.select]
      if (!member) {
        return
      }
      this.$emit('addMember', member)
      this.closeModal()
    },
    closeModal() {
      this.select = null
      this.dialog = false
    }
  }
}
</script>
