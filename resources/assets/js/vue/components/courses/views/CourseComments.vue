<template>
  <CommentsForEntry
    v-if="appSettings.module_comments == 1"
    :foreignId="course.id"
    :type="$constants.MORPH_TYPES.TYPE_COURSE"
    :scroll-to="scrollTo"
  />
</template>

<script>
import CommentsForEntry from "../../partials/comments/CommentsForEntry"
import {mapGetters} from "vuex";
export default {
  props: ['course'],
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
