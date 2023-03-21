<template>
  <v-dialog
    v-if="myRights['users-edit']"
    max-width="2000"
    persistent
    v-model="dialog">
    <v-form
      v-model="isValid"
      class="mx-3"
      lazy-validation
      ref="form"
      @submit.prevent="createUsers">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Benutzer einladen
        </v-card-title>
        <v-card-text>
          <v-data-table
            :headers="headers"
            :items="users"
            :rows-per-page-items="[50]"
            class="elevation-1 s-AddUserTable"
            item-key="id">
            <tr
              slot="items"
              slot-scope="props">
              <td>
                <v-text-field
                  v-model="props.item.username"
                  label="Benutzername"
                  class="mt-4"
                  :rules="usernameRule(props.item)"
                  outline/>
              </td>
              <td>
                <v-text-field
                  v-model="props.item.firstname"
                  label="Vorname (optional)"
                  class="mt-4"
                  outline/>
              </td>
              <td>
                <v-text-field
                  v-model="props.item.lastname"
                  label="Nachname (optional)"
                  class="mt-4"
                  outline/>
              </td>
              <td>
                <v-text-field
                  v-model="props.item.email"
                  label="Email"
                  class="mt-4"
                  :rules="emailRule(props.item)"
                  outline/>
              </td>
              <td v-if="Object.keys(metaFields).length">
                <div class="mt-4">
                  <v-text-field
                    v-for="(meta, metaKey) in metaFields"
                    :key="metaKey"
                    v-model="props.item.meta[metaKey]"
                    browser-autocomplete="chrome-off"
                    :label="meta.label"
                    outline/>
                </div>
              </td>
              <td v-if="languages.length > 1">
                <v-select
                  v-model="props.item.language"
                  color="blue-grey lighten-2"
                  label="Sprache"
                  class="mt-4"
                  :items="languages"
                  outline />
              </td>
              <td>
                <tag-select
                  v-model="props.item.tags"
                  color="blue-grey lighten-2"
                  label="TAGs"
                  class="mt-4"
                  multiple
                  outline
                  placeholder="Keine TAGs"
                  limit-to-tag-rights
                  :required="!isEmptyUser(props.item)"
                />
              </td>
            </tr>
          </v-data-table>
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            @click="closeModal"
            flat>
            Abbrechen
          </v-btn>
          <v-btn
            :loading="isSaving"
            :disabled="isSaving || !isValid"
            color="primary"
            type="submit">
            Benutzer erstellen
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-dialog>
</template>
<script>
import TagSelect from "../partials/global/TagSelect"
import {mapGetters} from "vuex";

export default {
  props: ['value'],
  data() {
    return {
      isSaving: false,
      isValid: false,
      users: [],
    }
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
      metaFields: 'app/metaFields',
      myRights: 'app/myRights',
      allowMaillessSignup: 'app/allowMaillessSignup',
      defaultLanguage: 'languages/defaultLanguage'
    }),
    dialog: {
      set(value) {
        this.$emit('input', value)
      },
      get() {
        return this.value
      }
    },
    headers() {
      let headers = [
        {
          text: "Benutzername",
          value: "username",
          sortable: false,
        },
        {
          text: "Vorname",
          value: "firstname",
          sortable: false,
        },
        {
          text: "Nachname",
          value: "lastname",
          sortable: false,
        },
        {
          text: "Email",
          value: "email",
          sortable: false,
        },
      ]

      if (Object.keys(this.metaFields).length) {
        headers.push({
          text: "Meta Informationen",
          value: "meta",
          sortable: false,
        })
      }

      if (this.languages.length > 1) {
        headers.push({
          text: "Sprache",
          value: "language",
          sortable: false,
        })
      }

      headers.push(
        {
          text: "TAGs",
          value: "tags",
          sortable: false,
        }
      )
      return headers
    },
    languages() {
      const availableLanguages = this.$store.getters['app/languages']
      const languages = []
      Object.keys(availableLanguages).forEach(languageKey => {
        languages.push({
          text: availableLanguages[languageKey],
          value: languageKey,
        })
      })
      return languages
    },
  },
  watch: {
    users: {
      handler() {
        if (this.users.length === 0) {
          this.addUser()
        }
        const lastUser = this.users[this.users.length - 1]
        if (!this.isEmptyUser(lastUser)) {
          this.addUser()
        }
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    async createUsers() {
      await this.$refs.form.validate()
      if (this.isSaving || !this.myRights['users-edit'] || !this.isValid) {
        return
      }

      this.isSaving = true

      let users = JSON.parse(JSON.stringify(this.users)).filter(user => !this.isEmptyUser(user))

      axios.post('/backend/api/v1/users', {
        users: users,
      }).then(() => {
        this.$emit('refresh')
        this.closeModal()
      }).catch(e => {
        console.log(e)
        if(e.response.data) {
          alert(e.response.data.message)
        } else {
          alert('Die Benutzer konnten leider nicht eingeladen werden.')
        }
      }).finally(() => {
        this.isSaving = false
      })
    },
    addUser() {
      this.users.push({
        email: '',
        username: '',
        firstname: '',
        lastname: '',
        language: this.defaultLanguage,
        meta: {},
        tags: [],
      })
    },
    isEmptyUser(user) {
      if(!user.email && !user.username && !user.firstname && !user.lastname &&
        this.isEmptyMeta(user.meta) && user.tags.length === 0) {
        return true
      }
      return false
    },
    isEmptyMeta(meta) {
      if(!Object.keys(meta).length) {
        return true
      }
      for (let property in meta) {
        if(meta[property].length) {
          return false
        }
      }
      return true
    },
    usernameRule(user) {
      let validateStatus = true

      if(user.username.length === 0) {
        validateStatus = 'Benutzername wird benötigt'
      }

      if(this.isEmptyUser(user)) {
        validateStatus = true
      }

      return [validateStatus]
    },
    emailRule(user) {
      let validateStatus = true

      if(user.email.length === 0) {
        validateStatus = 'Email wird benötigt'
      }

      if(this.isEmptyUser(user) || this.allowMaillessSignup) {
        validateStatus = true
      }

      return [validateStatus]
    },
    closeModal() {
      this.users = []
      this.dialog = false
    },
  },
  components: {
    TagSelect,
  },
}
</script>
<style scoped>
#app .s-AddUserTable td {
  padding: 0 16px !important;
}
</style>
