<template>
  <div v-if="form && myRights['forms-stats']">
      <div class="pa-4">
        <v-alert
          v-if="!availableCourseContents.length && !courseContentIsLoading"
          type="info"
          outline
          :value="true">
          Die Anzeige von User-Antworten ist erst möglich, nachdem das Formular in einen veröffentlichten Kurs
          eingebunden wurde.
        </v-alert>
        <v-autocomplete
          v-if="availableCourseContents.length"
          v-model="courseContentId"
          :items="availableCourseContents"
          label="Kurs Inhalt"
          item-text="fullTitle"
          item-value="id"
          hide-details
          class="mb-4"
        />
        <v-layout
          v-if="availableCourseContents.length"
          row>
          <v-flex xs4>
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
              placeholder="Name / Mail / User-ID"
              single-line
              v-model="search"/>
          </v-flex>

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
        <FormAnswerTable
          v-if="availableCourseContents.length"
          :answers="answers"
          :answer-count="answerCount"
          :fields="fields"
          :is-loading="formIsLoading"
          :pagination.sync="pagination"
          :show-personal-data="showPersonalData('courses')"
        />
      </div>
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import TagSelect from "../../partials/global/TagSelect"
import formStatsMixin from '../../../mixins/formStatsMixin'
import FormAnswerTable from "../../partials/form-stats/FormAnswerTable"

let axiosCancel = null

export default {
  mixins: [formStatsMixin],
  props: {
    form: {
      type: Object,
      required: true,
    }
  },
  data () {
    return {
      availableCourseContents: [],
      courseContentId: null,
      course: null,
      formIsLoading: true,
      courseContentIsLoading: true,
    }
  },
  created() {
    this.loadCourseContents()
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
  },
  watch: {
    courseContentId() {
      this.loadData()
    },
  },
  methods: {
    loadCourseContents() {
      this.courseContentIsLoading = true

      axios.get(`/backend/api/v1/course-contents/forms/${this.form.id}`).then(response => {
        this.availableCourseContents = response.data.courseContents.map(courseContent => {
          courseContent.fullTitle = `${courseContent.course.title} - ${courseContent.title}`
          return courseContent
        })

        if(this.availableCourseContents.length) {
          this.courseContentId = this.availableCourseContents[0].id
        }
      }).catch(e => {
        console.log(e)
      }).finally(() => {
        this.courseContentIsLoading = false
      })
    },
    loadData() {
      if (axiosCancel) {
        axiosCancel()
      }
      this.formIsLoading = true
      let cancelToken = new axios.CancelToken(c => {
        axiosCancel = c
      })

      const courseContent = this.availableCourseContents.find(courseContent => courseContent.id === this.courseContentId)
      this.course = courseContent.course

      axios.get(`/backend/api/v1/course-statistics/${courseContent.course.id}/forms/${this.courseContentId}`, {
        cancelToken,
        params: {
          ...this.pagination,
          search: this.search,
          tags: this.selectedTags,
        },
      }).then(response => {
        if(response instanceof axios.Cancel) {
          return
        }

        this.selected = []
        this.answerCount = response.data.count
        this.answers = response.data.answers
        this.fields = response.data.fields
      }).finally(() => {
        this.formIsLoading = false
      })
    },
  },
  components: {
    FormAnswerTable,
    TagSelect,
  },
}
</script>
