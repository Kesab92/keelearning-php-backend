<template>
  <div>
    <v-layout
      row
      class="mb-2">
      <v-flex
        v-if="superadminOnly"
        shrink>
        <v-tooltip bottom>
          <v-icon slot="activator">admin_panel_settings</v-icon>
          Nur für Superadmins
        </v-tooltip>
      </v-flex>
      <v-flex align-self-center>
        <div class="body-2">{{ label }}</div>
      </v-flex>
    </v-layout>
    <template v-if="showCurrentImage">
      <img
        class="s-imageUpload__currentImage"
        :style="imageStyle"
        :src="currentImage"><br>
      <v-layout row>
        <v-flex shrink>
          <v-btn
            class="mx-0"
            @click="currentImageHidden = true">Bild ersetzen</v-btn>
        </v-flex>
        <v-flex
          v-if="deleteable"
          shrink>
          <v-btn
            @click="resetImage"
            icon>
            <v-icon>delete</v-icon>
          </v-btn>
        </v-flex>
      </v-layout>
    </template>
    <VueDropzone
      v-if="!showCurrentImage"
      :id="`dropzone-${setting}`"
      :options="dropzoneOptions"
      @vdropzone-success="success"
      class="s-imageUpload__dropzone" />
  </div>
</template>

<script>
  import Vue2Dropzone from "vue2-dropzone"
  import '../../../../../css/vendor/dropzone-vue.css'

  export default {
    props: [
      'setting',
      'settings',
      'label',
      'description',
      'profileId',
      'maxWidth',
      'imageBackground',
      'isCandy',
      'height',
      'deleteable',
    ],
    data() {
      return {
        currentImageHidden: false,
      }
    },
    computed: {
      dropzoneOptions() {
        return {
          url: `/backend/api/v1/settings/profile/${this.profileId}/image/${this.setting}`,
          thumbnailWidth: 150,
          maxFilesize: 20,
          autoProcessQueue: true,
          uploadMultiple: false,
          addRemoveLinks: true,
          dictDefaultMessage: `<strong>${this.label}</strong><br>${this.description}<br>(Max. Dateigröße 20MB)`,
          headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          },
        }
      },
      imageStyle() {
        let style = {}
        if(this.maxWidth) {
          style.maxWidth = this.maxWidth + 'px'
        }
        if(this.imageBackground) {
          style.background = this.imageBackground
          style.padding = '10px'
        }
        if(this.height) {
          style.height = this.height + 'px'
        }
        return style
      },
      currentImage() {
        return this.settings[this.setting].value
      },
      showCurrentImage() {
        return !!this.currentImage && !this.currentImageHidden
      },
      superadminOnly() {
        const oldSuperadminSettings = [
          'app_icon',
          'app_icon_no_transparency',
          'app_logo',
          'app_logo_inverse',
          'auth_background_image',
        ]
        if(!this.isCandy && oldSuperadminSettings.includes(this.setting)) {
          return true
        }
        return this.settings[this.setting].superadmin
      },
    },
    methods: {
      success(file, response) {
        this.currentImageHidden = false
        this.$emit('setSetting', {
          type: 'profileSetting',
          setting: this.setting,
          value: response.image,
        })
      },
      resetImage() {
        if(!confirm('Möchten Sie dieses Bild entfernen?')) {
          return
        }
        this.$emit('updateSetting', {
          type: 'profileSetting',
          setting: this.setting,
          value: '',
        })
      },
    },
    components: {
      VueDropzone: Vue2Dropzone
    }
  }
</script>

<style lang="scss" scoped>
  #app .s-imageUpload__dropzone {
    margin-left: 0 !important;
  }

  .s-imageUpload__currentImage {
    max-width: calc(100% - 20px);
    object-fit: contain;
  }
</style>
