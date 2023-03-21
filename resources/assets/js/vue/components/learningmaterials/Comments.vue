<template>
  <CommentsForEntry
    v-if="appSettings.module_comments == 1 && material"
    :foreignId="material.id"
    :type="$constants.MORPH_TYPES.TYPE_LEARNINGMATERIAL"
    :scroll-to="scrollTo"
  />
</template>

<script>
import CommentsForEntry from "../partials/comments/CommentsForEntry"
import {mapGetters} from "vuex";
export default {
  props: ["material"],
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
    }),
    scrollTo() {
      if(this.$route.params.commentId) {
        return Number(this.$route.params.commentId)
      }
      return null
    },
  },
  components: {
    CommentsForEntry,
  },
}
</script>
