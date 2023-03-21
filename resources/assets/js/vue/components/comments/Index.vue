<template>
  <div>
    <ModuleIntro/>
    <v-alert
      :value="cantSeeAnyCommentableContent"
      outlined
      type="warning"
      prominent
      border="left"
      class="mt-4">
      Sie haben keinen Zugriff auf Inhaltstypen mit Kommentaren.
      Bitte kontaktieren Sie Ihren Administrator.
    </v-alert>
    <v-alert
      outlined
      type="warning"
      prominent
      border="left"
      :value="unresolvedCount"
      class="mt-4">
      <div v-if="unresolvedCount == 1">
        Es gibt einen gemeldeten Kommentar, der noch nicht bearbeitet wurde.
      </div>
      <div v-else>
        Es gibt {{ unresolvedCount }} gemeldete Kommentare, die noch nicht bearbeitet wurden.
      </div>
      <v-btn
        class="ml-0"
        small
        @click="loadUnresolved">
        Gemeldete Kommentare anzeigen
      </v-btn>
    </v-alert>
    <v-card
      v-if="!cantSeeAnyCommentableContent"
      class="mt-4 mb-4">
      <v-card-title primary-title>
        <v-layout row>
          <v-flex xs4>
            <v-select
              v-model="selectedFilters"
              :items="filters"
              label="Filter"
              class="mr-4"
              multiple
              clearable
              dense>
              <template v-slot:item="data">
                <template v-if="(typeof data.item) !== 'object'">
                  <v-list-tile-content v-text="data.item"/>
                </template>
                <template v-else>
                  <v-list-tile-action>
                    <v-checkbox
                      v-model="data.tile.props.value" />
                  </v-list-tile-action>
                  <v-list-tile-content>
                    <v-list-tile-title v-html="data.item.text"/>
                  </v-list-tile-content>
                </template>
              </template>
            </v-select>
          </v-flex>
          <v-flex xs4>
            <tag-select
              class="mr-4"
              v-model="selectedTags"
              multiple/>
          </v-flex>
          <v-flex xs4>
            <v-text-field
              v-if="showPersonalData('comments')"
              append-icon="search"
              clearable
              placeholder="Username"
              single-line
              v-model="search"/>
          </v-flex>
        </v-layout>
      </v-card-title>
      <CommentsTable/>
    </v-card>
    <CourseSidebar
      root-url="comments.index"
      route-prefix="comments.courses" />
    <NewsSidebar
      root-url="comments.index"
      route-prefix="comments." />
    <LearningmaterialSidebar
      root-url="comments.index"
      route-prefix="comments." />
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import constants from "../../logic/constants"
import CommentsTable from "./CommentsTable"
import CourseSidebar from "../courses/CourseSidebar"
import ModuleIntro from "./ModuleIntro"
import LearningmaterialSidebar from "../learningmaterials/LearningmaterialSidebar"
import NewsSidebar from "../news/NewsSidebar"
import tableConfig from "../../mixins/tableConfig"
import TagSelect from "../partials/global/TagSelect"
import {debounce} from "lodash"

export default {
  mixins: [
    tableConfig,
  ],
  data() {
    return {
      unresolvedCount: 0,
    }
  },
  watch: {
    $route() {
      if(this.$route.name === 'comments.index') {
        this.restoreConfig()
        this.loadData()
      }
    },
    search: debounce(function () {
      this.storeConfig()
    }, 1000),
    selectedFilters() {
      this.storeConfig()
    },
    selectedTags() {
      this.storeConfig()
    },
  },
  created() {
    this.checkWhetherReportedExist()
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
      showPersonalData: 'app/showPersonalData',
    }),
    cantSeeAnyCommentableContent() {
      return this.myRights['comments-personaldata'] && !(
        this.myRights['news-edit']
        || this.myRights['courses-view']
        || this.myRights['courses-edit']
        || this.myRights['learningmaterials-edit']
      )
    },
    filters() {
      let filters = [
        {
          header: 'Typ'
        }
      ]
      if (this.myRights['news-edit']) {
        filters.push({
          text: 'News',
          value: `type_${constants.COMMENTS.TYPE_NEWS}`,
        })
      }
      if (this.myRights['courses-view'] || this.myRights['courses-edit']) {
        filters.push({
          text: 'Kurs',
          value: `type_${constants.COMMENTS.TYPE_COURSES}`,
        })
        filters.push({
          text: 'Kursinhalte',
          value: `type_${constants.COMMENTS.TYPE_COURSE_CONTENT_ATTEMPT}`,
        })
      }
      if (this.myRights['learningmaterials-edit']) {
        filters.push({
          text: 'Mediathek',
          value: `type_${constants.COMMENTS.TYPE_LEARNING_MATERIALS}`,
        })
      }
      return filters.concat([
        {
          header: 'Status'
        },
        {
          text: 'Normal',
          value: `status_${constants.COMMENTS.STATUS_NORMAL}`,
        },
        {
          text: 'Gemeldet',
          value: `status_${constants.COMMENTS.STATUS_UNRESOLVED}`,
        },
        {
          text: 'Gelöscht',
          value: `status_${constants.COMMENTS.STATUS_DELETED}`,
        },
      ])
    },
    selectedFilters: {
      get() {
        return this.$store.state.comments.filters
      },
      set(data) {
        this.$store.commit('comments/setFilters', data)
      },
    },
    search: {
      get() {
        return this.$store.state.comments.search
      },
      set(data) {
        this.$store.commit('comments/setSearch', data)
      },
    },
    selectedTags: {
      get() {
        return this.$store.state.comments.tags
      },
      set(data) {
        this.$store.commit('comments/setTags', data)
      },
    },
  },
  methods: {
    getBaseRoute() {
      return {
        name: 'comments.index',
      }
    },
    getCurrentTableConfig() {
      const config = {}
      if (this.selectedFilters.length) {
        config.selectedFilters = this.selectedFilters
      }
      if (this.selectedTags.length) {
        config.selectedTags = this.selectedTags
      }
      if (this.search) {
        config.search = this.search
      }
      return config
    },
    loadData() {
      this.$store.dispatch('comments/loadComments')
    },
    loadUnresolved() {
      const statusName = `status_${this.$constants.COMMENTS.STATUS_UNRESOLVED}`
      if (!this.selectedFilters.includes(statusName)) {
        this.selectedFilters.push(statusName)
      }
    },
    checkWhetherReportedExist() {
      axios.get('/backend/api/v1/comments/unresolved').then(response => {
        this.unresolvedCount = response.data.unresolvedCount
      })
    },
  },
  components: {
    CommentsTable,
    CourseSidebar,
    LearningmaterialSidebar,
    ModuleIntro,
    NewsSidebar,
    TagSelect,
  }
}
</script>
