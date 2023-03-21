<template>
  <div>
    <FileSelectorFiletype
      v-model="selectedType"
      @click="handleFileTypeClick" />
    <div v-show="selectedType === 'file' || selectedType === 'wbt'">
      <template v-if="!materialData.file_url && materialData.file_type !== 'azure_video'">
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
          Wir unterstützen nur gezippte xAPI<span v-if="appSettings.scorm_wbts_enabled === '1'">- und SCORM</span>-Dateien (mehr Infos finden Sie im
          <a
            href="https://helpdesk.keelearning.de/de/articles/4233320-wbt-web-based-training"
            target="_blank">
            Helpdesk
          </a>).
        </div>
      </template>
      <div v-else>
        <Preview
          :translation="materialData"
          :file-type="getFileType(materialData)"
          ref="preview" />
        <v-checkbox
          v-if="getFileType(materialData) === 'audio' || materialData.file_type === 'application/pdf'"
          v-model="materialData.download_disabled"
          hide-details
          label="Download deaktivieren"/>
        <template v-if="materialData.file_type === 'azure_video'">
          <Toggle
            v-model="materialData.show_watermark"
            label="Realname & ID im Player anzeigen"
            hint="Blendet den Realnamen und die ID des Benutzer über dem Video ein." />
          <v-select
            v-model="materialData.subtitles_language"
            label="Untertitel"
            hint="Wählen Sie die Sprache des Videos aus, das Sie hier hochgeladen haben"
            persistent-hint
            :items="$constants.SUBTITLES.LANGUAGES"
            outline />
        </template>

        <div v-if="materialData.file_type === 'wbt' && appSettings.scorm_wbts_enabled === '1'">
          <v-select
            outline
            class="my-2"
            v-model="materialData.wbt_subtype"
            :items="wbtSubtypes"
            hide-details
            label="WBT Typ" />
          <v-alert
            v-if="materialData.wbt_subtype === wbtScormSubtype"
            type="info"
            outline
            class="mb-4"
            :value="true">
            Aktuell erfassen wir keine Statistiken für Web-based Trainings im SCORM-Format. Durch das Einbinden des SCORM-WBTs in einen Kurs, können Sie über die Kursstatistiken den Lernerfolg abrufen.<br>
            Wenn Sie die Events innerhalb Ihres WBTs tracken möchten, laden Sie dieses im modernen xAPI-Format hoch.
          </v-alert>
        </div>
      </div>
    </div>
    <FileSelectorYouTube
      v-if="selectedType === 'youtube'"
      :material-data="materialData" />
    <FileSelectorLink
      v-if="selectedType === 'link'"
      :material-data="materialData" />
    <div v-if="selectedType && (materialData.file || materialData.link)">
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
import Helpers from '../../../logic/helpers.js'
import FileSelectorYouTube from "./FileSelectorYouTube"
import FileSelectorLink from "./FileSelectorLink"
import Preview from "./preview/Preview"
import helpers from "../../../logic/helpers.js"
import constants from "../../../logic/constants.js"
import {mapGetters} from "vuex"

export default {
  props: ['value', 'uploadUrl'],
  data() {
    return {
      files: [],
      selectedType: null,
    }
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
    }),
    materialData: {
      get() {
        return this.value
      },
      set(data) {
        this.$emit('input', data)
      },
    },
    acceptedFileTypes() {
      let acceptedFileTypes = [
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
        'application/mmap',
        'audio/x-musepack',
        'application/zip',
        'application/x-zip-compressed',
        'video/*',
      ]
      if(this.selectedType === 'wbt') {
        acceptedFileTypes = [
          'application/zip',
          'application/x-zip-compressed',
        ]
      }

      return acceptedFileTypes
    },
    filepondLabel() {
      if(this.selectedType === 'wbt') {
        return 'WBT als zip hier ablegen'
      }
      return 'Datei hier ablegen'
    },
    wbtSubtypes() {
      return [
        {
          text: 'xAPI',
          value: constants.MEDIALIBRARY.WBT_SUBTYPES.XAPI,
        },
        {
          text: 'SCORM',
          value: constants.MEDIALIBRARY.WBT_SUBTYPES.SCORM,
        },
      ]
    },
    wbtScormSubtype() {
      return constants.MEDIALIBRARY.WBT_SUBTYPES.SCORM
    }
  },
  watch: {
    materialData: {
      handler() {
        if(this.materialData.link) {
          if(Helpers.isYouTubeURL(this.materialData.link)) {
            this.selectedType = 'youtube'
          } else {
            this.selectedType = 'link'
          }
        } else if(this.materialData.file_type) {
          this.selectedType = 'file'
        } else {
          this.selectedType = null
        }
      },
      immediate: true,
    },
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
        let fileType = e.detail.file.fileType

        // .mmap doesn't contain the MIME type, so it has to be overridden
        if(!e.detail.file.fileType && e.detail.file.fileExtension === 'mmap') {
          fileType = 'application/mmap'
        }

        if(e.detail.error) {
          return
        }
        axios.post(this.uploadUrl, {
          serverId: e.detail.file.serverId,
          filename: e.detail.file.filename,
          fileType: fileType,
          fileExtension: e.detail.file.fileExtension,
        }).then(() => {
          try {
            // We need this try catch in case the user already closed the window
            this.$refs.pond._pond.removeFiles()
            this.$emit('update')
          } catch (e) {}
        }).catch((e) => {
          alert('Die Datei konnte leider nicht verarbeitet werden. Bitte probieren Sie es später erneut.')
          this.$refs.pond._pond.removeFiles()
        })
      })
    },
    getFileType(data) {
      return helpers.getLearningmaterialFileType(data)
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
          case 'mmap':
            resolve('application/mmap')
            return
        }
        reject('unknown file type')
      })
    },
    refresh() {
      if (this.$refs.preview) {
        this.$refs.preview.refresh()
      }
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
