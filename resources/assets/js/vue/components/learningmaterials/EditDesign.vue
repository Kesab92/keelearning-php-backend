<template>
  <div v-if="materialData">
    <details-sidebar-toolbar>
      <template v-slot:default>
        <v-btn
          color="primary"
          @click="save"
          :loading="isSaving"
          :disabled="isSaving"
        >
          Speichern
        </v-btn>
      </template>
      <template v-slot:alerts>
        <reusable-clone-warning v-if="materialData.is_reusable_clone" />
        <UsageInfoAlert :material="materialData" />
      </template>
    </details-sidebar-toolbar>

    <div class="pa-4">
      <div class="subheading">Coverbild</div>
      <ImageUploader
        :current-image="materialData.cover_image_url"
        name="Coverbild"
        :url="`/backend/api/v1/learningmaterials/${materialData.id}/cover`"
        width="300px"
        height="180px"
        @newImage="handleNewImage"
      />
    </div>
  </div>
</template>

<script>
import ImageUploader from "../partials/global/ImageUploader"
import ReusableCloneWarning from "../partials/global/ReusableCloneWarning"
import UsageInfoAlert from "./UsageInfoAlert"

export default {
  props: ['material'],
  data() {
    return {
      materialData: null,
      isSaving: false,
    }
  },
  watch: {
    material: {
      handler() {
        if(!this.material) {
          return
        }
        this.materialData = JSON.parse(JSON.stringify(this.material))
      },
      immediate: true,
    },
  },
  methods: {
    handleNewImage(image) {
      this.materialData.cover_image_url = image
    },
    async save() {
      this.isSaving = true
      await this.$store.dispatch('learningmaterials/saveLearningmaterial', {
        id: this.materialData.id,
        cover_image_url: this.materialData.cover_image_url,
      })
      this.isSaving = false
    }
  },
  components: {
    ImageUploader,
    ReusableCloneWarning,
    UsageInfoAlert,
  },
}
</script>
