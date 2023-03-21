<template>
  <div>
    <details-sidebar-toolbar>
      <template v-slot:default>
        <v-btn
          :loading="isSaving"
          color="primary"
          @click="$emit('save')"
        >
          Speichern
        </v-btn>

        <v-spacer/>

        <v-btn
          :loading="isSaving"
          color="red"
          outline
          @click="remove"
        >
          Löschen
        </v-btn>
      </template>
      <template v-slot:alerts>
        <v-alert
          outline
          type="info"
          :value="!newsData.published_at">
          Die News ist erst in der App zu sehen wenn diese von Ihnen veröffentlicht wurde.<br>
          <v-btn
            :loading="isSaving"
            color="primary"
            @click="publishNow"
            class="ml-0"
          >
            Jetzt veröffentlichen
          </v-btn>
        </v-alert>
      </template>
    </details-sidebar-toolbar>
    <DeleteDialog
      v-model="deleteDialogOpen"
      :deletion-url="`/backend/api/v1/news/${newsData.id}`"
      :dependency-url="`/backend/api/v1/news/${newsData.id}/delete-information`"
      :entry-name="newsData.title"
      :redirect-url="afterDeletionRedirectURL"
      type-label="News"
      @deleted="handleNewsDeleted"/>
  </div>
</template>

<script>
import DeleteDialog from "../partials/global/DeleteDialog"

export default {
  props: [
    'newsData',
    'isSaving',
  ],
  data() {
    return {
      deleteDialogOpen: false,
    }
  },
  computed: {
    afterDeletionRedirectURL() {
      return "/news#/news"
    },
  },
  methods: {
    async save() {
      if (this.isSaving) {
        return
      }
      await this.$store.dispatch("news/saveNewsEntry", {
        id: this.newsData.id,
        published_at: this.newsData.published_at,
      })
    },
    publishNow() {
      this.$emit('publishNow')
    },
    remove() {
      this.deleteDialogOpen = true
    },
    handleNewsDeleted() {
      this.$store.commit("news/deleteNewsEntry", this.newsData.id)
      this.$store.dispatch("news/loadNews")
    },
  },
  components: {
    DeleteDialog,
  },
}
</script>
