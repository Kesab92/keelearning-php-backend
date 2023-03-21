<template>
  <div>
    <template v-if="showCurrentImage">
      <img
        class="s-imageUpload__currentImage"
        :src="currentImage" ><br>
      <v-btn
        v-if="!readonly"
        flat
        block
        @click="currentImageHidden = true">Neues Bild wählen</v-btn>
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
    'test',
  ],
  data() {
    return {
      currentImageHidden: false,
    }
  },
  computed: {
    dropzoneOptions() {
      return {
        url: `/backend/api/v1/tests/${this.test.id}/cover`,
        thumbnailWidth: 150,
        maxFilesize: 20,
        autoProcessQueue: true,
        uploadMultiple: false,
        addRemoveLinks: true,
        dictDefaultMessage: `<strong>Coverbild</strong><br>(Max. Dateigröße 20MB)`,
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
  width: 100%;
  max-height: 168px;
  object-fit: cover;
}
</style>
