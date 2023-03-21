<template>
  <div>
    <Toolbar
      :report-data="report"
      :export-link="exportLink"
    />
    <div class="pa-4">
    <Report
      :report-settings.sync="report.settings"
      :report-tags.sync="report.tags"
      :report-type="reportType"
      :show-tag-select="true"
    />
      </div>
  </div>
</template>
'
<script>
import Toolbar from './ReportToolbar'
import Report from './components/Report'
export default {
  data () {
    return {
      report: {
        settings: [],
        tags: [],
      }
    }
  },
  computed: {
    // TODO To add selecting of a report type. It can depend on url
    reportType() {
      return 'users'
    },
    exportLink() {
      let settings = {}

      if (this.report.tags.length) {
        settings.tags = this.report.tags.join(",")
      }
      if (this.report.settings.length) {
        settings.settings = this.report.settings.join(",")
      }

      let query = Object.keys(settings).map(key => {
        if (!settings[key]) {
          return null
        }
        return `${encodeURIComponent(key)}=${encodeURIComponent(settings[key])}`
      }).filter(v => v !== null).join("&")
      return `/reports/${this.reportType}/export?${query}`
    },
  },
  components: {
    Toolbar,
    Report,
  },
}
</script>
