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
                <v-list-tile-sub-title v-if="data.item.external">Externer Benutzer</v-list-tile-sub-title>
              </v-list-tile-content>
            </template>
          </v-autocomplete>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" flat @click="dialog = false">Abbrechen</v-btn>
          <v-btn color="blue darken-1" flat @click="save">Benutzer hinzufügen</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
  import { mapGetters } from 'vuex'

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
      search (val) {
        val && val !== this.select && this.querySelections(val)
      },
      dialog (isOpen) {
        if(isOpen) {
          this.$nextTick(() => {
            this.$refs.autocomplete.$el.querySelector('input').focus()
          })
        }
      },
    },
    computed: {
      ...mapGetters({
        showEmails: 'app/showEmails',
        showPersonalData: 'app/showPersonalData',
      }),
      searchLabel() {
        if (!this.showPersonalData('webinars')) {
          // can only add external users
          return 'E-Mail'
        }
        return 'Benutzername / E-Mail'
      },
    },
    methods: {
      querySelections (query) {
        if (!this.showPersonalData('webinars')) {
          if (this.isEmail(query)) {
            this.items = [{
              id: query,
              title: query,
              external: true,
            }]
          }
          return
        }
        this.loading = true
        axios.get(`/backend/api/v1/search/users/webinars?q=${encodeURIComponent(query)}`).then(response => {
          this.items = response.data.results
          // Cache users so we can access them later on
          if(this.items.length) {
            this.items.forEach(item => {
              this.cachedUsers[item.id] = item
            })
          }
          // Add the option to invite an external user
          if(!this.items.length && this.isEmail(query)) {
            this.items.push({
              id: query,
              title: query,
              external: true,
            })
          }
          this.loading = false
        }).catch(error => {
          // this.message = 'Es ist ein unerwarteter Fehler aufgetreten.'
          this.loading = false
        })
      },
      isEmail(email) {
        // This is a super simple test if the given string is an email
        // We want to accept basically anything here, because this check
        // is only for presenting the user with the option to select this
        // as an external user invitation
        const regExp = /\S+@\S+\.\S+/
        return regExp.test(email)
      },
      save() {
        // We can access external users via this.items
        let user = this.items.find((item) => item.id === this.select)
        // We have to access internal users via this.cachedUsers
        if(!user) {
          user = this.cachedUsers[this.select]
        }
        if (!user) {
          return
        }
        this.$emit('addUser', user)
        this.dialog = false
      },
    }
  }
</script>
