<template>
  <div>
    <ModuleIntro />
    <QuizTabs />
    <v-card class="mt-2 mb-4">
      <v-card-title primary-title>
        <v-layout
          row
          align-center>
          <v-spacer />
          <v-flex
            xs-4
            shrink>
            <v-btn
              href="/stats/quiz/csv/questions"
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
        :items="questions"
        :loading="isLoading"
        :pagination.sync="pagination"
        :rows-per-page-items="[50, 100, 200]"
        :total-items="questionCount"
        class="elevation-1 quiz-table"
        item-key="id">
        <tr
          slot="items"
          slot-scope="props">
          <td>
            {{ props.item.id }}
          </td>
          <td>
            {{ props.item.title }}
          </td>
          <td>
            {{ props.item.stats.correct }}
          </td>
          <td>
            {{ props.item.stats.wrong }}
          </td>
          <td>
            {{ props.item.difficulty > 0 ? '+':'' }}{{ Math.round(props.item.difficulty * 100) }}%
          </td>
        </tr>
        <template slot="no-data">
          <v-alert
            :value="true"
            type="info"
            v-show="(!questions || questions.length === 0) && !isLoading">
            Es gibt noch keine Fragen-Statistiken.
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
      questionCount: null,
      questions: [],
      headers: [],
    }
  },
  watch: {
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
    }),
    pageSelectOptions() {
      if (!this.questionCount || !this.pagination.rowsPerPage) {
        return [1]
      }
      const max = Math.ceil(this.questionCount / this.pagination.rowsPerPage)
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
    loadData() {
      if (axiosCancel) {
        axiosCancel()
      }
      this.isLoading = true
      let cancelToken = new axios.CancelToken(c => {
        axiosCancel = c
      })
      axios.get("/backend/api/v1/stats/quiz/questions", {
        cancelToken,
        params: {
          ...this.pagination,
        },
      }).then(response => {
        if(response instanceof axios.Cancel) {
          return
        }

        this.headers = response.data.headers
        this.questions = response.data.questions
        this.questionCount = response.data.count
        this.isLoading = false
      })
    }
  },
  components: {
    QuizTabs,
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
