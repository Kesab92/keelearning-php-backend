<template>
  <div>
    <tag-select
      v-if="showTagSelect"
      v-model="reportTagData"
      color="blue-grey lighten-2"
      label="Benutzer filtern"
      multiple
      outline
      placeholder="Alle Benutzer"
      limit-to-tag-rights
      show-limited-tags
      :extend-items="getTagItems"
    />
    <div
      v-for="settingGroup in visibleSettingGroups"
      :key="settingGroup.title">
      <v-layout
        v-if="settingGroup.settings.length"
        row
        class="mb-2 mt-4">
        <v-flex>
          <h4 class="sectionHeader">
            {{ settingGroup.title }}
          </h4>
        </v-flex>
      </v-layout>
      <Toggle
        v-for="setting in settingGroup.settings"
        :key="setting.type"
        :label="setting.title"
        :value="hasSetting(setting.type)"
        @input="setSetting(setting.type, $event)"
        class="mb-2"/>
    </div>
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import reportDefinitions from "../reports"
import TagSelect from "../../partials/global/TagSelect"

export default {
  props: {
    reportSettings: {
      type: Array,
      required: true,
    },
    reportTags: {
      type: Array,
      required: false,
    },
    reportType: {
      type: String,
      required: true,
    },
    showTagSelect: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data() {
    return {
      reportSettingData: [],
      reportTagData: [],
      keyedReportDefinitions: {},
    }
  },
  created() {
    reportDefinitions[this.reportType].forEach(settingGroup => {
      settingGroup.settings.forEach(setting => {
        this.keyedReportDefinitions[setting.type] = setting
      })
    })

    if(this.reportSettings.length) {
      this.reportSettingData = JSON.parse(JSON.stringify(this.reportSettings))
    }

    this.reportTagData = JSON.parse(JSON.stringify(this.reportTags))
  },
  watch: {
    reportTagData: {
      handler() {
        this.updateReportTags()
      },
    },
    filteredSettingGroups: {
      handler() {
        if(this.filteredSettingGroups && !this.reportSettings.length) {
          this.setDefaultSettings()
        }
      },
      immediate: true,
      deep: true,
    },
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
      isSuperAdmin: 'app/isSuperAdmin',
      myRights: 'app/myRights',
      showEmails: 'app/showEmails',
      showPersonalData: 'app/showPersonalData',
    }),
    filteredSettingGroups() {
      if(!this.showEmails || !this.showPersonalData || !Object.keys(this.myRights).length) {
        return []
      }
      return reportDefinitions[this.reportType].map(settingGroup => {
        const settings = settingGroup.settings.filter((setting) => {
          if (setting.preventedBySetting && this.appSettings[setting.preventedBySetting] == 1) {
            return false
          }
          if (setting.showPersonalData && this.showPersonalData(setting.showPersonalData) == 0) {
            return false
          }
          if (setting.showEmails && this.showEmails(setting.showEmails) == 0) {
            return false
          }
          if (setting.module && this.appSettings[setting.module] != 1) {
            return false
          }
          if (setting.necessaryRights && !this.hasAccess(setting.necessaryRights)) {
            return false
          }
          return true
        })
        return {
          ...settingGroup,
          settings,
        }
      }).filter(settingGroup => {
        if (!settingGroup.settings.length) {
          return false
        }
        if (settingGroup.module && this.appSettings[settingGroup.module] != "1") {
          return false
        }
        return true
      })
    },
    visibleSettingGroups() {
      return JSON.parse(JSON.stringify(this.filteredSettingGroups)).map(settingGroup => {
        settingGroup.settings = settingGroup.settings.filter(setting => this.settingIsVisible(setting))

        return settingGroup
      })
    },
  },
  methods: {
    hasSetting(type) {
      const reportDefinition = this.keyedReportDefinitions[type]
      if (!reportDefinition) {
        return false
      }
      const requiresOneOf = reportDefinition.requiresOneOf
      if (requiresOneOf && !this.hasOneOfSettings(requiresOneOf)) {
        return false
      }
      const preventedBySetting = reportDefinition.preventedBySetting
      if (preventedBySetting && this.appSettings[preventedBySetting] == 1) {
        return false
      }
      return this.reportSettingData.includes(type)
    },
    hasOneOfSettings(types) {
      return types.some((type) => this.hasSetting(type))
    },
    setSetting(type, value) {
      if (value) {
        if (!this.reportSettingData.includes(type)) {
          this.reportSettingData.push(type)
        }
      } else {
        if (this.reportSettingData.includes(type)) {
          this.reportSettingData.splice(this.reportSettingData.indexOf(type), 1)
        }
      }
      this.updateReportSettings()
    },
    setDefaultSettings() {
      Object.values(this.filteredSettingGroups).forEach(settingGroup => {
        settingGroup.settings.forEach(setting => {
          if(setting.default) {
            this.reportSettingData.push(setting.type)
          }
        })
      })
      this.updateReportSettings()
    },
    settingIsVisible(setting) {
      if (setting.requiresOneOf && !this.hasOneOfSettings(setting.requiresOneOf)) {
        return false
      }
      return true
    },
    updateReportTags() {
      this.$emit('update:reportTags', this.reportTagData)
    },
    updateReportSettings() {
      this.$emit('update:reportSettings', this.reportSettingData)
    },
    hasAccess(necessarySettingRights) {
      return necessarySettingRights.some(right => {
        return this.myRights[right]
      })
    },
    getTagItems(items) {
      return [
        {
          label: "Benutzer ohne TAG",
          id: -1,
        },
      ].concat(items)
    },
  },
  components: {
    TagSelect,
  }
}
</script>
