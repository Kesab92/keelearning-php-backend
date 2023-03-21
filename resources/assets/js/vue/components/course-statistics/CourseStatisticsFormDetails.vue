<template>
  <div>
    <v-layout
      row
      class="px-4 pt-4">
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
    <FormAnswerTable
      :answers="answers"
      :answer-count="answerCount"
      :fields="fields"
      :is-loading="isLoading"
      :pagination.sync="pagination"
      :show-personal-data="showPersonalData('courses')"
      />
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import TagSelect from "../partials/global/TagSelect"
import formStatsMixin from '../../mixins/formStatsMixin'
import FormAnswerTable from "../partials/form-stats/FormAnswerTable"

let axiosCancel = null

export default {
  mixins: [formStatsMixin],
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
      isLoading: true,
    }
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
    }),
    courseContentId() {
      return parseInt(this.$route.params.courseContentId, 10)
    },
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
      axios.get(`/backend/api/v1/course-statistics/${this.course.id}/forms/${this.courseContentId}`, {
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
        this.isLoading = false
      }).catch(e => {
        console.log(e)
      })
    },
  },
  components: {
    FormAnswerTable,
    TagSelect,
  },
}
</script>
