export default {
  props: [
    'appSettings',
    'availableModules',
    'isCandy',
    'notificationSettings',
    'profileId',
    'profiles',
    'profileSettings',
    'superadmin',
  ],
  data() {
    return {
      config: {},
      formIsValid: null,
      isSaving: false,
      savingSuccess: false,
    }
  },
  created() {
    this.cloneConfig()
  },
  methods: {
    cloneConfig() {
      Object.keys(this.config).forEach(key => {
        if (typeof this.profileSettings[key] !== 'undefined') {
          this.config[key] = this.profileSettings[key].original_value
        }
      })
    },
    updateAppConfigItems() {
      if (!this.formIsValid || this.isSaving) {
        return
      }
      this.savingSuccess = false
      this.isSaving = true
      let settings = []
      Object.keys(this.config).forEach(key => {
        if (typeof this.profileSettings[key] !== 'undefined') {
          settings.push({
            key,
            value: this.config[key],
          })
        }
      })
      axios.post(`/backend/api/v1/settings/profile/${this.profileId}`, { settings }).then(() => {
        Object.keys(this.config).forEach(key => {
          if (typeof this.profileSettings[key] !== 'undefined') {
            this.$emit('setSetting', {
              type: 'profileSetting',
              setting: key,
              value: this.config[key],
            })
          }
        })
        this.savingSuccess = true
        setTimeout(() => (this.isSaving = false), 1000)
      }).catch((error) => {
        if (error.response.data.error !== undefined) {
          alert(error.response.data.error)
        } else {
          alert('Die Einstellung konnte leider nicht gespeichert werden. Bitte probieren Sie es später erneut.')
        }
        this.isSaving = false
      })
    },
    updateSetting(data) {
      this.$emit('updateSetting', data)
    },
    setSetting(data) {
      this.$emit('setSetting', data)
    },
  },
}
