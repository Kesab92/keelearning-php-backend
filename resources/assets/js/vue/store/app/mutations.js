import { Store } from "vuex"

export default {
  setAppConfiguration(state, data) {
    state.languages = data.languages
    state.metaFields = data.metaFields
    state.appSettings = data.appSettings
    state.defaultAppProfileId = data.defaultAppProfileId
    state.appProfileSettings = data.appProfileSettings
    state.myRights = data.myRights
    state.isFullAdmin = data.isFullAdmin
    state.isMainAdmin = data.isMainAdmin
    state.isSuperAdmin = data.isSuperAdmin
    state.tagRights = data.tagRights
    state.profiles = data.profiles
    state.allowMaillessSignup = data.allowMaillessSignup
    state.userId = data.userId
    state.appHostedAt = data.app_hosted_at
    state.hasNewQuestionPreview = data.hasNewQuestionPreview
  },
}
