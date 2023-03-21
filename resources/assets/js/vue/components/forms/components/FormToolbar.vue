<template>
  <div>
    <details-sidebar-toolbar>
      <template
        v-if="formData"
        v-slot:default>
        <v-btn
          :loading="isSaving"
          :disabled="!isValid"
          color="primary"
          @click="$emit('save')"
        >
          {{ saveButtonText }}
        </v-btn>
        <v-btn
          v-if="formData.is_draft"
          :loading="isSaving"
          :disabled="!isValid"
          @click="$emit('publish')"
        >
          Veröffentlichen
        </v-btn>

        <v-spacer/>

        <v-menu offset-x offset-y>
          <v-btn
            slot="activator"
            flat
          >
            Aktionen
            <v-icon right>arrow_drop_down</v-icon>
          </v-btn>
          <v-list>
            <v-list-tile
              v-for="(action, index) in actions"
              :key="`form-action-${index}`"
              @click="doAction(action)"
            >
              <v-list-tile-title>{{ action.title }}</v-list-tile-title>
            </v-list-tile>
          </v-list>
        </v-menu>
      </template>
    </details-sidebar-toolbar>
    <v-alert
      outline
      type="info"
      color="grey"
      class="mb-4"
      :value="formData.is_archived">
      Dieses Formular ist archiviert und kann nicht bei neuen Kursen eingefügt werden.
    </v-alert>
    <DeleteDialog
      v-model="deleteDialogOpen"
      :deletion-url="`/backend/api/v1/forms/${formData.id}`"
      :dependency-url="`/backend/api/v1/forms/${formData.id}/delete-information`"
      :entry-name="formData.name"
      :redirect-url="afterDeletionRedirectURL"
      type-label="Formular"
      @deleted="handleFormDeleted">
    </DeleteDialog>
    <ArchiveFormModal
      v-model="archiveModalOpen"
      :form="formData" />
  </div>
</template>

<script>

import {mapGetters} from "vuex"
import ArchiveFormModal from "./ArchiveFormModal"
import DeleteDialog from "../../partials/global/DeleteDialog"

export default {
  props: {
    formData: {
      type: Object,
      required: true,
    },
    isValid: {
      type: Boolean,
      required: false,
      default: true,
    },
  },
  data () {
    return {
      archiveModalOpen: false,
      deleteDialogOpen: false,
    }
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    isSaving: {
      get() {
        return this.$store.state.forms.isSaving
      },
      set(data) {
        this.$store.commit('forms/setIsSaving', data)
      },
    },
    actions() {
      const actions = [
        {
          name: 'delete',
          title: 'Löschen',
        },
      ]

      if(!this.formData.is_draft) {
        actions.push({
          name: 'convert-to-draft',
          title: 'Veröffentlichung zurücknehmen',
        })
      }
      if (this.formData.is_archived) {
        actions.push({
          name: 'unarchive',
          title: 'Dearchivieren'
        })
      } else {
        actions.push({
          name: 'archive',
          title: 'Archivieren'
        })
      }

      return actions
    },
    afterDeletionRedirectURL() {
      return "/forms#/forms"
    },
    saveButtonText() {
      if(this.formData.is_draft) {
        return 'Entwurf speichern'
      }
      return `Speichern`
    },
  },
  methods: {
    doAction(action) {
      switch (action.name) {
        case 'delete':
          this.deleteDialogOpen = true
          break
        case 'convert-to-draft':
          this.convertToDraft()
          break
        case 'archive':
          this.archiveModalOpen = true
          break
        case 'unarchive':
          this.unarchive()
          break
      }
    },
    handleFormDeleted() {
      this.$store.commit("forms/deleteForm", this.formData.id)
      this.$store.dispatch("forms/loadForms")
    },
    convertToDraft() {
      if (this.isSaving) {
        return
      }
      this.$store.dispatch("forms/convertFormToDraft", {
        id: this.formData.id,
      })
    },
    async unarchive() {
      if (this.isSaving) {
        return
      }

      this.isSaving = true
      await this.$store.dispatch("forms/unarchiveForm", {
        id: this.formData.id,
      })
      this.isSaving = false
    },
  },
  components: {
    ArchiveFormModal,
    DeleteDialog,
  },
}
</script>
