<template>
  <div>
    <v-layout
      row
      class="px-4 pt-4">
      <v-flex
        v-if="showPersonalData('courses')"
        xs3>
        <v-select
          :items="filters"
          class="mr-4"
          clearable
          label="Filter"
          placeholder="Alle"
          v-model="filter"/>
      </v-flex>
      <v-flex xs2>
        <tag-select
          v-model="selectedTags"
          multiple
          class="mr-4"
          :extend-items="getFilterTags" />
      </v-flex>
      <v-flex
        v-if="showPersonalData('courses')"
        xs4>
        <v-text-field
          append-icon="search"
          clearable
          placeholder="Name / Mail / ID"
          single-line
          v-model="search"/>
      </v-flex>
      <v-spacer />
      <v-flex shrink>
        <v-btn
          :href="exportLink"
          target="_blank"
          color="primary"
          slot="activator">
          <v-icon
            dark
            left>cloud_download
          </v-icon>
          Ergebnisse exportieren
        </v-btn>
      </v-flex>
    </v-layout>
    <v-data-table
      :headers="headers"
      :items="users"
      :loading="isLoading"
      :pagination.sync="pagination"
      :rows-per-page-items="[50, 100, 200]"
      :total-items="userCount"
      class="elevation-1 course-users-progress-table"
      item-key="id">
      <tr
        slot="items"
        slot-scope="props">
        <td v-if="showPersonalData('courses')">
          {{ props.item.username }}
          <div
            class="grey--text"
            v-if="props.item.email">
            {{ props.item.email }}
          </div>
        </td>
        <td class="no-wrap">
          <template v-if="hasNotFinished(props.item)">
            Nicht abgelegt
          </template>
          <v-layout
            row
            align-center
            v-else-if="hasPassed(props.item)">
            <v-icon
              class="mr-2"
              color="green">check</v-icon>
            <div>
              Bestanden
              <div
                class="grey--text"
                v-if="props.item.attempts[testId].finished_at">
                {{ props.item.attempts[testId].finished_at | dateTime }}
              </div>
            </div>
          </v-layout>
          <v-layout
            row
            align-center
            v-else-if="hasFailed(props.item)">
            <v-icon
              class="mr-2"
              color="red">clear</v-icon>
            <div>
              Nicht Bestanden
              <div
                class="grey--text"
                v-if="props.item.attempts[testId].finished_at">
                {{ props.item.attempts[testId].finished_at | dateTime }}
              </div>
            </div>
          </v-layout>
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
  </div>
</template>

<script>
import {debounce} from "lodash"
import {mapGetters} from "vuex"
import UserCourseProgress from "./UserCourseProgress.vue"
import TagSelect from "../partials/global/TagSelect"

let axiosCancel = null

export default {
  props: {
    course: {
      type: Object,
      required: true,
    },
    tags: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      userCount: null,
      isLoading: true,
      pagination: {
        page: 1,
        rowsPerPage: 50,
        sortBy: "id",
      },
      filter: 'participating',
      search: null,
      selectedTags: [],
      users: [],
    }
  },
  watch: {
    pagination: {
      handler() {
        this.loadData()
      },
      deep: true,
    },
    search: debounce(function () {
      this.loadData()
    }, 500),
    selectedTags() {
      this.loadData()
    },
    filter() {
      this.loadData()
    },
  },
  created() {
    this.loadData()
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
      showPersonalData: 'app/showPersonalData',
    }),
    filters() {
      const filters = [
        {
          text: 'Eingeschrieben',
          value: 'participating',
        },
        {
          text: 'Abgeschlossen',
          value: 'completed',
        }
      ]
      if (this.showPersonalData('courses')) {
        filters.push({
          text: 'Alle',
          value: '',
        })
      }
      return filters
    },
    headers() {
      const headers = []
      if (this.showPersonalData('courses')) {
        headers.push({
          text: 'Name',
          value: 'username',
          width: '300px',
        })
      }
      headers.push({
        text: 'Fortschritt',
        value: `progress_${this.$route.params.testId}`,
        sortable: true,
      })
      return headers
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
    testId() {
      return parseInt(this.$route.params.testId, 10)
    },
    exportLink() {
      return '/course-statistics/' + this.course.id + '/export/test/' + this.testId
    },
  },
  methods: {
    getFilterTags(tags) {
      if(this.course.tags.length > 0) {
        return tags.filter(tag => this.course.tags.includes(tag.id))
      }
      return tags
    },
    loadData() {
      if (axiosCancel) {
        axiosCancel()
      }
      this.isLoading = true
      let cancelToken = new axios.CancelToken(c => {
        axiosCancel = c
      })
      axios.get("/backend/api/v1/course-statistics/" + this.course.id + "/users", {
        cancelToken,
        params: {
          ...this.pagination,
          filter: this.filter,
          search: this.search,
          tags: this.selectedTags,
        },
      }).then(response => {
        if(response instanceof axios.Cancel) {
          return
        }

        this.selected = []
        this.userCount = response.data.count
        this.users = response.data.users
        this.isLoading = false
      }).catch(e => {
        console.log(e)
      })
    },
    hasNotFinished(user) {
      if(typeof user.attempts[this.testId] === 'undefined') {
        return true
      }
      return user.attempts[this.testId].passed === null
    },
    hasPassed(user) {
      if(typeof user.attempts[this.testId] === 'undefined') {
        return false
      }
      return user.attempts[this.testId].passed === 1
    },
    hasFailed(user) {
      if(typeof user.attempts[this.testId] === 'undefined') {
        return false
      }
      return user.attempts[this.testId].passed === 0
    }
  },
  components: {
    TagSelect,
    UserCourseProgress,
  },
}
</script>


<style lang="scss">

#app .course-users-progress-table {
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
