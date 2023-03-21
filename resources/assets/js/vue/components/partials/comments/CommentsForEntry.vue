<template>
  <div class="pa-4">
    <div
      v-if="isLoading"
      class="text-sm-center my-3">
    <v-progress-circular
      indeterminate
      color="primary" />
    </div>
    <template v-else>
      <template v-if="comments.length > 0">
        <div v-if="pages > 1" class="text-sm-center my-3">
          <v-pagination
            :length="pages"
            v-model="pagination.page"
            :total-visible="pagination.visible"/>
        </div>
        <div v-for="item in visibleComments" :key="item.id"
             :ref="`comment-${item.id}`">
          <Comment
            :comment="item"
            :foreign-id="foreignId"
            :foreign-type="type"
            :class="{
              'blue lighten-5': item.id === scrollTo,
              '-deleted': item.deleted_at,
              'orange lighten-3': item.id !== scrollTo && hasOpenReport(item),
            }" />
          <div v-for="reply in item.replies" :key="reply.id"
               :ref="`comment-${reply.id}`">
            <Comment
              :comment="reply"
              :foreign-id="foreignId"
              :foreign-type="type"
              class="pl-5"
              :class="{
                'blue lighten-5': reply.id === scrollTo,
                '-deleted': reply.deleted_at,
                'orange lighten-3': reply.id !== scrollTo && hasOpenReport(reply),
              }" />
          </div>
        </div>
        <div v-if="pages > 1" class="text-sm-center my-3">
          <v-pagination
            v-model="pagination.page"
            :length="pages"
            :total-visible="pagination.visible"/>
        </div>
      </template>
      <v-alert
        v-else
        outline
        type="info"
        :value="true">
        Aktuell gibt es noch keine Kommentare.
      </v-alert>
    </template>
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import Comment from "./Comment"

export default {
  props: {
    foreignId: {
      type: Number,
      required: true,
    },
    type: {
      type: Number,
      required: true,
    },
    scrollTo: {
      type: Number,
      required: false,
      default: null,
    },
  },
  data() {
    return {
      pagination: {
        page: 1,
        perPage: 10,
        visible: 10,
      },
    }
  },
  computed: {
    ...mapGetters({
      comments: 'comments/commentsForEntry',
      isLoading: 'comments/listForEntryIsLoading'
    }),
    pages() {
      if (!this.comments) {
        return 1
      }
      return Math.ceil(this.comments.length / this.pagination.perPage)
    },
    visibleComments() {
      if (!this.comments) {
        return []
      }
      return this.comments.slice((this.pagination.page - 1) * this.pagination.perPage, this.pagination.page * this.pagination.perPage)
    },
  },
  async mounted() {
    await this.loadData()
    if (this.scrollTo) {
      const page = this.getPageWithSearchedComment()
      if (page) {
        this.pagination.page = page
        this.$nextTick(function () {
          if (this.$refs[`comment-${this.scrollTo}`]) {
            const commentOffset = this.$refs[`comment-${this.scrollTo}`][0].offsetTop
            this.$refs[`comment-${this.scrollTo}`][0].closest('.js-sidebar').scrollTop = commentOffset - 100
          }
        })
      }
    }
  },
  methods: {
    hasOpenReport(comment) {
      return comment.reports.some((report) => report.status == this.$constants.COMMENT_REPORTS.STATUS_REPORTED)
    },
    async loadData() {
      return await this.$store.dispatch('comments/loadCommentsForEntry', {
        foreignType: this.type,
        foreignId: this.foreignId,
      })
    },
    getPageWithSearchedComment() {
      for (let page = 1; page <= this.pages; page++) {
        const visibleComments = this.getVisibleComments(page);
        if (visibleComments.findIndex(comment => comment.id === this.scrollTo) > -1) {
          return page
        }

        const scrollToIsInsideReplies = visibleComments.find(comment => {
          if (comment.replies.length > 0 && comment.replies.findIndex(reply => reply.id === this.scrollTo) > -1) {
            return true
          }
        })

        if (scrollToIsInsideReplies) {
          return page
        }
      }
      return null
    },
    getVisibleComments(page) {
      return this.comments.slice((page - 1) * this.pagination.perPage, page * this.pagination.perPage)
    },
  },
  components: {
    Comment,
  }
}
</script>
