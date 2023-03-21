<template>
  <div>
    <ModuleIntro />
    <CourseTabs />
    <AddCourseModal
      v-if="myRights['courses-edit']"
      v-model="addCourseModalOpen"
      create-template />
    <v-btn
      v-if="myRights['courses-edit']"
      color="primary"
      @click="addCourseModalOpen = true">
      <v-icon
        dark
        left>add
      </v-icon>
      Neue Vorlage
    </v-btn>
    <v-card class="mt-2 mb-4">
      <v-card-title primary-title>
        <v-layout row>
          <v-flex xs3>
            <v-select
              :items="filters"
              class="mr-4"
              clearable
              label="Filter"
              v-model="filter"/>
          </v-flex>
          <v-flex xs3>
            <tag-select
              class="mr-4"
              v-model="selectedTags"
              :extend-items="getTagItems"
              limitToTagRights
              multiple />
          </v-flex>
          <v-flex xs3>
            <content-category-select
              class="mr-4"
              v-model="selectedCategories"
              type="courses"
              hideCreation
              multiple />
          </v-flex>
          <v-flex xs3>
            <v-text-field
              append-icon="search"
              clearable
              placeholder="Name / ID"
              single-line
              v-model="search"/>
          </v-flex>
        </v-layout>
      </v-card-title>
      <v-data-table
        :headers="headers"
        :items="templates"
        :loading="isLoading"
        :pagination.sync="pagination"
        :rows-per-page-items="[50, 100, 200]"
        :total-items="templateCount"
        class="elevation-1 courses-table table-layout-fixed"
        item-key="id">
        <tr
          @click="editCourse(props.item.id)"
          class="clickable"
          slot="items"
          slot-scope="props">
          <td class="pa-2 pr-0">
            <img
              v-if="props.item.cover_image_url"
              class="s-course__coverImage"
              :src="props.item.cover_image_url" >
          </td>
          <td>
            {{ props.item.title }}
          </td>
          <td>
            <v-icon
              v-if="props.item.is_mandatory"
              color="success">
              done
            </v-icon>
            <v-icon v-else>
              close
            </v-icon>
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
            <span
              v-if="props.item.is_repeating"
              class="success--text"
            >
              wiederholend
            </span>
            <span v-else>
              nicht wiederholend
            </span>
          </td>
          <td>
            <template v-if="props.item.is_repeating">
              <template v-if="props.item.repetition_interval_type == $constants.COURSES.INTERVAL_TYPES.WEEKLY">
                <template v-if="props.item.repetition_interval == 1">
                  Jede Woche
                </template>
                <template v-else>
                  Alle {{ props.item.repetition_interval }} Wochen
                </template>
              </template>
              <template v-if="props.item.repetition_interval_type == $constants.COURSES.INTERVAL_TYPES.MONTHLY">
                <template v-if="props.item.repetition_interval == 1">
                  Jeden Monat
                </template>
                <template v-else>
                  Alle {{ props.item.repetition_interval }} Monate
                </template>
              </template>
            </template>
            <template v-else>
              -
            </template>
          </td>
          <td>
            <template v-if="props.item.next_repetition_date">
              {{ props.item.next_repetition_date | date }}
            </template>
            <template v-else>
              -
            </template>
          </td>
          <td>
            <content-category-list
              :categories="props.item.categories"
              type="courses" />
          </td>
          <td>
            <v-icon
              v-if="props.item.visible"
              color="success">
              done
            </v-icon>
            <v-icon v-else>
              close
            </v-icon>
          </td>
        </tr>
        <template slot="no-data">
          <v-alert
            :value="true"
            type="info"
            v-show="(!templates || templates.length === 0) && !isLoading">
            Es wurden keine Templates gefunden.
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
    <CourseSidebar
      root-url="courses.templates"
      route-prefix="courses.templates" />
  </div>
</template>

<script>
import {mapGetters} from 'vuex'
import {debounce} from 'lodash'
import helpers from '../../../logic/helpers'
import AddCourseModal from '../components/add-course-modal/AddCourseModal'
import CourseSidebar from '../CourseSidebar'
import CourseTabs from '../components/CourseTabs'
import ModuleIntro from '../components/ModuleIntro'
import TagSelect from '../../partials/global/TagSelect'
import constants from "../../../logic/constants";

export default {
  data() {
    return {
      addCourseModalOpen: false,
      courseModalOpen: false,
      filters: [
        {
          text: "Aktiv",
          value: constants.COURSES.FILTERS.VISIBLE,
        },
        {
          text: "Inaktiv",
          value: constants.COURSES.FILTERS.INVISIBLE,
        },
        {
          text: "Wiederholend",
          value: constants.COURSES.FILTERS.IS_REPEATING,
        },
        {
          text: "Nicht Wiederholend",
          value: constants.COURSES.FILTERS.IS_NOT_REPEATING,
        },
        {
          text: "Archiviert",
          value: constants.COURSES.FILTERS.ARCHIVED,
        },
      ],
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
    }, 1000),
    filter() {
      this.loadData()
    },
    selectedTags() {
      this.loadData()
    },
    selectedCategories() {
      this.loadData()
    },
  },
  computed: {
    ...mapGetters({
      isLoading: 'courses/templateListIsLoading',
      myRights: 'app/myRights',
      templateCount: 'courses/templateCount',
      templates: 'courses/templates',
    }),
    headers() {
      let headers = [
        {
          text: '',
          value: 'image',
          width: '110px',
          sortable: false,
        },
        {
          text: 'Name',
          value: 'title',
          width: '200px',
          sortable: true,
        },
        {
          text: 'Verpflichtend',
          value: 'is_mandatory',
          width: '120px',
          sortable: false,
        },
        {
          text: 'Benutzergruppen',
          value: 'tags',
          sortable: false,
          width: '250px',
        },
        {
          text: 'Typ',
          value: 'is_repeating',
          width: '100px',
          sortable: false,
        },
        {
          text: 'Wiederholung',
          value: 'repeat_interval',
          width: '150px',
          sortable: false,
        },
        {
          text: 'Nächste Wiederholung',
          value: 'next_repetition_date',
          width: '150px',
          sortable: true,
        },
        {
          text: 'Kategorie',
          value: 'categories',
          width: '200px',
          sortable: false,
        },
        {
          text: 'Aktiv',
          value: 'visible',
          width: '100px',
          sortable: false,
        },
      ]
      return headers
    },
    pageSelectOptions() {
      if (!this.templateCount || !this.pagination.rowsPerPage) {
        return [1]
      }
      const max = Math.ceil(this.templateCount / this.pagination.rowsPerPage)
      const options = []
      for (let i = 1; i <= max; i++) {
        options.push(i)
      }
      return options
    },
    pagination: {
      get() {
        return this.$store.state.courses.templates.pagination
      },
      set(data) {
        this.$store.commit('courses/setTemplatePagination', data)
      },
    },
    filter: {
      get() {
        return this.$store.state.courses.templates.filters.filter
      },
      set(value) {
        this.$store.commit('courses/setTemplateFilter', {
            field: 'filter',
            value,
        })
      },
    },
    search: {
      get() {
        return this.$store.state.courses.templates.filters.search
      },
      set(value) {
        this.$store.commit('courses/setTemplateFilter', {
            field: 'search',
            value,
        })
      },
    },
    selectedCategories: {
      get() {
        return this.$store.state.courses.templates.filters.categories
      },
      set(value) {
        this.$store.commit('courses/setTemplateFilter', {
            field: 'categories',
            value,
        })
      },
    },
    selectedTags: {
      get() {
        return this.$store.state.courses.templates.filters.tags
      },
      set(value) {
        this.$store.commit('courses/setTemplateFilter', {
            field: 'tags',
            value,
        })
      },
    },
  },
  methods: {
    editCourse(courseId) {
      this.$router.push({
        name: 'courses.templates.edit.general',
        params: {
          courseId: courseId,
        },
      })
    },
    getNextRepetitionDate(course) {
      return helpers.nextRepetitionCourseDate(course)
    },
    getTagItems(items) {
      return [
        {
          label: 'Templates ohne TAG',
          id: -1,
        },
        ...items,
      ]
    },
    loadData() {
      this.$store.dispatch('courses/loadTemplates')
    },
    openStatistics(courseId) {
      window.location.href = `/course-statistics/${courseId}`
    },
  },
  components: {
    AddCourseModal,
    CourseSidebar,
    CourseTabs,
    ModuleIntro,
    TagSelect,
  }
}
</script>

<style lang="scss">
.ui.segment.menu {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
}

#app .courses-table {
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

#app .s-course__coverImage {
  width: 110px;
  display: block;
  height: 100%;
  min-height: 70px;
  max-height: 150px;
  object-fit: cover;
}
</style>
