<template>
  <div>
    <template v-if="showCurrentImage">
      <v-layout
        column
        align-center>
        <img
          class="s-imageUpload__currentImage"
          :src="currentImage">
      </v-layout>
      <v-btn
        v-if="!readonly"
        flat
        block
        @click="currentImageHidden = true">
        Neues Icon wählen
      </v-btn>
    </template>
    <VueDropzone
      v-if="!showCurrentImage && !readonly"
      id="dropzone"
      :options="dropzoneOptions"
      @vdropzone-success="success"
      class="s-imageUpload__dropzone" />
  </div>
</template>

<script>
import Vue2Dropzone from "vue2-dropzone"
import '../../../../css/vendor/dropzone-vue.css'

export default {
  props: [
    'currentImage',
    'readonly',
    'test'
  ],
  data() {
    return {
      currentImageHidden: false,
    }
  },
  computed: {
    dropzoneOptions() {
      return {
        url: `/backend/api/v1/tests/${this.test.id}/icon`,
        thumbnailWidth: 150,
        maxFilesize: 5,
        autoProcessQueue: true,
        uploadMultiple: false,
        addRemoveLinks: true,
        dictDefaultMessage: `<strong>Icon</strong><br>(Max. Dateigröße 5MB)`,
        headers: {
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        },
      }
    },
    showCurrentImage() {
      return !!this.currentImage && !this.currentImageHidden
    },
  },
  methods: {
    success(file, response) {
      this.currentImageHidden = false
      this.$emit('newImage', response.image)
    },
  },
  components: {
    VueDropzone: Vue2Dropzone
  }
}
</script>

<style lang="scss" scoped>
#app .s-imageUpload__dropzone {
  margin: 0 !important;
}

.s-imageUpload__currentImage {
  width: 64px;
  height: 64px;
  object-fit: cover;
}
</style>
