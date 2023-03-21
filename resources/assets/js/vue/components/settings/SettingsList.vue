<template>
  <div class="s-settingsContainer">
    <component
      :is="activeComponent"
      :app-settings="appSettings"
      :notification-settings="notificationSettings"
      :profile-settings="profileSettings"
      :available-modules="availableModules"
      :profile-id="profileId"
      :superadmin="superadmin"
      :is-candy="isCandy"
      :profiles="profiles"
      @updateSetting="updateSetting"
      @setSetting="setSetting" />
  </div>
</template>

<script>
  import AdminOptions from './areas/AdminOptions'
  import AdminDisabled from './areas/AdminDisabled'
  import ProfileContact from './areas/ProfileContact'
  import AdminLegacy from './areas/AdminLegacy'
  import ProfileSmtp from './areas/ProfileSmtp'
  import CustomerInfo from './areas/CustomerInfo'
  import ProfileDesign from './areas/ProfileDesign'
  import ProfileGeneral from "./areas/ProfileGeneral"
  import ProfileHome from "./areas/ProfileHome"
  import ProfileModules from "./areas/ProfileModules"
  import ProfileNotifications from "./areas/ProfileNotifications"
  import ProfileQuiz from "./areas/ProfileQuiz"
  import ProfileTest from "./areas/ProfileTest"
  import ProfileSignup from "./areas/ProfileSignup"
  import ProfileTranslations from "./areas/ProfileTranslations"
  export default {
    props: ['area', 'appSettings', 'notificationSettings', 'profileSettings', 'profileId', 'superadmin', 'availableModules', 'isCandy', 'profiles'],
    computed: {
      activeComponent() {
        const components = {
          'admin.options': AdminOptions,
          'admin.disabled': AdminDisabled,
          'admin.legacy': AdminLegacy,
          'customer.info': CustomerInfo,
          'profile.general': ProfileGeneral,
          'profile.contact': ProfileContact,
          'profile.home': ProfileHome,
          'profile.modules': ProfileModules,
          'profile.notifications': ProfileNotifications,
          'profile.signup': ProfileSignup,
          'profile.quiz': ProfileQuiz,
          'profile.test': ProfileTest,
          'profile.design': ProfileDesign,
          'profile.smtp': ProfileSmtp,
          'profile.translations': ProfileTranslations,
        }
        return components[this.area]
      },
    },
    methods: {
      updateSetting(data) {
        this.$emit('updateSetting', data)
      },
      setSetting(data) {
        this.$emit('setSetting', data)
      },
    },
  }
</script>

<style lang="scss" scoped>
  #app .s-settingsContainer {
    width: 100%;
    background: white;
    padding: 20px 20px 20px 35px;
  }
</style>
