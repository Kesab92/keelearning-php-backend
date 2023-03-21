<template>
  <tr>
    <td>
      <v-edit-dialog
        v-if="user.external"
      >
        <span
          v-if="!user.name"
          class="grey--text text--lighten-1"
        >
          n/a
        </span>
        <template v-else>
          {{ user.name }}
        </template>
        <template v-slot:input>
          <v-text-field
            v-model="user.name"
            label="Name"
            single-line
          />
        </template>
      </v-edit-dialog>
      <template v-else>
        {{ user.name }}
      </template>
    </td>
    <td>
      <v-edit-dialog
        v-if="user.external"
      >
        {{ user.email }}
        <template v-slot:input>
          <v-text-field
            v-model="user.email"
            label="E-Mail"
            single-line
          />
        </template>
      </v-edit-dialog>
      <template v-else>
        {{ user.email }}
      </template>
    </td>
    <td>
      <v-select
        v-model="user.role"
        :items="roles"
        label="Wähle eine Rolle"
        single-line
      />
    </td>
    <td>{{ user.external ? 'Extern' : 'Intern' }}</td>
    <td class="px-1">
      <v-menu
        v-if="user.id && user.role == initialRole"
        offset-y
        :close-on-content-click="false"
        ref="menu"
      >
        <v-btn flat icon slot="activator">
          <v-icon small>
            more_vert
          </v-icon>
        </v-btn>
        <v-list>
          <v-list-tile
            :disabled="inviteLinkLoading"
            @click="getLink"
          >
            <v-list-tile-title>
              Einladungslink kopieren
            </v-list-tile-title>
          </v-list-tile>
          <v-list-tile
            :disabled="sendMailLoading"
            @click="sendMail"
          >
            <v-list-tile-title>Einladungsmail senden</v-list-tile-title>
          </v-list-tile>
        </v-list>
      </v-menu>
      <v-tooltip v-else left>
        <v-btn slot="activator" flat icon disabled>
          <v-icon small>
            more_vert
          </v-icon>
        </v-btn>
        <span>
          Sie müssen das Webinar speichern, bevor Sie Benachrichtigungen versenden können.
        </span>
      </v-tooltip>
      <v-btn @click="deleteItem" flat icon>
        <v-icon small>
          delete
        </v-icon>
      </v-btn>
    </td>
  </tr>
</template>

<script>
import Helpers from '../../../logic/helpers.js'

export default {
  props: ['value'],
  data() {
    return {
      initialRole: null,
      inviteLink: null,
      inviteLinkLoading: false,
      sendMailLoading: false,
    }
  },
  created() {
    this.initialRole = this.user.role
    this.$watch('user.role', () => {
      this.$set(this.user, 'dirty', this.user.role != this.initialRole)
    })
  },
  computed: {
    roles() {
      return [
        {
          text: 'Moderator',
          value: this.$constants.WEBINARS.ROLE_MODERATOR,
        },
        {
          text: 'Teilnehmer',
          value: this.$constants.WEBINARS.ROLE_PARTICIPANT,
        },
      ]
    },
    user: {
      get() {
        return this.value
      },
      set(user) {
        this.$emit('input', user)
      },
    },
  },
  methods: {
    getLink() {
      if (this.inviteLink) {
        this.copyLink()
        return
      }
      if (this.inviteLinkLoading) {
        return
      }
      this.inviteLinkLoading = true
      axios.get(`/backend/api/v1/webinars/get-join-link/${this.user.id}`)
        .then(response => {
          this.inviteLink = response.data.join_link
          this.inviteLinkLoading = false
          this.copyLink()
        }).catch(thrown => {
          alert('Es ist ein unerwarteter Fehler aufgetreten. Bitte laden Sie die Seite neu.')
          this.inviteLinkLoading = false
        })
    },
    copyLink() {
      Helpers.copyToClipboard(this.inviteLink)
      this.$emit('message', 'copied')
      this.$refs.menu.isActive = false
    },
    deleteItem() {
      if(confirm('Möchten Sie diesen Benutzer entfernen?')) {
        this.$emit('delete', this.user)
      }
    },
    sendMail() {
      if (this.sendMailLoading) {
        return
      }
      this.sendMailLoading = true
      axios.post('/backend/api/v1/webinars/send-additional-user-invitation', {
          additional_user_id: this.user.id,
        }).then(response => {
          this.sendMailLoading = false
          this.$emit('message', 'invite')
          this.$refs.menu.isActive = false
        }).catch(thrown => {
          alert('Es ist ein unerwarteter Fehler aufgetreten. Bitte laden Sie die Seite neu.')
          this.sendMailLoading = false
        })
    },
  },
}
</script>
