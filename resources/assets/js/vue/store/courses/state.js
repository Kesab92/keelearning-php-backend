import constants from '../../logic/constants'

const defaultState = {
  courseDetails: {},
  listIsLoading: false,
  filter: constants.COURSES.FILTERS.ACTIVE,
  search: null,
  tags: [],
  categories: [],
  pagination: {
    sortBy: 'id',
    descending: true,
    page: 1,
    rowsPerPage: 50,
  },
  courses: [],
  courseCount: 0,
  isSaving: false,
  courseReminders: [],
  allTemplates: {
    global: [],
    local: [],
  },
  templates: {
    count: 0,
    entries: [],
    filters: {
      categories: [],
      filter: constants.COURSES.FILTERS.VISIBLE,
      search: null,
      tags: [],
    },
    isLoading: false,
    pagination: {
      sortBy: 'id',
      descending: true,
      page: 1,
      rowsPerPage: 50,
    },
  },
  reminderEmails: []
}

export default defaultState
