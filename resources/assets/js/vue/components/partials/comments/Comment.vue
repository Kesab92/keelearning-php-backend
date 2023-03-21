<template>
  <v-card
    class="c-comment">
    <v-card-title class="pb-0">
      <v-avatar
        v-if="showPersonalData('comments')"
        size="36px"
        class="mr-2">
        <img
          :class="{
            'c-comment__avatar': comment.deleted_at,
          }"
          v-if="comment.author.avatar"
          :alt="comment.author.username"
          :src="comment.author.avatar">
      </v-avatar>
      <div>
        <div
          v-if="showPersonalData('comments')"
          class="font-weight-bold">
          {{ comment.author.username }}
        </div>
        <div class="grey--text">{{ comment.created_at | dateTime }}</div>
        <v-chip
          v-if="comment.deleted_at"
          disabled
          small>
          Gelöscht
        </v-chip>
        <v-chip
          v-if="!!openReports.length"
          disabled
          small>
          Gemeldet
        </v-chip>
      </div>
      <v-spacer></v-spacer>
      <v-menu
        v-if="availableActions.length > 0"
        bottom
        left
        offset-y>
        <v-btn
          slot="activator"
          icon>
          <v-icon>more_vert</v-icon>
        </v-btn>

        <v-list>
          <v-list-tile
            v-for="(item, i) in availableActions"
            :key="i"
            @click="selectAction(item.name)">
            <v-list-tile-action class="o-commentActionItem">
              {{ item.title }}
            </v-list-tile-action>
          </v-list-tile>
        </v-list>
      </v-menu>
    </v-card-title>
    <v-card-text>
      <div>{{ comment.body }}</div>

      <div
      v-if="comment.attachments.length"
      class="mt-3">
        <b>Anhänge</b>
        <ul>
          <li
            v-for="attachment in comment.attachments"
            :key="attachment.id">
            <a
              :href="attachment.file_url"
              target="_blank">
              {{ attachment.original_filename }}
            </a>
          </li>
        </ul>
      </div>

      <template v-if="!this.comment.deleted_at">
        <v-btn v-if="!replyOpen" flat small class="ml-0" @click="replyOpen = true"><v-icon small left>reply</v-icon>Antworten</v-btn>
        <v-form @submit.prevent="submitReply" class="s-reply mt-2" v-if="replyOpen">
          <v-textarea v-model="replyBody" required autofocus auto-grow :rows="3" hide-details label="Antwort" outline />
          <div class="mt-2">
            <v-btn type="submit" class="ml-0" color="primary" :disabled="replyIsSubmitting">Absenden</v-btn>
            <v-btn flat @click="resetReply">Abbrechen</v-btn>
          </div>
        </v-form>
      </template>

      <v-alert
        v-for="report in openReports"
        :key="report.id"
        :value="true"
        color="warning">
          <strong>Offene Meldung von <a :href="`/users#/users/${report.reporter.id}/message`">{{ report.reporter.username }}</a> ({{ $constants.COMMENT_REPORTS.REASON_PHRASES[report.reason] }}):</strong><br>
          {{ report.reason_explanation }}
      </v-alert>
      <v-alert
        v-for="report in justifiedReports"
        :key="report.id"
        :value="true"
        outline
        color="warning">
          <strong>Meldung von <a :href="`/users#/users/${report.reporter.id}/message`">{{ report.reporter.username }}</a> ({{ $constants.COMMENT_REPORTS.REASON_PHRASES[report.reason] }}):</strong><br>
          {{ report.reason_explanation }}<br><br>
          <strong>Gelöscht von <a :href="`/users#/users/${report.statusManager.id}/message`">{{ report.statusManager.username }}</a>:</strong><br>
          {{ report.status_explanation }}
      </v-alert>
      <v-alert
        v-for="report in unjustifiedReports"
        :key="report.id"
        :value="true"
        outline
        color="info">
          <strong>Meldung von <a :href="`/users#/users/${report.reporter.id}/message`">{{ report.reporter.username }}</a> ({{ $constants.COMMENT_REPORTS.REASON_PHRASES[report.reason] }}):</strong><br>
          {{ report.reason_explanation }}<br><br>
          <strong>Als unbedenklich markiert von <a :href="`/users#/users/${report.statusManager.id}/message`">{{ report.statusManager.username }}</a>:</strong><br>
          {{ report.status_explanation }}
      </v-alert>
    </v-card-text>
    <DeleteCommentModal
      v-model="deleteCommentModalOpen"
      :comment="comment"
      :reason-is-required="!!openReports.length"
      @deleted="loadData"
    />
    <MarkCommentAsHarmlessModal
      v-model="harmlessCommentModalOpen"
      :comment="comment"
      @marked="loadData"
    />
  </v-card>
</template>

<script>
import { mapGetters } from 'vuex'
import DeleteCommentModal from "./DeleteCommentModal"
import MarkCommentAsHarmlessModal from "./MarkCommentAsHarmlessModal"

export default {
  props: {
    comment: {
      type: Object,
      required: true,
    },
    foreignType: {
      type: Number,
      required: false,
      default: null,
    },
    foreignId: {
      type: Number,
      required: false,
      default: null,
    },
  },
  data() {
    return {
      deleteCommentModalOpen: false,
      harmlessCommentModalOpen: false,
      replyOpen: false,
      replyBody: '',
      replyIsSubmitting: false,
    }
  },
  created() {
    this.resetReply()
  },
  computed: {
    ...mapGetters({
      showPersonalData: 'app/showPersonalData',
    }),
    availableActions() {
      let actions = []
      if (!this.comment.deleted_at) {
        actions.push({
          name: 'delete',
          title: 'Löschen',
        })
      }
      if (!!this.openReports.length) {
        actions.push({
          name: 'harmless',
          title: 'Als harmlos markieren',
        })
      }
      return actions
    },
    justifiedReports() {
      return this.comment.reports.filter((report) => report.status == this.$constants.COMMENT_REPORTS.STATUS_PROCESSED_JUSTIFIED)
    },
    openReports() {
      return this.comment.reports.filter((report) => report.status == this.$constants.COMMENT_REPORTS.STATUS_REPORTED)
    },
    unjustifiedReports() {
      return this.comment.reports.filter((report) => report.status == this.$constants.COMMENT_REPORTS.STATUS_PROCESSED_UNJUSTIFIED)
    },
  },
  methods: {
    selectAction(action) {
      if (action === 'delete') {
        this.delete()
      }
      if (action === 'harmless') {
        this.markAsHarmless();
      }
    },
    delete() {
      this.deleteCommentModalOpen = true
    },
    markAsHarmless() {
      this.harmlessCommentModalOpen = true
    },
    loadData() {
      this.$store.dispatch('comments/loadComments')
      if (this.foreignType && this.foreignId) {
        this.$store.dispatch('comments/loadCommentsForEntry', {
          foreignType: this.foreignType,
          foreignId: this.foreignId,
        })
      }
    },
    submitReply() {
      if(this.replyIsSubmitting) {
        return
      }
      if(!this.replyBody) {
        alert('Bitte geben Sie eine Antwort ein, bevor Sie den Kommentar absenden.')
        return
      }
      this.replyIsSubmitting = true
      this.$store.dispatch('comments/submitReply', { commentId: this.comment.id, body: this.replyBody }).then(() => {
        this.loadData()
        this.resetReply()
      }).catch(() => {
        alert('Ihre Antwort konnte leider nicht übermittelt werden. Bitte versuchen Sie es später erneut.')
      }).finally(() => {
        this.replyIsSubmitting = false
      })
    },
    resetReply() {
      this.replyOpen = false
      this.replyBody = `@${this.comment.author.username} `
    },
  },
  components: {
    DeleteCommentModal,
    MarkCommentAsHarmlessModal,
  }
}
</script>
<style>
.o-commentActionItem {
  cursor: pointer;
}
</style>

<style scoped lang="scss">
#app {
  .c-comment.-deleted {
    color: rgba(0, 0, 0, 0.5);
  }
  .c-comment__avatar {
    opacity: 0.5;
  }
}

#app .c-comment__avatar {
  opacity: 0.5;
}

#app .s-reply {
  background-image: radial-gradient(circle, #ecebee 1.5px, transparent 2px);
  background-repeat: repeat-y;
  background-size: 4px 8px;
  padding-left: 16px;
}
</style>
