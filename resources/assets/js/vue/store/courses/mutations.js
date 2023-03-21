import Vue from 'vue'

export default {
  deleteCourse(state, id) {
    Vue.delete(state.courseDetails, id)
  },
  setCategories(state, categories) {
    state.categories = categories
  },
  setCourse(state, course) {
    Vue.set(state.courseDetails, course.id, course)
  },
  setCourseCount(state, count) {
    state.courseCount = count
  },
  setCourses(state, courses) {
    state.courses = courses
  },
  setCoursesListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setPagination(state, pagination) {
    Vue.set(state, 'pagination', pagination)
  },
  setSearch(state, search) {
    state.search = search
  },
  setTags(state, tags) {
    state.tags = tags
  },
  setIsSaving(state, isSaving) {
    state.isSaving = isSaving
  },
  setFilter(state, filter) {
    state.filter = filter
  },
  setCourseReminders(state, courseReminders) {
    state.courseReminders = courseReminders
  },
  setTemplateCount(state, count) {
    Vue.set(state.templates, 'count', count)
  },
  setTemplateFilter(state, filter) {
    Vue.set(state.templates.filters, filter.field, filter.value)
  },
  setTemplateListLoading(state, isLoading) {
    Vue.set(state.templates, 'isLoading', isLoading)
  },
  setTemplatePagination(state, pagination) {
    Vue.set(state.templates, 'pagination', pagination)
  },
  setTemplates(state, templates) {
    Vue.set(state.templates, 'entries', templates)
  },
  setAllTemplates(state, templates) {
    Vue.set(state, 'allTemplates', templates)
  },
  setReminderEmails(state, reminderEmails) {
    state.reminderEmails = reminderEmails
  },
}
