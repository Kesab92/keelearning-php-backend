<template>
  <div>
    <details-sidebar-toolbar>
      <template
        v-if="questionData"
        v-slot:default>
        <v-btn
          :loading="isSaving"
          color="primary"
          @click="$emit('save')"
        >
          Speichern
        </v-btn>

        <v-switch
          v-model="questionData.visible"
          class="ml-3"
          hide-details
          height="30"
          label="Sichtbar"
          @change="$emit('updateVisibility', questionData.visible)"
        />

        <v-spacer/>

        <v-btn
          :href="previewUrl"
          outline
          class="ml-0"
          target="_blank"
        >
          Vorschau
        </v-btn>

        <v-btn
          :loading="isSaving"
          color="red"
          outline
          @click="remove"
        >
          Löschen
        </v-btn>
      </template>
      <template
        v-if="myRights['questions-edit']"
        v-slot:alerts>
        <v-alert
          outline
          type="warning"
          :value="questionData.isreusableclone">
          Inhalte bitte nicht editieren, da diese aus einer globalen Vorlage stammen und ggf. von mehreren Kursen verwendet werden.
        </v-alert>
        <v-alert
          outline
          type="info"
          :value="questionData.type === $constants.QUESTIONS.TYPE_INDEX_CARD">
          Diese Frage wird nicht im Quiz angezeigt.
        </v-alert>
        <v-alert
          outline
          type="info"
          :value="!questionData.visible">
          Diese Frage ist derzeit nicht in der App sichtbar.
        </v-alert>
      </template>
    </details-sidebar-toolbar>
    <DeleteDialog
      v-model="deleteDialogOpen"
      :deletion-url="`/backend/api/v1/questions/${questionData.id}`"
      :dependency-url="`/backend/api/v1/questions/${questionData.id}/delete-information`"
      :entry-name="questionData.name"
      :redirect-url="afterDeletionRedirectURL"
      type-label="Frage"
      @deleted="handleQuestionDeleted"/>
  </div>
</template>

<script>
import DeleteDialog from "../partials/global/DeleteDialog";
import {mapGetters} from "vuex";

export default {
  props: [
    'questionData',
    'isSaving',
  ],
  data() {
    return {
      deleteDialogOpen: false,
    }
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
      appHostedAt: 'app/appHostedAt',
      hasNewQuestionPreview: 'app/hasNewQuestionPreview',
    }),
    afterDeletionRedirectURL() {
      return "/questions#/questions"
    },
    previewUrl() {
      if(this.hasNewQuestionPreview) {
        return `${this.appHostedAt}/questions/${this.questionData.id}/preview`
      } else {
        return `${this.appHostedAt}/training/category/${this.questionData.category_id}/${this.questionData.id}`
      }
    },
  },
  methods: {
    remove() {
      this.deleteDialogOpen = true
    },
    handleQuestionDeleted() {
      this.$store.commit("questions/deleteQuestion", this.questionData.id)
      this.$store.dispatch("questions/loadQuestions")
    },
  },
  components: {
    DeleteDialog,
  },
}
</script>
