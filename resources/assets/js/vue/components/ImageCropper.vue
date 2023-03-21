<template>
  <div class="wrap">
    <template v-if="!selectedImage">
      <label
        class="ui primary button"
        for="image-cropper-upload"
      >
        Bild wählen
      </label>
      <input
        type="file"
        id="image-cropper-upload"
        accept="image/png, image/jpeg, image/gif, image/jpg"
        @change="selectImageFile($event, 1)"
      >
    </template>
    <template v-else>
      <cropper
        ref="cropper"
        :src="this.selectedImage"
        :defaultSize="defaultSize"
        :stencilProps="{
                  aspectRatio: 300/180
                }"
        @change="lastImageState = $event"
      />
      <button
        class="ui green button save-button"
        @click="saveImage"
      >
        Speichern
      </button>
    </template>
  </div>
</template>

<script>
import { Cropper }  from 'vue-advanced-cropper'

export default {
  props: [
    'target',
  ],
  data() {
    return {
      selectedFile: null,
      selectedImage: null,
      lastImageState: null,
    }
  },
  methods: {
    selectImageFile(event) {
      let file = event.target.files[0]
      if (!/\.(gif|jpg|jpeg|png|bmp|GIF|JPG|PNG)$/.test(event.target.value)) {
        alert('Dieses Bildformat wird nicht unterstützt.')
        return
      }
      let reader = new FileReader()
      reader.onload = readerEvent => {
        if (typeof readerEvent.target.result === "object") {
          this.selectedImage = window.URL.createObjectURL(new Blob([readerEvent.target.result]))
        } else {
          this.selectedImage = readerEvent.target.result;
        }
        this.selectedFile = file
      };
      reader.readAsArrayBuffer(file)
    },
    saveImage() {
      const {canvas} = this.$refs.cropper.getResult()
      this.convertFromDataURItoBlob(canvas.toDataURL())
        .then((blob) => {
          let data = new FormData()
          data.append('file', blob(), this.selectedFile.name)

          window.axios.post(this.target, data, {
            headers: {
              'Content-Type': `multipart/form-data; boundary=${data._boundary}`,
            },
            timeout: 30000,
          }).then(() => {
            window.location.reload()
          })
        })
    },
    convertFromDataURItoBlob(data) {
      return Promise.resolve(() => {
        // convert base64 to raw binary data held in a string
        // doesn't handle URLEncoded DataURIs - see SO answer #6850276 for code that does this
        let byteString = atob(data.split(',')[1]);

        // separate out the mime component
        let mimeString = data.split(',')[0].split(':')[1].split(';')[0];

        // write the bytes of the string to an ArrayBuffer
        let ab = new ArrayBuffer(byteString.length);
        let ia = new Uint8Array(ab);
        for (let i = 0; i < byteString.length; i++) {
          ia[i] = byteString.charCodeAt(i);
        }

        //New Code
        return new Blob([ab], {type: mimeString});
      })
    },
    defaultSize(cropper, image, restrictions, imageWidth, imageHeight, props) {
      return {
        width: image.width,
        height: image.height
      }
    }
  },
  components: {
    Cropper,
  },
}
</script>

<style scoped lang="scss">
.wrap {
  height: 600px;
  padding: 1rem;
  padding-bottom: 65px;
  position: relative;
}

.save-button {
  position: absolute;
  bottom: 1rem;
  right: 1rem;
}

#image-cropper-upload {
  clip: rect(0 0 0 0);
  position: absolute;
}
</style>

<style lang="scss">
.vue-square-handler {
  background: red;
}
</style>
