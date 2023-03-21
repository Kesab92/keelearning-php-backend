<template>
  <div
    ref="dot"
    :class="{
      '-certificate': isCertificate,
      '-test': isTest,
      '-passed': hasPassed,
      '-failed': hasFailed,
    }"
    v-click-outside="closeTooltip"
    @click="handleClick"
    class="c-courseProgress__content -clickable">
    <v-tooltip
      :activator="$refs.dot"
      bottom
      content-class="black v-tooltip__content--solid body-2"
      max-width="300"
      :value="tooltipOpen">
      <strong>{{ title }}</strong>
      <div class="body-1">
        <div v-if="hasPassed">Bestanden am {{ attempt.finished_at | dateTime }}</div>
        <div v-else-if="hasFailed">Gescheitert am {{ attempt.finished_at | dateTime }}</div>
        <div v-else>Noch nicht bearbeitet</div>
        <div v-if="showCertificateDownload">
          <v-btn
            download
            target="_blank"
            :href="attempt.certificateDownloadURL">
            Zertifikat herunterladen
          </v-btn>
        </div>
        <div v-if="content.tags">
          <v-chip
            :key="`${content.id}-${tag.id}`"
            disabled
            small
            v-for="tag in content.tags">
            {{ tag.label }}
          </v-chip>
        </div>
      </div>
    </v-tooltip>
  </div>
</template>

<script>
import ClickOutside from 'vue-click-outside'
import {mapGetters} from "vuex"
export default {
  props: {
    course: {
      type: Object,
      required: true,
    },
    content: {
      type: Object,
      required: true,
    },
    attempts: {
      type: [Array, Object],
      required: true,
    }
  },
  data() {
    return {
      tooltipOpen: false,
    }
  },
  computed: {
    ...mapGetters({
      showPersonalData: 'app/showPersonalData',
    }),
    title() {
      if(this.content.title) {
        return this.content.title
      }
      if(this.content.relatable && typeof this.content.relatable !== 'undefined') {
        return this.content.relatable.title
      }
      return ''
    },
    showCertificateDownload() {
      if(!this.attempt) {
        return false
      }
      if(!this.attempt.certificateDownloadURL) {
        return false
      }
      if(!this.showPersonalData('courses')) {
        return false
      }
      return true
    },
    attempt() {
      return this.attempts[this.content.id]
    },
    hasPassed() {
      return this.attempt && this.attempt.passed === 1
    },
    hasFailed() {
      return this.attempt && this.attempt.passed === 0
    },
    isCertificate() {
      return this.content.type === this.$constants.COURSES.TYPE_CERTIFICATE
    },
    isTest() {
      return this.content.type === this.$constants.COURSES.TYPE_QUESTIONS && this.content.is_test
    },
  },
  methods: {
    handleClick() {
      this.tooltipOpen = !this.tooltipOpen
    },
    closeTooltip() {
      if(this.tooltipOpen) {
        this.tooltipOpen = false
      }
    }
  },
  directives: {
    ClickOutside,
  },
}
</script>
