<template>
  <div v-if="reportingData">
    <v-form
      v-model="formIsValid"
      @submit.prevent="save">
      <details-sidebar-toolbar>
        <v-btn
          :disabled="isSaving || !formIsValid"
          :loading="isSaving"
          color="primary"
          type="submit"
        >
          Speichern
        </v-btn>
        <v-spacer/>
        <v-btn
          :loading="isSaving"
          color="red"
          outline
          @click="deleteDialogOpen = true"
        >
          Löschen
        </v-btn>
      </details-sidebar-toolbar>

      <div class="pa-4">
        <v-text-field
          v-model="reportingData.emails"
          :rules="[$rules.emails]"
          class="mb-2"
          label="Empfänger E-Mail Adressen"
          persistent-hint
          required>
          <v-tooltip slot="append" bottom>
          <span
            slot="activator">
            <v-icon>info</v-icon>
          </span>
            An diese E-Mail Adressen wird der Report versendet. Sie können mehrere E-Mail Adressen mit einem Komma
            trennen.
          </v-tooltip>
        </v-text-field>
        <tag-select
          v-model="reportingData.tag_ids"
          slot="activator"
          color="blue-grey lighten-2"
          label="Benutzer-TAGs"
          placeholder="Alle Benutzer"
          persistent-hint
          multiple
          outline
          limitToTagRights
          show-limited-tags>
          <v-tooltip slot="append" bottom>
          <span
            slot="activator">
            <v-icon>info</v-icon>
          </span>
            Hier kann der Report auf Benutzer mit einem dieser TAGs eingeschränkt werden.
          </v-tooltip>
        </tag-select>
        <category-select
          v-if="reporting.type === $constants.REPORTINGS.TYPE_QUIZ"
          v-model="reportingData.category_ids"
          slot="activator"
          color="blue-grey lighten-2"
          label="Kategorien einschränken"
          persistent-hint
          multiple
          outline
        >
          <v-tooltip slot="append" bottom>
          <span
            slot="activator">
            <v-icon>info</v-icon>
          </span>
            Hier kann ausgewählt werden ob im Report nur bestimmte Kategorien gelistet sein sollen.
          </v-tooltip>
        </category-select>
        <v-select
          v-model="reportingData.interval"
          :items="intervals"
          slot="activator"
          label="Intervall"
          persistent-hint
          outline>
          <v-tooltip slot="append" bottom>
          <span
            slot="activator">
            <v-icon>info</v-icon>
          </span>
            Wie oft soll der Report versendet werden?
          </v-tooltip>
        </v-select>

        <DeleteDialog
          v-model="deleteDialogOpen"
          :deletion-url="`/backend/api/v1/reportings/${reporting.id}`"
          :dependency-url="`/backend/api/v1/reportings/${reporting.id}/delete-information`"
          :redirect-url="afterDeletionRedirectURL"
          type-label="Reporting"
          @deleted="handleReportingDeleted"/>
      </div>
    </v-form>
  </div>
</template>

<script>

import DeleteDialog from "../global/DeleteDialog"
import TagSelect from "../global/TagSelect"
import CategorySelect from "../global/CategorySelect"

export default {
  props: {
    reporting: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      formIsValid: true,
      reportingData: null,
      isSaving: false,
      deleteDialogOpen: false,
    }
  },
  watch: {
    reporting: {
      handler() {
        this.reportingData = JSON.parse(JSON.stringify(this.reporting))
      },
      immediate: true,
    },
  },
  computed: {
    intervals() {
      return Object.values(this.$constants.REPORTINGS.INTERVALS)
    },
    afterDeletionRedirectURL() {
      if(this.reporting.type === this.$constants.REPORTINGS.TYPE_QUIZ) {
        return "/stats/quiz/reporting#/stats/quiz/reporting"
      }
      if(this.reporting.type === this.$constants.REPORTINGS.TYPE_USERS) {
        return "/stats/users#/stats/users/reporting"
      }
      return '/'
    },
  },
  methods: {
    save() {
      if (!this.formIsValid || this.isSaving) {
        return
      }
      this.isSaving = true
      const tag_ids = this.reportingData.tag_ids
      const category_ids = this.reportingData.category_ids
      this.$store.dispatch("reportings/saveReporting", {
        id: this.reportingData.id,
        emails: this.reportingData.emails,
        interval: this.reportingData.interval,
        tag_ids,
        category_ids,
      }).catch(e => {
        if(e.response) {
          alert(e.response.data.message)
        } else {
          alert('Das Reporting konnte nicht erstellt werden.')
        }
      }).finally(() => {
        this.isSaving = false
      })
    },
    handleReportingDeleted() {
      this.$store.dispatch("reportings/updateReportings", this.reporting.type)
    },
  },
  components: {
    DeleteDialog,
    TagSelect,
    CategorySelect,
  },
}
</script>
