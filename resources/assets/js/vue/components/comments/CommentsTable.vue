<template>
  <div>
    <v-data-table
      :headers="headers"
      :items="comments"
      :loading="isLoading"
      :pagination.sync="pagination"
      :rows-per-page-items="[50, 100, 200]"
      :total-items="commentsCount"
      class="elevation-1"
      item-key="id">
      <tr
        @click="openComment(props.item)"
        class="clickable s-comments__row"
        :class="{
          invisible: props.item.deleted_at,
          'orange lighten-3': hasOpenReport(props.item),
        }"
        slot="items"
        slot-scope="props">
        <td>
          <template v-if="props.item.body">
            {{ props.item.body | truncate(200) }}
          </template>
          <div
            v-else
            class="grey--text">
            {{ props.item.attachments.length > 1 ? `${props.item.attachments.length} Dateien` : `1 Datei` }}
          </div>
        </td>
        <td v-if="showPersonalData('comments')">
          {{ props.item.author.username }}
        </td>
        <td>
          {{ getStatus(props.item) }}
        </td>
        <td>
          {{ props.item.created_at | dateTime }}
        </td>
        <td>
          {{ props.item.commentable.title }}
        </td>
        <td>
          {{ $constants.MORPH_TYPE_LABELS[props.item.foreign_type] }}
        </td>
      </tr>
    </v-data-table>
  </div>
</template>

<script>
import {mapGetters} from "vuex";

export default {
  watch: {
    pagination: {
      handler() {
        this.loadData()
      },
      deep: true,
    },
  },
  computed: {
    ...mapGetters({
      commentsCount: 'comments/commentsCount',
      comments: 'comments/comments',
      isLoading: 'comments/listIsLoading',
      showPersonalData: 'app/showPersonalData',
    }),
    headers() {
      let headers = [{
        text: 'Kommentar',
        value: 'body',
        sortable: false,
      }]
      if (this.showPersonalData('comments')) {
        headers.push({
          text: 'Benutzer',
          value: 'author.username',
          sortable: false,
        })
      }
      headers = headers.concat([
        {
          text: 'Status',
          value: 'status',
          sortable: false,
        },
        {
          text: 'Kommentiert am',
          value: 'created_at',
        },
        {
          text: 'Titel',
          value: 'commentable.title',
          sortable: false,
        },
        {
          text: 'Inhaltstyp',
          value: 'foreign_type',
          sortable: false,
        },
      ])
    },
    pagination: {
      get() {
        return this.$store.state.comments.pagination
      },
      set(data) {
        this.$store.commit('comments/setPagination', data)
      },
    },
  },
  methods: {
    hasOpenReport(comment) {
      return comment.reports.some((report) => report.status == this.$constants.COMMENT_REPORTS.STATUS_REPORTED)
    },
    openComment(comment) {
      if (comment.foreign_type === this.$constants.MORPH_TYPES.TYPE_NEWS) {
        this.$router.push({
          name: 'comments.news.edit.comments',
          params: {
            commentId: comment.id,
            newsId: comment.foreign_id,
          },
        })
      }
      if (comment.foreign_type === this.$constants.MORPH_TYPES.TYPE_COURSE) {
        this.$router.push({
          name: 'comments.courses.edit.comments',
          params: {
            commentId: comment.id,
            courseId: comment.foreign_id,
          },
        })
      }
      if (comment.foreign_type === this.$constants.MORPH_TYPES.TYPE_LEARNINGMATERIAL) {
        this.$router.push({
          name: 'comments.learningmaterials.edit.comments',
          params: {
            commentId: comment.id,
            folderId: comment.commentable.learning_material_folder_id,
            learningmaterialId: comment.foreign_id,
          },
        })
      }
      if (comment.foreign_type === this.$constants.MORPH_TYPES.TYPE_COURSE_CONTENT_ATTEMPT) {
        this.$router.push({
          name: 'comments.courses.edit.todolists.results',
          params: {
            contentId: comment.commentable.content_id,
            participationId: comment.commentable.participation_id,
            courseId: comment.commentable.course_id,
          },
        })
      }
    },
    loadData() {
      this.$store.dispatch('comments/loadComments')
    },
    getStatus(comment) {
      if (comment.deleted_at) {
        return 'gelöscht'
      }
      if (this.hasOpenReport(comment)) {
        return 'gemeldet'
      }
      if (comment.reports.some((report) => report.status == this.$constants.COMMENT_REPORTS.STATUS_PROCESSED_UNJUSTIFIED)) {
        return 'gemeldet, freigegeben'
      }
      return ''
    },
  },
}
</script>

<style lang="scss">
#app .s-comments__row td {
  height: 90px !important;
}

.s-comments__row.invisible td {
  color: rgba(0, 0, 0, 0.5);

  ::v-deep .v-icon {
    opacity: 0.5;
  }
}
</style>
