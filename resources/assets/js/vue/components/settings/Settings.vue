<template>
  <v-layout
    row
    class="s-settingsWrapper">
    <Sidenav
      v-if="!isLoading"
      :selected="selectedArea"
      :profiles="profiles"
      :profile-id="profileId"
      :superadmin="superadmin"
      :is-candy="isCandy"
      @select="select" />
    <SettingsList
      v-if="!isLoading"
      :area="selectedArea"
      :app-settings="appSettings"
      :profiles="profiles"
      :profile-settings="profileSettings"
      :profile-id="profileId"
      :available-modules="availableModules"
      :superadmin="superadmin"
      :is-candy="isCandy"
      @updateSetting="updateSetting"
      @setSetting="setSetting" />
    <v-snackbar
      v-model="settingUpdated"
      color="success"
      top
      :timeout="2000"
    >
      Einstellung gespeichert
    </v-snackbar>
  </v-layout>
</template>

<script>
  import Sidenav from './Sidenav'
  import SettingsList from './SettingsList'
  export default {
    props: ['superadmin'],
    data() {
      return {
        isLoading: true,
        appSettings: null,
        profileSettings: null,
        profiles: [],
        isCandy: null,
        availableModules: null,
        settingUpdated: false,
      }
    },
    created() {
      this.loadData()
    },
    computed: {
      profileId() {
        return parseInt(this.$route.params.profileId, 10)
      },
      selectedArea() {
        return this.$route.params.area
      },
    },
    watch: {
      '$route'(newRoute, oldRoute) {
        if(newRoute.params.profileId !== oldRoute.params.profileId) {
          this.loadData()
        }
      },
    },
    methods: {
      select(area) {
        this.$router.push({
          name: 'settings',
          params: {
            profileId: this.profileId,
            area,
          }
        })
      },
      loadData() {
        this.isLoading = true
        let promises = []
        promises.push(axios.get('/backend/api/v1/settings/app').then(response => {
          this.appSettings = response.data
        }))
        promises.push(axios.get('/backend/api/v1/settings/profiles').then(response => {
          this.profiles = response.data
        }))
        promises.push(axios.get('/backend/api/v1/settings/availableModules').then(response => {
          this.availableModules = response.data
        }))
        promises.push(axios.get('/backend/api/v1/settings/isCandy').then(response => {
          this.isCandy = response.data.isCandy
        }))
        Promise.all(promises).then(() => {
          axios.get('/backend/api/v1/settings/profile/' + this.profileId).then(response => {
            this.profileSettings = response.data
            this.isLoading = false
          }).catch(() => {
            alert('Die Einstellungen zu diesem Profil konnten nicht geladen werden. Bitte probieren Sie es später erneut.')
          })
        }).catch(() => {
          alert('Die Einstellungen konnten nicht geladen werden. Bitte probieren Sie es später erneut.')
        })
      },
      setSetting(data) {
        if(data.type === 'appSetting') {
          this.$set(this.appSettings[data.setting], 'value', data.value)
        } else if(data.type === 'profileSetting') {
          this.$set(this.profileSettings[data.setting], 'value', data.value)
          this.$set(this.profileSettings[data.setting], 'original_value', data.value)
        } else if(data.type === 'notificationSetting') {
        }
      },
      updateSetting(data) {
        let settings
        let updateUrl
        if(data.type === 'appSetting') {
          settings = this.appSettings
          updateUrl = '/backend/api/v1/settings/app'
        } else if(data.type === 'profileSetting') {
          settings = this.profileSettings
          updateUrl = '/backend/api/v1/settings/profile/' + this.profileId
        } else {
          alert('Ungültiger Einstellungs Typ')
          return
        }
        let originalValue = settings[data.setting]
        this.$set(settings[data.setting], 'value', data.value)
        axios.post(updateUrl, {
          settings: [
            {
              key: data.setting,
              value: data.value,
            },
          ],
        })
        .then(() => {
          this.settingUpdated = true
          if(data.type === 'appSetting' && data.setting === 'has_candy_frontend') {
            this.isCandy = data.value
          }
          if(data.type === 'appSetting' && data.setting.indexOf('module_') === 0) {
            if(data.value) {
              if(!this.availableModules.includes(data.setting)) {
                this.availableModules.push(data.setting)
              }
            } else {
              let moduleIdx = this.availableModules.indexOf(data.setting)
              if(moduleIdx !== -1) {
                this.availableModules.splice(moduleIdx, 1)
              }
            }
          }
        })
        .catch(() => {
          this.$set(settings[data.settings], 'value', originalValue)
          alert('Die Einstellung konnte leider nicht gespeichert werden. Bitte probieren Sie es später erneut.')
        })
      }
    },
    components: {
      Sidenav,
      SettingsList,
    },
  }
</script>

<style lang="scss" scoped>
  #app .s-settingsWrapper {
    border-radius: 10px;
    overflow: hidden;
    flex-grow: 0;
    margin-left: -10px;
    box-shadow: 0 0.8px 0.5px rgba(0,0,0,.001), 0 1.4px 1px rgba(0,0,0,.001), 0 2.1px 1.7px rgba(0,0,0,.002), 0 2.6px 2.5px rgba(0,0,0,.002), 0 3.2px 3.6px rgba(0,0,0,.003), 0 3.8px 5.1px rgba(0,0,0,.003), 0 4.4px 7.3px rgba(0,0,0,.004), 0 5.3px 10.6px rgba(0,0,0,.005), 0 6.5px 16.3px rgba(0,0,0,.006), 0 10px 29px rgba(0,0,0,.01);
  }
</style>
