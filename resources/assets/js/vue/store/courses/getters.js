export default {
  course(state) {
    return (id) => state.courseDetails[id]
  },
  courseCount: (state) => state.courseCount,
  courses: (state) => state.courses,
  listIsLoading: (state) => state.listIsLoading,
  isSaving: (state) => state.isSaving,
  courseReminders: (state) => state.courseReminders,
  templateCount: (state) => state.templates.count,
  templateListIsLoading: (state) => state.templates.isLoading,
  templates: (state) => state.templates.entries,
  allTemplates: (state) => state.allTemplates,
  reminderEmails: (state) => state.reminderEmails,
}
