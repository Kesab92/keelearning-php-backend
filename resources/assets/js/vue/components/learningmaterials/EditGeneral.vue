<template>
  <div v-if="materialData">
    <details-sidebar-toolbar>
      <template v-slot:default>
        <v-btn
          color="primary"
          @click="save"
          :loading="isSaving"
          :disabled="isSaving">
          Speichern
        </v-btn>
        <v-switch
          v-model="materialData.visible"
          class="mt-0 pt-0"
          hide-details
          height="30"
          label="Sichtbar"
        />
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
            <v-list-tile @click="duplicate">
              <v-list-tile-title>Duplizieren</v-list-tile-title>
            </v-list-tile>
            <v-list-tile @click="remove">
              <v-list-tile-title>Löschen</v-list-tile-title>
            </v-list-tile>
          </v-list>
        </v-menu>
      </template>
      <template v-slot:alerts>
        <reusable-clone-warning v-if="materialData.is_reusable_clone" />
        <UsageInfoAlert :material="materialData" />
      </template>
    </details-sidebar-toolbar>

    <div class="pa-4">
      <v-layout
        row
        align-center
        class="mb-4">
        <v-flex grow>
          <translated-input
            v-model="materialData.title"
            label="Name"
            :translations="materialData.translations"
            attribute="title" />
        </v-flex>
        <v-btn
          outline
          color="secondary"
          @click="locationSelectModalOpen = true"
        >
          <v-icon left>input</v-icon>
          Verschieben
        </v-btn>
      </v-layout>

      <tag-select
        multiple
        label="Sichtbar für folgende User"
        placeholder="Alle"
        outline
        v-model="materialData.tags"
      />

      <TranslatedFileSelector
        v-model="materialData"
        class="my-4"
        ref="fileSelector"
        @update="update"
        @reset="reset"
        :upload-url="`/backend/api/v1/learningmaterials/${materialData.id}/upload`"/>

      <div class="subheading mt-5">Veröffentlichung</div>
      <v-alert
        :value="publishedInFuture"
        color="warning"
        icon="priority_high"
        outline
      >
        Diese Datei ist aktuell nicht sichtbar, wird aber am {{ material.published_at | date }} automatisch veröffentlicht.
      </v-alert>
      <v-date-picker
        v-if="materialData.published_at || forcePublishedAtDatePicker"
        v-model="materialData.published_at"
        first-day-of-week="1"
        locale="de-DE"
        :show-current="true"
        landscape/>
      <v-btn
        v-else
        outline
        color="secondary"
        class="mx-0"
        @click="forcePublishedAtDatePicker = true">
        Datum setzen
      </v-btn>

      <SendNotification
        class="mt-4"
        v-model="materialData"
        :material="material"
        @activateNotification="activateNotification" />

      <DeleteDialog
        v-model="deleteDialogOpen"
        :dependency-url="`/backend/api/v1/learningmaterials/${materialData.id}/delete-information`"
        :deletion-url="`/backend/api/v1/learningmaterials/${materialData.id}`"
        :redirect-url="`/learningmaterials#/learningmaterials/${materialData.learning_material_folder_id}`"
        @deleted="handleFileDeleted"
        type-label="Datei"
        :entry-name="materialData.title"/>
      <SelectLocationModal
        v-model="locationSelectModalOpen"
        :preselected-folder="materialData.learning_material_folder_id"
        :allow-root="false"
        :callback="moveFile" />
    </div>
  </div>
</template>

<script>
import moment from 'moment'
import SendNotification from "./SendNotification"
import DeleteDialog from "../partials/global/DeleteDialog"
import ReusableCloneWarning from "../partials/global/ReusableCloneWarning"
import SelectLocationModal from "./SelectLocationModal"
import TranslatedFileSelector from "./file-selector/TranslatedFileSelector"
import TagSelect from "../partials/global/TagSelect"
import UsageInfoAlert from "./UsageInfoAlert"

export default {
  props: ['material'],
  data() {
    return {
      materialData: null,
      isSaving: false,
      deleteDialogOpen: false,
      locationSelectModalOpen: false,
      forcePublishedAtDatePicker: false,
    }
  },
  watch: {
    material: {
      handler() {
        if(!this.material) {
          return
        }
        this.materialData = JSON.parse(JSON.stringify(this.material))
        if(this.materialData.published_at) {
          this.materialData.published_at = moment(this.materialData.published_at).format('YYYY-MM-DD')
        }
      },
      immediate: true,
    },
  },
  computed: {
    publishedInFuture() {
      if(!this.material.published_at) {
        return false
      }
      return moment(this.material.published_at).isAfter(moment())
    },
  },
  methods: {
    async save() {
      this.isSaving = true
      let publishedAt = this.materialData.published_at
      if(publishedAt) {
        publishedAt = moment(publishedAt).format('YYYY-MM-DD 00:00:00')
      }
      await this.$store.dispatch('learningmaterials/saveLearningmaterial', {
        id: this.materialData.id,
        title: this.materialData.title,
        tags: this.materialData.tags,
        published_at: publishedAt,
        link: this.materialData.link,
        visible: this.materialData.visible,
        download_disabled: this.materialData.download_disabled,
        show_watermark: this.materialData.show_watermark,
        subtitles_language: this.materialData.subtitles_language,
        wbt_subtype: this.materialData.wbt_subtype,
      })
      if (this.$refs.fileSelector) {
        this.$refs.fileSelector.refresh()
      }
      this.isSaving = false
    },
    remove() {
      this.deleteDialogOpen = true
    },
    handleFileDeleted() {
      this.$store.commit('learningmaterials/deleteMaterial', this.materialData.id)
    },
    reset() {
      this.$store.dispatch('learningmaterials/resetLearningmaterial', { learningmaterialId: this.materialData.id })
    },
    update() {
      this.$store.dispatch('learningmaterials/loadLearningmaterial', { learningmaterialId: this.materialData.id })
    },
    duplicate() {
      axios.post(`/backend/api/v1/learningmaterials/${this.materialData.id}/clone`)
        .then((response) => {
          this.$router.push(`/learningmaterials/${this.materialData.learning_material_folder_id}/${response.data.learning_material_id}/general`)
        })
        .catch(() => {
          alert('Der Mediathek-Inhalt konnte nicht dupliziert werden. Bitte probieren Sie es später erneut.')
        })
    },
    moveFile(newFolderId) {
      if(!newFolderId) {
        return
      }
      return this.$store.dispatch('learningmaterials/saveLearningmaterial', {
        id: this.materialData.id,
        learning_material_folder_id: newFolderId,
      }).then(() => {
        this.$router.replace({
          name: 'learningmaterials.index',
          params: {
            folderId: newFolderId,
          },
        })
      })
    },
    async activateNotification() {
      await this.save()
      this.$store.dispatch('learningmaterials/activateNotification', {
        id: this.materialData.id,
      })
    }
  },
  components: {
    TagSelect,
    TranslatedFileSelector,
    SelectLocationModal,
    DeleteDialog,
    ReusableCloneWarning,
    SendNotification,
    UsageInfoAlert,
  },
}
</script>

<style lang="scss" scoped>
#app .s-visibilitySwitch {
  margin-top: 0;
  padding-top: 0;
}
</style>
