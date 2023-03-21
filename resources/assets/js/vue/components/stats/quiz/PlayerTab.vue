<template>
  <div>
    <ModuleIntro />
    <QuizTabs />
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
            <v-btn
              href="/stats/quiz/reporting#/stats/quiz/reporting">
              <v-icon
                dark
                left>email
              </v-icon>
              E-Mail Reporting
            </v-btn>
            <v-btn
              href="/stats/quiz/csv/players"
              color="primary"
              target="_blank"
              slot="activator">
              <v-icon
                dark
                left>cloud_download
              </v-icon>
              Details exportieren
            </v-btn>
          </v-flex>
        </v-layout>
      </v-card-title>
      <v-data-table
        :headers="headers"
        :items="players"
        :loading="isLoading"
        :pagination.sync="pagination"
        :rows-per-page-items="[50, 100, 200]"
        :total-items="playerCount"
        class="elevation-1 quiz-table"
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
          <td>
            <TagDisplay :tags="props.item.tags" />
          </td>
          <td>
            {{ props.item.stats.gameCount }}
          </td>
          <td>
            {{ props.item.stats.gameWins }}
          </td>
          <td>
            {{ props.item.stats.gameDraws }}
          </td>
          <td>
            {{ props.item.stats.gameLosses }}
          </td>
          <td>
            {{ props.item.stats.gameAborts }}
          </td>
          <td>
            <v-tooltip bottom>
              <template slot="activator">
                    {{ props.item.stats.gameWinPercentage * 100 | decimals }}%
              </template>
              <span>{{ props.item.stats.gameWins }} / {{ props.item.stats.gameLosses  + props.item.stats.gameWins + props.item.stats.gameDraws}}</span>
            </v-tooltip>
          </td>
          <td>
            <v-tooltip bottom>
              <template slot="activator">
                {{ props.item.stats.answersCorrectPercentage * 100 | decimals }}%
              </template>
              <span>{{ props.item.stats.answersCorrect }} / {{ props.item.stats.answersCorrect + props.item.stats.answersWrong}}</span>
            </v-tooltip>
          </td>
          <td
            v-for="(category, id) in props.item.stats.categories"
            :key="`stats-category-${id}`"
           >
            <v-tooltip bottom>
              <template slot="activator">
                {{ category.answersCorrectPercentage * 100 | decimals }}%
              </template>
              <span>{{ category.answersCorrect }} / {{ category.answersCorrect + category.answersWrong}}</span>
            </v-tooltip>
          </td>
        </tr>
        <template slot="no-data">
          <v-alert
            :value="true"
            type="info"
            v-show="(!players || players.length === 0) && !isLoading">
            Es gibt noch keine Benutzer-Statistiken.
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
  </div>
</template>

<script>
import QuizTabs from './QuizTabs'
import TagDisplay from '../../partials/global/TagDisplay'
import TagSelect from "../../partials/global/TagSelect"
import ModuleIntro from "./ModuleIntro"
import {mapGetters} from "vuex";

let axiosCancel = null
const paginationDefaults = {
  page: 1,
  rowsPerPage: 50,
  sortBy: "id",
}

export default {
  data() {
    return{
      isLoading: false,
      pagination: {...paginationDefaults},
      playerCount: null,
      players: [],
      metaFields: {},
      headers: [],
      selectedTags: [],
    }
  },
  watch: {
    pagination: {
      deep: true,
      handler() {
        this.loadData()
      },
    },
    selectedTags() {
      this.loadData()
    },
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
    }),
    pageSelectOptions() {
      if (!this.playerCount || !this.pagination.rowsPerPage) {
        return [1]
      }
      const max = Math.ceil(this.playerCount / this.pagination.rowsPerPage)
      const options = []
      for (let i = 1; i <= max; i++) {
        options.push(i)
      }
      return options
    },
  },
  created() {
    this.loadData()
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
      axios.get("/backend/api/v1/stats/quiz/players", {
        cancelToken,
        params: {
          ...this.pagination,
          tags: this.selectedTags,
        },
      }).then(response => {
        if(response instanceof axios.Cancel) {
          return
        }

        this.headers = response.data.headers
        this.players = response.data.players
        this.playerCount = response.data.count
        this.metaFields = response.data.metaFields
        this.isLoading = false
      })
    }
  },
  components: {
    QuizTabs,
    TagDisplay,
    TagSelect,
    ModuleIntro,
  }
}
</script>

<style lang="scss">
#app .quiz-table {
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
