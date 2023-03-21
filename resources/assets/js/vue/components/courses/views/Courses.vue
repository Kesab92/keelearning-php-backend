<template>
  <div>
    <ModuleIntro />
    <CourseTabs />
    <AddCourseModal
      v-if="myRights['courses-edit']"
      v-model="addCourseModalOpen" />
    <v-btn
      v-if="myRights['courses-edit']"
      color="primary"
      @click="addCourseModalOpen = true">
      <v-icon
        dark
        left>add
      </v-icon>
      Neuer Kurs
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
        :items="courses"
        :loading="isLoading"
        :pagination.sync="pagination"
        :rows-per-page-items="[50, 100, 200]"
        :total-items="courseCount"
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
              :src="props.item.cover_image_url"/>
            <img
              v-else
              class="s-course__coverImage"
              src="/img/no-connection.svg"
              style="object-fit: contain"/>
          </td>
          <td>
            {{ props.item.title }}
          </td>
          <td>
            <v-icon
              v-if="props.item.is_mandatory"
              color="success"
            >
              done
            </v-icon>
            <v-icon v-else>
              close
            </v-icon>
          </td>
          <td>
            <template v-if="!props.item.has_individual_attendees">
              <v-chip
                :key="`${props.item.id}-${tag.id}`"
                disabled
                small
                v-for="tag in props.item.tags">
                {{ tag.label }}
              </v-chip>
            </template>
            <span v-if="props.item.has_individual_attendees && props.item.individualAttendeesCount > 0" class="grey--text text--darken-1">
              {{ props.item.individualAttendeesCount }} einzelne{{ props.item.individualAttendeesCount === 1 ? 'r' : '' }} User
            </span>
            <div v-if="props.item.has_individual_attendees && props.item.individualAttendeesCount === 0" class="s-noUsersWarning red--text">
              <v-icon color="red">warning</v-icon>
              Keine User zugewiesen
            </div>
          </td>
          <td>
            <v-chip
              disabled
              small
              :text-color="getStatus(props.item).textColor"
              :color="getStatus(props.item).color">
              {{ getStatus(props.item).status }}
            </v-chip>
          </td>
          <td>
            <content-category-list
              :categories="props.item.categories"
              type="courses" />
          </td>
          <td class="body-2 font-weight-medium">
            <ProgressBar :value="props.item.user_finished_percentage"/>
          </td>
          <td class="body-2 font-weight-medium" >{{ props.item.user_count_passed}}/{{ props.item.user_count_total}}</td>
          <td>
            {{ props.item.participations }}
          </td>
          <td class="no-wrap">
            <template v-if="props.item.available_from">
              {{ props.item.available_from | dateTime }}
            </template>
            <div
              v-else
              class="grey--text">
              n/a
            </div>
          </td>
          <td
            v-if="myRights['courses-stats']"
            @click.stop="openStatistics(props.item.id)">
            <v-btn
              :href="`/course-statistics/${props.item.id}`"
              flat>
              <v-icon left>trending_up</v-icon>
              Statistiken
            </v-btn>
          </td>
        </tr>
        <template slot="no-data">
          <v-alert
            :value="true"
            type="info"
            v-show="(!courses || courses.length === 0) && !isLoading">
            Es wurden keine Kurse gefunden.
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
    <CourseSidebar />
  </div>
</template>

<script>
import {mapGetters} from "vuex";
import {debounce} from "lodash"
import AddCourseModal from "../components/add-course-modal/AddCourseModal"
import TagSelect from "../../partials/global/TagSelect"
import ModuleIntro from "../components/ModuleIntro"
import CourseTabs from "../components/CourseTabs"
import CourseSidebar from "../CourseSidebar"
import moment from "moment";
import constants from "../../../logic/constants";
import ProgressBar from "../../dashboard/components/global/ProgressBar.vue";

export default {
  data() {
    return {
      addCourseModalOpen: false,
      createTemplate: false,
      watchSettings: false,
      filters: [
        {
          text: "Sichtbar / in Bearbeitung",
          value: constants.COURSES.FILTERS.ACTIVE,
        },
        {
          text: "Alle Kurse",
          value: constants.COURSES.FILTERS.ALL,
        },
        {
          text: "Abgelaufen",
          value: constants.COURSES.FILTERS.EXPIRED,
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
  created() {
    // If the relaunch requested to open the new course modal, do that and then remove the "new" query parameter from the url
    if(window.location.search.startsWith('?new')) {
      if(this.$route.name === 'courses.index') {
        this.addCourseModalOpen = true
      }
      history.replaceState({}, '', window.location.pathname + window.location.hash)
      window.parent.postMessage({
        type: 'keelearning-iframe-loaded',
        hash: window.location.hash,
        path: window.location.pathname,
        search: '',
        appId: window.VUEX_STATE.appId,
      }, '*')
    }
  },
  computed: {
    ...mapGetters({
      courseCount: 'courses/courseCount',
      courses: 'courses/courses',
      isLoading: 'courses/listIsLoading',
      myRights: 'app/myRights',
    }),
    pagination: {
      get() {
        return this.$store.state.courses.pagination
      },
      set(data) {
        this.$store.commit('courses/setPagination', data)
      },
    },
    filter: {
      get() {
        return this.$store.state.courses.filter
      },
      set(data) {
        this.$store.commit('courses/setFilter', data)
      },
    },
    search: {
      get() {
        return this.$store.state.courses.search
      },
      set(data) {
        this.$store.commit('courses/setSearch', data)
      },
    },
    selectedTags: {
      get() {
        return this.$store.state.courses.tags
      },
      set(data) {
        this.$store.commit('courses/setTags', data)
      },
    },
    selectedCategories: {
      get() {
        return this.$store.state.courses.categories
      },
      set(data) {
        this.$store.commit('courses/setCategories', data)
      },
    },
    headers() {
      let headers = [
        {
          text: "",
          value: "image",
          width: "110px",
          sortable: false,
        },
        {
          text: "Name",
          value: "title",
          width: "200px",
          sortable: true,
        },
        {
          text: "Verpflichtend",
          value: "is_mandatory",
          width: "120px",
        },
        {
          text: "Benutzergruppen",
          value: "tags",
          sortable: false,
          width: "250px",
        },
        {
          text: "Sichtbar",
          value: "visible",
          width: "150px",
        },
        {
          text: "Kategorie",
          value: "categories",
          width: "200px",
          sortable: false,
        },
        {
          text: "Absolviert",
          value: "user_finished_percentage",
          width: "140px",
          sortable: true,
        },
        {
          text: "User",
          value: "user_count",
          width: "100px",
          sortable: false,
        },
        {
          text: "Eingeschrieben",
          value: "participations",
          sortable: false,
          width: "120px",
        },
        {
          text: "Startet am",
          value: "available_from",
          width: "100px",
          sortable: true,
        },
      ]

      if (this.myRights['courses-stats']) {
        headers.push({
          text: "",
          value: "id",
          sortable: false,
          width: "220px",
        })
      }

      return headers
    },
    pageSelectOptions() {
      if (!this.courseCount || !this.pagination.rowsPerPage) {
        return [1]
      }
      const max = Math.ceil(this.courseCount / this.pagination.rowsPerPage)
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
          label: "Kurse ohne TAG",
          id: -1,
        },
      ].concat(items)
    },
    editCourse(courseId) {
      this.$router.push({
        name: 'courses.edit.general',
        params: {
          courseId: courseId,
        },
      })
    },
    openAddCourseModal(asTemplate) {
      this.createTemplate = asTemplate
      this.addCourseModalOpen = true
    },
    openStatistics(courseId) {
      window.location.href = '/course-statistics/' + courseId
    },
    loadData() {
      this.$store.dispatch('courses/loadCourses')
    },
    getStatus(course) {
      if(!course.visible || course.archived_at) {
        return {
          color: 'red',
          textColor: 'white',
          status: 'Unsichtbar',
        }
      }

      const today = moment().startOf('day')
      const hasAvailableFrom = !!course.available_from
      const hasAvailableUntil = course.duration_type === this.$constants.COURSES.DURATION_TYPES.FIXED && !!course.available_until
      const availableFromHasPassed = hasAvailableFrom && moment(course.available_from).isSameOrBefore(today)
      const availableUntilHasPassed = hasAvailableUntil && moment(course.available_until).isSameOrBefore(today)

      if(hasAvailableFrom && !availableFromHasPassed) {
        return {
          color: 'orange',
          textColor: 'white',
          status: 'Noch nicht sichtbar',
        }
      }
      if(hasAvailableUntil && availableUntilHasPassed) {
        return {
          color: 'gray',
          textColor: 'dark',
          status: 'Nicht mehr sichtbar',
        }
      }
      return {
        color: 'green',
        textColor: 'white',
        status: 'Sichtbar',
      }
    },
  },
  components: {
    ProgressBar,
    CourseTabs,
    ModuleIntro,
    TagSelect,
    AddCourseModal,
    CourseSidebar,
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
  margin-bottom: 3px;
}

.s-noUsersWarning {
  display: flex;
  align-items: center;
  gap: 5px;
}
</style>
