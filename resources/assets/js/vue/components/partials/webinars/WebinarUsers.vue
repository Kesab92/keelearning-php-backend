<template>
  <div>
    <v-snackbar
      :top="true"
      color="success"
      v-model="snackbars.copied"
    >
      Einladungslink wurde in die Zwischenablage kopiert.
    </v-snackbar>
    <v-snackbar
      :top="true"
      color="success"
      v-model="snackbars.invite"
    >
      Einladungsmail wird in Kürze versendet.
    </v-snackbar>
    <p>
      Zusätzlich zu den Nutzern, die über ihre TAGs Zugriff auf das Webinar haben,
      können hier weitere interne & externe (= ohne Account) Benutzer eingeladen werden.
    </p>
    <v-toolbar
      flat
      color="white">
      <v-toolbar-title>Zusätzliche Teilnehmer</v-toolbar-title>
      <v-spacer />
      <WebinarAddUser @addUser="addUser" />
    </v-toolbar>
    <v-data-table
      :headers="headers"
      :items="users"
      class="elevation-1"
    >
      <template v-slot:no-data>
        <p class="text-xs-center my-2">
          Noch keine zusätzlichen Benutzer hinzugefügt.
        </p>
      </template>
      <template v-slot:items="props">
        <WebinarUserEntry
          v-model="props.item"
          :key="props.item.email"
          @delete="deleteItem"
          @message="showSnackbar"
        />
      </template>
    </v-data-table>
  </div>
</template>

<script>
  import WebinarAddUser from './WebinarAddUser'
  import WebinarUserEntry from './WebinarUserEntry'
  import helpers from "../../../logic/helpers"

  export default {
    props: ['value'],
    data: () => ({
      dialog: false,
      headers: [
        {
          text: 'Name',
          align: 'left',
          sortable: true,
          value: 'name',
        },
        {
          text: 'Email',
          align: 'left',
          sortable: true,
          value: 'email',
        },
        {
          text: 'Rolle',
          align: 'left',
          sortable: true,
          value: 'role',
        },
        {
          text: 'Typ',
          align: 'left',
          sortable: true,
          value: 'external',
        },
        {
          text: '',
          align: 'right',
          sortable: false,
        },
      ],
      snackbars: {
        copied: false,
        invite: false,
      },
    }),
    computed: {
      users: {
        get() {
          return this.value
        },
        set(users) {
          this.$emit('input', users)
        },
      },
    },
    methods: {
      deleteItem (item) {
        const index = this.users.indexOf(item)
        this.$delete(this.users, index)
      },
      addUser (user) {
        if(user.external) {
          if (this.users.filter(u => u.email == user.id).length) {
            return
          }
          this.users.push({
            id: null,
            user_id: null,
            external: true,
            name: this.getNameFromEmail(user.id),
            email: user.id,
            role: 2,
          })
        } else {
          if (this.users.filter(u => u.user_id == user.id).length) {
            return
          }
          this.users.push({
            id: null,
            user_id: user.id,
            external: false,
            name: user.username,
            email: user.email,
            role: 2,
          })
        }
      },
      getNameFromEmail(email) {
        if(!email) {
          return ''
        }
        const parts = email.split('@')
        const username = parts[0]
        let name = ''
        if(username.indexOf('.') === -1) {
          name = helpers.ucfirst(username)
        } else {
          let usernameParts = username.split('.')
          if(usernameParts.length > 2) {
            name = usernameParts.join('.')
          } else {
            name = helpers.ucfirst(usernameParts[0]) + ' ' + helpers.ucfirst(usernameParts[1])
          }
        }
        return name
      },
      showSnackbar(type) {
        this.snackbars[type] = true
      },
    },
    components: {
      WebinarAddUser,
      WebinarUserEntry,
    },
  }
</script>
