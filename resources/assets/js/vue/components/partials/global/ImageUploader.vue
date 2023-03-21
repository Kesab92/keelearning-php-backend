<template>
  <div :style="wrapperStyle">
    <template v-if="showCurrentImage">
      <img
        class="s-imageUpload__currentImage"
        :style="imageStyle"
        :src="currentImage">
      <template v-if="!isReadOnly">
        <br>
        <v-btn
          flat
          block
          @click="currentImageHidden = true">
          Neues Bild wählen
        </v-btn>
      </template>
    </template>
    <VueDropzone
      v-if="!showCurrentImage && !isReadOnly"
      id="dropzone"
      :options="dropzoneOptions"
      @vdropzone-success="success"
      class="s-imageUpload__dropzone" />
  </div>
</template>

<script>
  import Vue2Dropzone from "vue2-dropzone"
  import '../../../../../css/vendor/dropzone-vue.css'

  export default {
    props: {
      currentImage: {
        type: String|null,
        required: true,
      },
      url: {
        type: String,
        required: true,
      },
      name: {
        type: String,
        required: false,
      },
      width: {
        type: String,
        required: false,
      },
      height: {
        type: String,
        required: false,
      },
      maxHeight: {
        type: String,
        required: false,
      },
      description: {
        type: String,
        required: false,
      },
      validateFile: {
        type: Function,
        required: false,
      },
      isReadOnly: {
        type: Boolean,
        required: false,
        default: false,
      },
    },
    data() {
      return {
        currentImageHidden: false,
      }
    },
    computed: {
      dropzoneOptions() {
        let that = this
        return {
          url: this.url,
          thumbnailWidth: 150,
          maxFilesize: 20,
          autoProcessQueue: true,
          uploadMultiple: false,
          addRemoveLinks: true,
          acceptedFiles: 'image/*',
          accept(file, done) {
            if(that.validateFile) {
              that.validateFile(file, done)
            } else {
              done()
            }
          },
          dictDefaultMessage: `<strong>${this.name}</strong>${this.description || ''}<br>Max. Dateigröße 20MB`,
          headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          },
        }
      },
      showCurrentImage() {
        return !!this.currentImage && !this.currentImageHidden
      },
      wrapperStyle() {
        return {
          width: this.width,
        }
      },
      imageStyle() {
        return {
          maxWidth: '100%',
          height: this.height,
          maxHeight: this.maxHeight,
        }
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
