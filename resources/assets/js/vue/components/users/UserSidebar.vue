<template>
  <details-sidebar
    :root-url="{
      name: 'users.index',
    }"
    :drawer-open="typeof $route.params.userId !== 'undefined'"
    data-action="users/loadUser"
    :data-getter="(params) => $store.getters['users/user'](params.userId)"
    :data-params="{userId: $route.params.userId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: user, refresh }">
      <router-view
        :user="user"
        @refresh="refresh"
        @dataUpdated="$emit('dataUpdated')"
      />
    </template>
    <template v-slot:headerTitle="{ data: user }">
      <v-layout row>
        <v-flex
          shrink
          class="mr-3">
          <v-avatar
            :size="60"
            tile>
            <img :src="user.avatar" >
          </v-avatar>
        </v-flex>
        <v-flex>
          {{ user.username }}
          <div
            v-if="user.role"
            class="deep-orange--text mr-2"
            style="font-size:14px;">
            {{ user.role.name }}
            <span
              v-if="user.tagRights.length"
              class="mr-2 grey--text lighten-2">
            <v-tooltip bottom>
              <span slot="activator">(eingeschränkt)</span>
              Dieser Admin ist durch eine TAG-Beschränkung eingeschränkt
            </v-tooltip>
          </span>
          </div>
          <span
            class="grey--text lighten-2"
            style="font-size: 14px">#{{ user.id }}</span>
        </v-flex>
      </v-layout>
    </template>
    <template v-slot:headerExtension="{ data: user }">
      <div v-if="user.is_tmp">
        Temporärer Account
      </div>
      <div
        class="blue--text"
        v-if="user.is_keeunit">
        <v-icon>support_agent</v-icon>
        <span style="vertical-align: text-bottom;">keeunit</span>
      </div>
      Registriert am {{ user.created_at | date }}
      <div
        v-if="user.deleted_at"
        class="mt-2">
        <router-link
          :to="{
            name: 'users.edit.management',
            params: {
              userId: user.id,
            },
          }"
          class="red white--text pa-2">
          Gelöscht am {{ user.deleted_at | date }}
        </router-link>
      </div>
    </template>
  </details-sidebar>
</template>

<script>
import {mapGetters} from "vuex"

export default {
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
      myRights: 'app/myRights',
      showPersonalData: 'app/showPersonalData',
    }),
  },
  methods: {
    getLinks(user) {
      const links = [
        {
          label: 'Allgemein',
          to: {
            name: 'users.edit.general',
            params: {
              userId: user.id,
            },
          },
        },
      ]
      if(user.is_admin) {
        links.push({
          label: 'Berechtigungen',
          to: {
            name: 'users.edit.permissions',
            params: {
              userId: user.id,
            },
          },
        })
      }
      if(this.myRights['users-edit']) {
        links.push({
          label: 'Verwaltung',
          to: {
            name: 'users.edit.management',
            params: {
              userId: user.id,
            },
          },
        })
      }
      links.push({
        label: 'Benachrichtigungen',
        to: {
          name: 'users.edit.notifications',
          params: {
            userId: user.id,
          },
        },
      })
      if(this.myRights['users-edit'] && !user.deleted_at) {
        links.push({
          label: 'Nachricht senden',
          to: {
            name: 'users.send.message',
            params: {
              userId: user.id,
            },
          },
        })
      }
      if (this.showPersonalData('users')) {
        links.push({
          label: 'Qualifikations-Historie',
          to: {
            name: 'users.show.qualification-history',
            params: {
              userId: user.id,
            },
          },
        })
      }
      return links
    }
  }
}
</script>
