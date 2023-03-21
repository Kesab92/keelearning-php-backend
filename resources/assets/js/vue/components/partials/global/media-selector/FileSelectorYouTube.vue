<template>
  <div>
    <v-text-field
      :value="attachment.link"
      @input="update($event)"
      outline
      label="YouTube URL"
      :hint="(!youTubeURL) ? 'Geben Sie einen YouTubeLink ein' : ''"
      :hide-details="!!youTubeURL"
    />
    <YouTube
      v-if="attachment"
      :attachment="attachment" />
  </div>
</template>

<script>
import Helpers from '../../../../logic/helpers.js'
import YouTube from "./preview/YouTube"
export default {
  props: ['value'],
  computed: {
    attachment() {
      return this.value ? this.value : null
    },
    youTubeURL() {
      if (this.attachment.link) {
        return Helpers.getYouTubeURL(this.attachment.link)
      }
      return null
    },
  },
  methods: {
    update(link) {
      this.$emit('input', {...this.attachment, link:link})
    },
  },
  components: {
    YouTube,
  },
}
</script>
