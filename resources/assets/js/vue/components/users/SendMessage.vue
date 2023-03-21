<template>
  <div v-if="user">
    <h4 class="mx-4 mb-0 mt-4">Nachricht senden</h4>
    <div class="px-4 pb-4 pt-0">
      <p>
        Hier können Sie dem Benutzer eine Nachricht senden, die dieser per E-Mail, App Notification und in der App auf der Startseite erhält.
      </p>
      <text-editor
        v-model="body"
        label="Inhalt"
      />
      <v-btn
        class="mt-2 ml-0"
        :loading="isSaving"
        color="primary"
        @click="send"
      >
        Nachricht senden
      </v-btn>
      <div
        class="mt-2"
        v-if="response"
        :class="{
          'error--text': response.type === 'error'
        }">
        {{ response.message }}
      </div>
    </div>
    <h4 class="mx-4 mb-4 mt-4">Gesendete Nachrichten</h4>
    <v-data-table
      :headers="headers"
      :items="messages"
      :pagination.sync="pagination"
      :rows-per-page-items="[20, 40, 60]"
      :total-items="messageCount"
      class="elevation-1 direct-message-table"
    >
      <template v-slot:items="props">
          <td class="text-xs-left text-no-wrap">{{ props.item.updated_at }}</td>
          <td class="text-xs-left"><span v-html="props.item.body"></span></td>
          <td class="text-xs-left">
            <router-link
              v-if="showLink(props.item)"
              target="_blank"
              :to="{ name: 'users.edit.general', params: { userId: props.item.sender_id }}">
              {{ props.item.senderName }}
            </router-link>
            <template v-else-if="props.item.senderIsDummy">
              {{ props.item.senderName }}
            </template>
            <template v-else>
              {{ props.item.senderName }} (keeunit MA)
            </template>
          </td>
      </template>

      <template v-slot:no-data>
          <v-alert :value="true"  type="info">
            Es wurden bisher keine Direktnachrichten an den User versandt.
          </v-alert>
      </template>

      <template slot="actions-prepend">
            <div class="page-select">
              Page:
              <v-select
                :items="pageSelectOptions"
                v-model="pagination.page"
                class="pagination" />
            </div>
      </template>
  </v-data-table>
  </div>
</template>

<script>
import axios from "axios"
import TextEditor from "../partials/global/TextEditor"
import {mapGetters} from "vuex"
const paginationDefaults = {
  page: 1,
  rowsPerPage: 20,
  sortBy: 'updated_at',
}
export default {
  props: ["user"],
  data() {
    return {
      body: null,
      isSaving: false,
      messages: [],
      messageCount: null,
      response: {
        type: null,
      },
      headers: [
          {
            text: 'Gesendet am',
            value: 'updated_at'
          },
          {
            text: 'Nachricht',
            value: 'body'
          },
          {
            text: 'Gesendet von',
            value: 'senderName'
          }
        ],
      pagination: {...paginationDefaults},
    }
  },
  watch: {
    pagination: {
      deep: true,
      handler() {
        this.fetchDirectMessages()
      },
    },
  },
  computed:{
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    pageSelectOptions() {
      if (!this.messageCount || !this.pagination.rowsPerPage) {
        return [1]
      }
      const max = Math.ceil(this.messageCount / this.pagination.rowsPerPage)
      const options = []
      for (let i = 1; i <= max; i++) {
        options.push(i)
      }
      return options
    },
  },
  methods: {
    async send() {
      if (this.isSaving) {
        return
      }
      if (!this.body) {
        this.handleResponse('error', 'Bitte geben Sie zuerst eine Nachricht ein.')
        return
      }
      this.isSaving = true
      try {
        await this.sendMessage()
        this.body = ''
        this.handleResponse('success', 'Die Nachricht wurde versendet.')
      } catch (e) {
        let message = e.response.data.message
        if (!message) {
          message = 'Es ist ein unbekannter Fehler aufgetreten. Bitte versuchen Sie es später erneut.'
        }
        alert(message)
      }
      this.isSaving = false
    },
    sendMessage() {
      return axios.post('/backend/api/v1/users/' + this.user.id + '/send-message', {body: this.body}).then((response) => {
        this.fetchDirectMessages()
        return true
      })
    },
    fetchDirectMessages(){
      return axios.get('backend/api/v1/users/'+ this.user.id +'/direct-messages',{
        params: {...this.pagination}
      }).then((response) => {
        this.messageCount = response.data.messageCount
        this.messages = response.data.messages
      })
    },
    showLink(item) {
      if(item.senderIsSuperAdmin || item.senderIsDummy) {
        return false
      }
      if(!this.myRights['users-edit'] && !this.myRights['users-view']) {
        return false
      }
      return true
    },
    handleResponse(type, message) {
      this.response.type = type
      this.response.message = message
    },
  },
  components: {
    TextEditor,
  },
}
</script>
<style lang="scss">
#app .direct-message-table {
  .v-datatable__actions__select {
    max-width: 180px;
  }

  .page-select {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-right: 14px;
    height: 58px; // IE11 fix
    margin-bottom: -6px;
    color: rgba(0, 0, 0, 0.54);

    // IE11 fixes
    .v-select__slot, .v-select__selections {
      height: 32px;
    }

    .v-select {
      flex: 0 1 0;
      margin: 13px 0 13px 34px;
      font-size: 12px;
    }
  }
}
</style>
