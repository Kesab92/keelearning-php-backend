<template>
  <div>
    <v-layout
      row
      class="px-4 pt-4">
      <v-flex xs4>
        <v-text-field
          append-icon="search"
          clearable
          :placeholder="searchPlaceholder"
          single-line
          v-model="search"/>
      </v-flex>
      <v-spacer />
      <v-flex shrink>
        <v-btn
          :href="exportLink"
          target="_blank"
          color="primary"
          slot="activator">
          <v-icon
            dark
            left>cloud_download
          </v-icon>
          Ergebnisse exportieren
        </v-btn>
      </v-flex>
    </v-layout>
    <WBTStatsTable
      v-if="learningmaterial"
      :course-id="course.id"
      :learningmaterials="[this.learningmaterial.id]"
      :search="search" />
  </div>
</template>

<script>
import {mapGetters} from 'vuex'
import WBTStatsTable from '../partials/learningmaterials/WBTStatsTable'

export default {
  props: {
    course: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      search: null,
    }
  },
  computed: {
    ...mapGetters({
      showEmails: 'app/showEmails',
      showPersonalData: 'app/showPersonalData',
    }),
    courseContents() {
      return this.course.chapters.reduce((contents, chapter) => {
        chapter.contents.forEach(content => {
          contents.push(content)
        })
        return contents
      }, [])
    },
    learningmaterial() {
      const WBTContentId = parseInt(this.$route.params.wbtId, 10)
      let content = this.courseContents.find(content => content.id === WBTContentId)
      if(!content) {
        return null
      }
      return content.relatable
    },
    exportLink() {
      return '/course-statistics/' + this.course.id + '/export/wbt/' + this.$route.params.wbtId
    },
    searchPlaceholder() {
      if (!this.showPersonalData('courses')) {
        return 'ID'
      }
      if (!this.showEmails('courses')) {
        return 'Name / ID'
      }
      return 'Name / Mail / ID'
    },
  },
  components: {
    WBTStatsTable,
  },
}
</script>
