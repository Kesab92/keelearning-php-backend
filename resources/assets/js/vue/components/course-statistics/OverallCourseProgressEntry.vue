<template>
  <div
    ref="dot"
    :class="{
      '-certificate': isCertificate,
      '-test': isTest,
      '-passed': courseProgress[content.id] > 0,
    }"
    v-click-outside="closeTooltip"
    @click="handleClick"
    :style="{'--opacity': opacity}"
    class="c-courseProgress__content -clickable">
    <v-tooltip
      :activator="$refs.dot"
      bottom
      content-class="black v-tooltip__content--solid body-2"
      max-width="300"
      :value="tooltipOpen">
      <strong>{{ title }}</strong>
      <div class="body-1">
        {{ Math.round(passedPercentage * 100) }}% bestanden
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
export default {
  props: {
    course: {
      type: Object,
      required: true,
    },
    userCount: {
      type: Number,
      default: null,
    },
    courseProgress: {
      type: Object,
      required: true,
    },
    content: {
      type: Object,
      required: true,
    }
  },
  data() {
    return {
      tooltipOpen: false,
    }
  },
  computed: {
    title() {
      if(this.content.title) {
        return this.content.title
      }
      if(this.content.relatable && typeof this.content.relatable !== 'undefined') {
        return this.content.relatable.title
      }
      return ''
    },
    passedPercentage() {
      if(typeof this.courseProgress[this.content.id] === 'undefined') {
        return 0
      }
      return this.courseProgress[this.content.id] / this.userCount
    },
    opacity() {
      const min = 0.3
      return Math.max(this.passedPercentage, min)
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
