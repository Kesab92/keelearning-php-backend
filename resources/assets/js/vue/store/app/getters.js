export default {
  languages: (state) => state.languages,
  metaFields: (state) => state.metaFields,
  appSettings: (state) => state.appSettings,
  myRights: (state) => state.myRights,
  profiles: (state) => state.profiles,
  appProfileSettings: (state) => {
    return (id) => {
      if(typeof state.appProfileSettings[id] === 'undefined') {
        return {}
      }
      return state.appProfileSettings[id]
    }
  },
  defaultAppProfileSettings: (state) => {
    if(!state.defaultAppProfileId) {
      return {}
    }
    if(typeof state.appProfileSettings[state.defaultAppProfileId] === 'undefined') {
      return {}
    }
    return state.appProfileSettings[state.defaultAppProfileId]
  },
  isFullAdmin: (state) => state.isFullAdmin,
  isMainAdmin: (state) => state.isMainAdmin,
  isSuperAdmin: (state) => state.isSuperAdmin,
  tagRights: (state) => state.tagRights,
  allowMaillessSignup: (state) => state.allowMaillessSignup,
  userId: (state) => state.userId,
  appHostedAt: (state) => state.appHostedAt,
  hasNewQuestionPreview: (state) => state.hasNewQuestionPreview,
  showEmails: (state) => {
    return (setting) => {
      if (state.isSuperAdmin) {
        return true
      }
      if (state.appSettings.hide_personal_data == '1') {
        return false
      }
      if (state.appSettings.hide_emails_backend == '1') {
        return false
      }
      return !!state.myRights[`${setting}-showemails`]
    }
  },
  showPersonalData: (state) => {
    return (setting) => {
      if (state.isSuperAdmin) {
        return true
      }
      if (state.appSettings.hide_personal_data == '1') {
        return false
      }
      return !!state.myRights[`${setting}-personaldata`]
    }
  },
}
