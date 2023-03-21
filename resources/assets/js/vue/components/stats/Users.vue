<template>
  <div>
    <ModuleIntro />
    <UserTabs />

    <v-card class="mt-2 mb-4">
      <v-card-title primary-title>
        <v-layout
          row
          align-center>
          <v-flex
            xs6
            xl4>
            <TagSelect
              v-model="selectedTags"
              class="mr-4"
              :extend-items="getTagItems"
              multiple />
          </v-flex>
          <v-spacer />
          <v-flex
            xs-4
            shrink>
            <v-tooltip
              bottom>
              <v-btn
                to="/stats/users/reports/users/general"
                color="primary"
                slot="activator">
                <v-icon
                  dark
                  left>cloud_download
                </v-icon>
                Details exportieren
              </v-btn>
              <span>Der Export wird mit den aktuellen Einstellungen gefiltert.</span>
            </v-tooltip>
          </v-flex>
        </v-layout>
      </v-card-title>

      <v-data-table
        :headers="headers"
        :items="users"
        :loading="isLoading"
        :pagination.sync="pagination"
        :rows-per-page-items="[50, 100, 200]"
        :total-items="userCount"
        class="elevation-1 users-table"
        item-key="id">
        <tr
          slot="items"
          slot-scope="props">
          <td>
            #{{ props.item.id }}
          </td>
          <td v-if="(typeof props.item.username) !== 'undefined' || (typeof props.item.email) !== 'undefined'">
            {{ props.item.username }}
            <div
              class="grey--text"
              v-if="props.item.email">
              {{ props.item.email }}
            </div>
          </td>
          <td v-if="(typeof props.item.firstname) !== 'undefined'">
            {{ props.item.firstname }}
          </td>
          <td v-if="(typeof props.item.lastname) !== 'undefined'">
            {{ props.item.lastname }}
          </td>
          <td
            v-for="metaKey in Object.keys(metaFields)"
            :key="metaKey">
            {{ props.item.meta[metaKey] }}
          </td>
          <td>
            <v-chip
              :key="`${props.item.id}-${tag.id}`"
              disabled
              small
              v-for="tag in props.item.tags">
              {{ tag.label }}
            </v-chip>
          </td>
          <td>
            <template v-if="props.item.last_online">
              {{ props.item.last_online }}
            </template>
            <span
              v-else
              class="grey--text">
              Nie
            </span>
          </td>
          <td
            v-if="(typeof props.item.games) !== 'undefined'"
            :class="{'grey--text': !props.item.games}">
            {{ props.item.games }}
          </td>
          <td
            v-if="(typeof props.item.human_games) !== 'undefined'"
            :class="{'grey--text': !props.item.human_games}">
            {{ props.item.human_games }}
          </td>
          <td
            v-if="(typeof props.item.human_wins) !== 'undefined'"
            :class="{'grey--text': !props.item.human_wins}">
            {{ parseInt(props.item.human_win_percentage * 100, 10) }}%
          </td>
          <td
            v-if="(typeof props.item.last_game) !== 'undefined'"
            :class="{'grey--text': !props.item.last_game}">
            {{ props.item.last_game | date }}
          </td>
          <td
            v-if="(typeof props.item.learned_questions) !== 'undefined'"
            :class="{'grey--text': !props.item.learned_questions}">
            {{ props.item.learned_questions }}
          </td>
          <td
            v-if="(typeof props.item.passed_tests) !== 'undefined' && rights['tests-stats']"
            :class="{'grey--text': !props.item.passed_tests.length}">
            {{ props.item.passed_tests.length }}
          </td>
          <td
            v-if="(typeof props.item.passed_courses) !== 'undefined' && rights['courses-stats']"
            :class="{'grey--text': !props.item.passed_courses.length}">
            {{ props.item.passed_courses.length }}
          </td>
        </tr>
        <template slot="no-data">
          <v-alert
            :value="true"
            type="info"
            v-show="(!users || users.length === 0) && !isLoading">
            Es wurden keine Benutzer gefunden.
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
    </v-card>
    <ReportSidebar />
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import ModuleIntro from "./UsersModuleIntro"
import TagSelect from "../partials/global/TagSelect"
import UserTabs from "./UserTabs"
import ReportSidebar from "../reports/ReportSidebar"

let axiosCancel = null
const paginationDefaults = {
  page: 1,
  rowsPerPage: 50,
  sortBy: "id",
}

export default {
  data() {
    return {
      userCount: null,
      isLoading: true,
      pagination: {...paginationDefaults},
      headers: [
        {
          text: "ID",
          value: "id",
          width: "90px",
        },
      ],
      metaFields: {},
      users: [],
      selectedTags: [],
    }
  },
  watch: {
    selectedTags() {
      this.loadData()
    },
    pagination: {
      deep: true,
      handler() {
        this.loadData()
      },
    },
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
      rights: 'app/myRights',
    }),
    exportLink() {
      let settings = {}
      if (this.selectedTags.length) {
        settings.tags = this.selectedTags.join(",")
      }

      let query = Object.keys(settings).map(key => {
        if (!settings[key]) {
          return null
        }
        return `${encodeURIComponent(key)}=${encodeURIComponent(settings[key])}`
      }).filter(v => v !== null).join("&")
      return `/stats/users/export?${query}`
    },
    pageSelectOptions() {
      if (!this.userCount || !this.pagination.rowsPerPage) {
        return [1]
      }
      const max = Math.ceil(this.userCount / this.pagination.rowsPerPage)
      const options = []
      for (let i = 1; i <= max; i++) {
        options.push(i)
      }
      return options
    },
  },
  methods: {
    getTagItems(items) {
      return [
        {
          label: "Benutzer ohne TAG",
          id: -1,
        },
      ].concat(items)
    },
    loadData() {
      if (axiosCancel) {
        axiosCancel()
      }
      this.isLoading = true
      let cancelToken = new axios.CancelToken(c => {
        axiosCancel = c
      })
      axios.get("/backend/api/v1/stats/users", {
        cancelToken,
        params: {
          ...this.pagination,
          tags: this.selectedTags,
        },
      }).then(response => {
        if(response instanceof axios.Cancel) {
          return
        }

        this.userCount = response.data.count
        this.users = response.data.users
        this.headers = response.data.headers
        this.metaFields = response.data.metaFields
        this.isLoading = false
      })
    },
  },
  components: {
    ReportSidebar,
    ModuleIntro,
    UserTabs,
    TagSelect,
  },
}
</script>

<style lang="scss">
#app .users-table {
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
