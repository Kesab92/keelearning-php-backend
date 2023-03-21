<template>
  <div>
    <FileSelectorFiletype
      v-model="selectedType"
      :enable-youtube="this.enableYoutube"
      :enable-wbt="this.enableWbt"
      :enable-link="this.enableLink"
      :enable-file="this.enableFile"
      @click="handleFileTypeClick" />
    <div v-show="selectedType === 'file' || selectedType === 'wbt'">
      <template v-if="!media.url && media.type !== 'azure_video'">
        <FilePond
          name="file"
          ref="pond"
          :label-idle="filepondLabel"
          :allow-multiple="false"
          :allow-revert="false"
          :allow-remove="false"
          :allow-process="false"
          :drop-on-page="true"
          :accepted-file-types="acceptedFileTypes"
          :file-validate-type-detect-type="detectFileType"
          :files="files"
          @init="handleFilePondInit" />
        <div
          v-if="selectedType === 'wbt'"
          class="grey--text text-xs-right text--darken-2"
          style="margin-top: -10px">
          Wir unterstützen nur gezippte xAPI-Dateien (mehr Infos finden Sie im
          <a
            href="https://helpdesk.keelearning.de/de/articles/4233320-wbt-web-based-training"
            target="_blank">
            Helpdesk
          </a>
          ).
        </div>
      </template>
      <div v-else>
        <Preview
          :attachment="media"
          :file-type="media.type" />
        <v-checkbox
          v-if="enableDownloadDisabling && (media.type === 'audio' || media.type === 'pdf')"
          v-model="media.download_disabled"
          hide-details
          label="Download deaktivieren"/>
        <Toggle
          v-if="enableWatermark && media.type === 'azure_video'"
          v-model="media.show_watermark"
          label="Realname & ID im Player anzeigen"
          hint="Blendet den Realnamen und die ID des Benutzer über dem Video ein." />
      </div>
    </div>
    <FileSelectorYouTube
      v-if="selectedType === 'youtube'"
      v-model="media" />
    <FileSelectorLink
      v-if="selectedType === 'link'"
      v-model="media" />
    <div v-if="selectedType && (media.file || media.link)">
      <v-btn
        class="mx-0"
        color="secondary"
        outline
        hide-details
        @click="reset">
        Inhalt zurücksetzen
      </v-btn>
    </div>
  </div>
</template>

<script>
import * as FilePondBase from 'filepond'
import vueFilePond from 'vue-filepond'
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size'
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type'
FilePondBase.registerPlugin(FilePondPluginFileValidateType)
FilePondBase.registerPlugin(FilePondPluginFileValidateSize)
const FilePond = vueFilePond()

import FileSelectorFiletype from "./FileSelectorFiletype"
import Helpers from '../../../../logic/helpers.js'
import FileSelectorYouTube from "./FileSelectorYouTube"
import FileSelectorLink from "./FileSelectorLink"
import Preview from "./preview/Preview"

export default {
  props: {
    value: {
      type: Object,
    },
    uploadUrl: {
      type: String,
    },
    enableLink: {
      type: Boolean,
      default: true,
    },
    enableWbt: {
      type: Boolean,
      default: true,
    },
    enableYoutube: {
      type: Boolean,
      default: true,
    },
    enableFile: {
      type: Boolean,
      default: true,
    },
    enableDownloadDisabling: {
      type: Boolean,
      default: false,
    },
    enableWatermark: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      selectedType: null,
      files: [],
    }
  },
  computed: {
    media: {
      get() {
        return this.value
      },
      set(data) {
        this.$emit('input', data)
      },
    },
    acceptedFileTypes() {
      if(this.enableFile) {
        return [
          'image/jpeg',
          'image/png',
          'image/gif',
          'image/svg+xml',
          'audio/mpeg',
          'audio/mp3',
          'application/pdf',
          'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
          'application/vnd.openxmlformats-officedocument.presentationml.presentation',
          'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
          'application/msword',
          'application/mspowerpoint',
          'application/msexcel',
          'application/vnd.ms-office',
          'application/vnd.ms-powerpoint',
          'application/vnd.ms-excel',
          'application/octet-stream',
          'application/cdfv2',
          'audio/x-musepack',
          'video/*',
        ]
      }

      if(this.enableWbt && this.selectedType === 'wbt') {
        return [
          'application/zip',
          'application/x-zip-compressed',
        ]
      }

      return []
    },
    filepondLabel() {
      if(this.selectedType === 'wbt') {
        return 'WBT als zip hier ablegen'
      }
      return 'Datei hier ablegen'
    },
  },
  watch: {
    attachment: {
      handler() {
        if(this.media.link) {
          if(Helpers.isYouTubeURL(this.media.link)) {
            this.selectedType = 'youtube'
          } else {
            this.selectedType = 'link'
          }
        } else if(this.media.type) {
          this.selectedType = 'file'
        } else {
          this.selectedType = null
        }
      },
      immediate: true,
    },
    selectedType() {
      this.media.type = this.selectedType
    }
  },
  methods: {
    handleFileTypeClick(type) {
      if(type === 'file' || type === 'wbt') {
        this.$refs.pond.browse()
      }
    },
    handleFilePondInit() {
      this.$refs.pond._pond.setOptions({
        maxFileSize: '1024MB',
        labelFileTypeNotAllowed: 'Dieser Dateityp ist leider nicht gültig',
        fileValidateTypeLabelExpectedTypes: 'Nur Bilder, Videos, WBTs, Audio Dateien und Dokumente sind erlaubt',
        labelMaxFileSizeExceeded: 'Diese Datei ist leider zu groß',
        labelMaxFileSize: 'Die Maximale Dateigröße ist {filesize}',
        instantUpload: true,
        labelFileProcessing: 'Wird hochgeladen',
        labelFileProcessingComplete: 'Erfolgreich hochgeladen',
        labelFileProcessingAborted: 'Abgebrochen',
        labelFileProcessingError: 'Fehler beim Hochladen',
        chunkSize: 20 * 1024 * 1024,
        chunkUploads: true,
        chunkRetryDelays: [500, 1000, 3000, 10000, 10000, 10000, 10000, 10000, 10000, 10000, 10000, 10000],
        server: {
          url: '/backend/api/v1/filepond/api',
          process: '/process',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          },
        },
      })
      const pondRoot = document.querySelector('.filepond--root')
      pondRoot.addEventListener('FilePond:processfile', e => {
        if(e.detail.error) {
          return
        }
        axios.post(this.uploadUrl, {
          serverId: e.detail.file.serverId,
          filename: e.detail.file.filename,
          fileType: e.detail.file.fileType,
          fileExtension: e.detail.file.fileExtension,
        }).then(() => {
          try {
            // We need this try catch in case the user already closed the window
            this.$refs.pond._pond.removeFiles()
            this.$emit('update')
          } catch (e) {
            console.log(e)
          }
        }).catch((e) => {
          alert('Die Datei konnte leider nicht verarbeitet werden. Bitte probieren Sie es später erneut.')
          this.$refs.pond._pond.removeFiles()
        })
      })
    },
    reset() {
      if(!confirm('Sind Sie sicher, dass sie den Inhalt dieser Datei löschen möchten?')) {
        return
      }
      this.$emit('reset')
    },
    detectFileType(file, type) {
      // only do custom detection if browser fails
      if (type && type.length) {
        return new Promise(resolve => resolve(type))
      }
      return new Promise((resolve, reject) => {
        const extension = file.name.split('.').pop()
        switch (extension) {
          case 'mpp':
            resolve('application/vnd.ms-office')
            return
        }
        reject('unknown file type')
      })
    },
  },
  components: {
    Preview,
    FileSelectorLink,
    FileSelectorYouTube,
    FileSelectorFiletype,
    FilePond,
  },
}
</script>
