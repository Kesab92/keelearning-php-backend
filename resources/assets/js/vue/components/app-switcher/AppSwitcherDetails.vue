<template>
  <div>
    <AppSwitcherTabs />
    <v-card class="s-appsCard mt-2">
      <v-card-title>
        <v-layout row>
          <v-flex grow>
            <form @submit.prevent="selectTopApp">
              <v-text-field
                append-icon="search"
                clearable
                placeholder="Name / ID"
                single-line
                autofocus
                :hint="searchHint"
                v-model="search"/>
            </form>
          </v-flex>
        </v-layout>
      </v-card-title>
      <v-data-table
        :headers="headers"
        :items="filteredAppsWithDetails"
        :loading="loading"
        ref="table"
        hide-actions
      >
        <tr
          slot="items"
          slot-scope="props"
          class="clickable"
          @click="selectApp(props.item)">
          <td
            style="width: 50px"
            class="text-xs-left px-0">
            <img
              class="s-appLogo"
              :src="props.item.logo_url" >
          </td>
          <td
            style="width: 50px"
            class="text-xs-left">
            {{ props.item.id }}
          </td>
          <td class="text-xs-left">
            {{ props.item.app_name }}
          </td>
          <td class="text-xs-left">
            {{ props.item.registered_users }}
          </td>
          <td
            class="text-xs-left"
            :class="{
              's-tooManyUsers': hasTooManyUsers(props.item),
          }">
            {{ props.item.user_licences }}
          </td>
          <td>
            {{ props.item.active_users }}
          </td>
          <td>
            {{ props.item.running_games }}
          </td>
          <td>
            {{ props.item.started_game_players }}
          </td>
          <td
            @click.stop=""
            class="text-xs-left unclickable">
            <a
              :href="props.item.app_hosted_at"
              target="_blank">
              {{ props.item.app_hosted_at }}
            </a>
          </td>
        </tr>
        <template v-slot:no-results>
          <v-alert
            :value="true"
            outline
            color="error"
            icon="warning">
            Für die Suche "{{ search }}" gibt es keine Apps.
          </v-alert>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>

<script>
import AppSwitcherTabs from './components/AppSwitcherTabs'
export default {
  props: ['apps'],
  data() {
    return {
      search: null,
      headers: [
        {
          text: '',
          value: 'id',
          sortable: false,
          align: 'left',
        },
        {
          text: 'Id',
          value: 'id',
          sortable: true,
          align: 'left',
        },
        {
          text: 'Name',
          value: 'app_name',
          sortable: true,
          align: 'left',
        },
        {
          text: 'Angemeldet',
          value: 'registered_users',
          sortable: true,
          align: 'left',
        },
        {
          text: 'Lizenzen',
          value: 'user_licences',
          sortable: true,
          align: 'left',
        },
        {
          text: 'Aktive/6 Mo',
          value: 'active_users',
          sortable: true,
          align: 'left',
        },
        {
          text: 'Laufende Duelle',
          value: 'running_games',
          sortable: true,
          align: 'left',
        },
        {
          text: 'Benutzer mit >= 1 Spiel',
          value: 'started_game_players',
          sortable: true,
          align: 'left',
        },
        {
          text: 'App',
          value: 'app_hosted_at',
          sortable: true,
          align: 'left',
        },
      ],
      details: {},
      loading: false,
    }
  },
  created() {
    this.loadDetails()
  },
  computed: {
    filteredApps() {
      if(!this.search) {
        return this.apps
      }
      return this.apps.filter(app => {
        console.log(app.app_name)
        if(!isNaN(this.search)) {
          return app.id === parseInt(this.search, 10)
        }
        if(app.app_name.toLowerCase().indexOf(this.search.toLowerCase()) !== -1) {
          return true
        }
        if(app.app_hosted_at.toLowerCase().indexOf(this.search.toLowerCase()) !== -1) {
          return true
        }
        return false
      })
    },
    topApp() {
      var foo = this.search // We need this, so the computed prop updates when we change the search
      if(!this.$refs.table) {
        return null
      }
      let items = this.$refs.table.filteredItems
      if(!items) {
        return null
      }
      return items[0]
    },
    searchHint() {
      if(!this.search) {
        return ''
      }
      if(!this.topApp) {
        return ''
      }
      return 'Drücke Enter um zur ' + this.topApp.app_name + ' zu wechseln.'
    },
    filteredAppsWithDetails() {
      if(!Object.keys(this.details).length) {
        return []
      }
      return this.filteredApps.map(app => {
        app.active_users = this.details[app.id].active_users
        app.running_games = this.details[app.id].running_games
        app.started_game_players = this.details[app.id].started_game_players

        return app
      })
    }
  },
  methods: {
    selectTopApp() {
      if (!this.topApp) {
        return
      }
      this.selectApp(this.topApp)
    },
    selectApp(app) {
      alert('Die aktive app über das alte Interface zu wechseln wird nicht mehr unterstützt.')
      // window.location.href = '/setapp/' + app.id
    },
    hasTooManyUsers(app) {
      return parseInt(app.registered_users) > parseInt(app.user_licences)
    },
    loadDetails() {
      this.loading = true
      axios.get("/backend/api/v1/apps/details").then(response => {
        this.details = response.data.details
      }).finally(() => {
        this.loading = false
      })
    },
  },
  components: {
    AppSwitcherTabs,
  }
}
</script>

<style lang="scss" scoped>
#app {
  .s-appLogo {
    width: 45px;
    height: 45px;
    margin: 5px;
    display: block;
  }

  .s-tooManyUsers {
    color: red;
    font-weight: bold;
  }

  .s-appsCard {
    max-width: 1400px;
  }
}
</style>
