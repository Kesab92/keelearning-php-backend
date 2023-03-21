<template>
  <iframe
    v-if="youTubeURL"
    class="mt-2"
    frameborder="0"
    allowfullscreen="1"
    allow="autoplay; encrypted-media"
    width="320"
    height="200"
    :src="youTubeURL" />
</template>

<script>
import Helpers from "../../../../../logic/helpers"

export default {
  props: ['attachment'],
  computed: {
    youTubeURL() {
      return Helpers.getYouTubeURL(this.attachment.link)
    }
  }
}
</script>
