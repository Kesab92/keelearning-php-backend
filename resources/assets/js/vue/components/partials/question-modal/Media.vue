<template>
  <v-card-text>
    <vue-dropzone
      id="dropzone"
      ref="myVueDropzone"
      @vdropzone-file-added="setFile"
      :options="dropzoneOptions"
    />
  </v-card-text>
</template>

<script>
  import Vue2Dropzone from "vue2-dropzone"
  import "vue2-dropzone/dist/vue2Dropzone.min.css"

  export default {
    data: function () {
      return {
        dropzoneOptions: {
          url: "https://httpbin.org/post",
          thumbnailWidth: 150,
          maxFilesize: 10,
          autoProcessQueue: false,
          uploadMultiple: true,
          addRemoveLinks: true,
          dictDefaultMessage: "Hier ein Bild oder eine Audiodatei hineinziehen<br>(Max. Dateigröße 10MB)",
        },
      }
    },
    methods: {
      setFile(file) {
        this.$emit('fileSelected', file)
      }
    },
    components: {
      vueDropzone: Vue2Dropzone,
    },
  }
</script>