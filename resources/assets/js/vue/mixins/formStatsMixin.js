import {mapGetters} from "vuex"
import {debounce} from "lodash"

export default {
  data() {
    return {
      answerCount: null,
      answers: [],
      fields: [],
      pagination: {
        page: 1,
        rowsPerPage: 50,
        sortBy: "id",
      },
      search: null,
      selectedTags: [],
    }
  },
  computed: {
    ...mapGetters({
      showPersonalData: 'app/showPersonalData',
    }),
    exportLink() {
      return '/course-statistics/' + this.course.id + '/export/form/' + this.courseContentId
    },
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
  },
  methods: {
    getFilterTags(tags) {
      if(this.course && this.course.tags.length > 0) {
        return tags.filter(tag => this.course.tags.includes(tag.id))
      }
      return tags
    },
  },
}
