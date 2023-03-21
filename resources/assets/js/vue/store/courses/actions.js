let loadCoursesCancel = null
let loadAllTemplatesCancel = null
let loadTemplatesCancel = null

export default {
  loadCourses({state, commit}) {
    if (loadCoursesCancel) {
      loadCoursesCancel()
    }
    commit('setCoursesListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadCoursesCancel = c
    })
    axios.get("/backend/api/v1/courses", {
      cancelToken,
      params: {
        ...state.pagination,
        filter: state.filter,
        search: state.search,
        tags: state.tags,
        categories: state.categories,
      },
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setCourseCount', response.data.count)
      commit('setCourses', response.data.courses)
      commit('setCoursesListLoading', false)
    })
  },
  loadAllTemplates({commit}) {
    if (loadAllTemplatesCancel) {
      loadAllTemplatesCancel()
    }
    let cancelToken = new axios.CancelToken(c => {
      loadAllTemplatesCancel = c
    })
    axios.get('/backend/api/v1/courses/templates', {
      cancelToken,
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setAllTemplates', response.data.templates)
    })
  },
  async loadCourse({state, commit, dispatch}, {courseId}) {
    const course = await axios.get('/backend/api/v1/courses/' + courseId).then((response) => {
      const course = response.data.course
      commit('setCourse', course)
      return state.courseDetails[course.id]
    })
    if(course.is_template) {
      await dispatch('templateInheritance/loadChildApps', null, { root: true })
    }
    return course
  },
  loadTemplates({ state, commit }) {
    if (loadTemplatesCancel) {
      loadTemplatesCancel()
    }
    commit('setTemplateListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadTemplatesCancel = c
    })
    axios.get('/backend/api/v1/courses', {
      cancelToken,
      params: {
        ...state.templates.pagination,
        filter: state.templates.filters.filter,
        categories: state.templates.filters.categories,
        search: state.templates.filters.search,
        tags: state.templates.filters.tags,
        templates: true,
      },
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setTemplateCount', response.data.count)
      commit('setTemplates', response.data.courses)
      commit('setTemplateListLoading', false)
    })
  },
  saveCourse({state, commit, dispatch}, data) {
    return axios.post('/backend/api/v1/courses/' + data.id, data).then((response) => {
      const course = response.data.course
      commit('setCourse', course)
      if(course.is_template) {
        dispatch('loadTemplates')
      } else {
        dispatch('loadCourses')
      }
      return state.courseDetails[course.id]
    })
  },
  loadReminders({state, commit}, {courseId}) {
    return axios.get(`/backend/api/v1/courses/${courseId}/reminders`).then((response) => {
      const reminders = response.data.reminders
      commit('setCourseReminders', reminders)
      return reminders
    })
  },
  storeReminder({state, commit, dispatch}, data) {
    return axios.post(`/backend/api/v1/courses/${data.courseId}/reminders`, data).then((response) => {
      const reminders = response.data.reminders
      commit('setCourseReminders', reminders)
      return reminders
    })
  },
  deleteReminder({state, commit, dispatch}, {courseId, reminderId}) {
    return axios.delete(`/backend/api/v1/courses/${courseId}/reminders/${reminderId}`).then((response) => {
      const reminders = response.data.reminders
      commit('setCourseReminders', reminders)
      return reminders
    })
  },
  archiveCourse({state, commit, dispatch}, data) {
    return axios.post(`/backend/api/v1/courses/${data.id}/archive`).then((response) => {
      const course = response.data.course
      commit('setCourse', course)
      dispatch('loadCourses')
      return state.courseDetails[course.id]
    })
  },
  unarchiveCourse({state, commit, dispatch}, data) {
    return axios.post(`/backend/api/v1/courses/${data.id}/unarchive`).then((response) => {
      const course = response.data.course
      commit('setCourse', course)
      dispatch('loadCourses')
      return state.courseDetails[course.id]
    })
  },
}
