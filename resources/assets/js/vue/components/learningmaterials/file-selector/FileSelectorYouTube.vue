<template>
  <div>
    <v-text-field
      v-model="materialData.link"
      outline
      label="YouTube URL"
      :hint="(!youTubeURL) ? 'Geben Sie einen YouTubeLink ein' : ''"
      :hide-details="!!youTubeURL"
    />
    <YouTube :material-data="materialData" />
  </div>
</template>

<script>
import Helpers from '../../../logic/helpers.js'
import YouTube from "./preview/YouTube"
export default {
  components: {YouTube},
  props: ['materialData'],
  computed: {
    youTubeURL() {
      return Helpers.getYouTubeURL(this.materialData.link)
    },
  },
}
</script>
