<template>
  <div>
    <translated-input
      :translation-contains-value="checkIfTranslationHasFile"
      :translations="materialData.translations"
      attribute="file_type"
      input-type="custom"
      label="Datei-Inhalt"
      @forceTranslation="clearModel">
      <template slot="default-translation">
        <template v-if="hasOriginalContent">
          <Preview
            :file-type="getFileType(materialData)"
            :translation="materialData"
            class="mt-1"/>
          <v-checkbox
            v-if="getFileType(materialData) === 'audio' || materialData.file_type === 'application/pdf'"
            v-model="materialData.download_disabled"
            disabled
            hide-details
            label="Download deaktivieren"/>
          <template v-if="materialData.file_type === 'azure_video'">
            <Toggle
              v-model="materialData.show_watermark"
              disabled
              label="Realname & ID im Player anzeigen"
              hint="Wenn diese Option aktiv ist, wird Realname und ID des Benutzers über das Video gelegt." />
            <v-select
              v-model="materialData.subtitles_language"
              disabled
              label="Untertitel"
              hint="Wählen Sie die Sprache des Videos aus, das Sie hier hochgeladen haben"
              persistent-hint
              :items="$constants.SUBTITLES.LANGUAGES"
              outline />
          </template>
        </template>
        <div
          v-else
          class="grey--text">n/a
        </div>
      </template>
      <template slot="override-translation">
        <v-layout row>
          <v-flex grow>
            <div class="subheading">Datei-Inhalt</div>
          </v-flex>
          <v-flex shrink>
            <v-tooltip
              left
              max-width="300px">
              <div
                slot="activator"
                style="cursor: help">
                <v-icon>info</v-icon>
              </div>
              <div>
                Die maximale Dateigröße beträgt 1GB.<br>
                Es können neben xAPI WBTs (als zip) auch die gängigen Office-, Bild-, Video-
                und Audioformate hochgeladen werden:<br>
                <ul>
                  <li>
                    Audio: MP3
                  </li>
                  <li>
                    Bilder: JPG, PNG, GIF, SVG
                  </li>
                  <li>
                    Word-Dateien: DOC, DOT, DOCX …
                  </li>
                  <li>
                    Excel-Dateien: XLS, XLSX, XLA …
                  </li>
                  <li>
                    Präsentationen: PPT, PPTX, POT …
                  </li>
                  <li>
                    Videos: alle gängigen Videoformate
                  </li>
                </ul>
              </div>
            </v-tooltip>
          </v-flex>
        </v-layout>
        <FileSelector
          v-model="materialData"
          :upload-url="uploadUrl"
          ref="fileSelector"
          @reset="handleReset"
          @update="handleUpdate"/>
      </template>
    </translated-input>
  </div>
</template>

<script>
import {mapGetters} from "vuex"
import FileSelector from "./FileSelector"
import Preview from "./preview/Preview"
import helpers from "../../../logic/helpers"

export default {
  props: ["value", "uploadUrl"],
  data() {
    return {
      forceTranslation: false,
    }
  },
  computed: {
    ...mapGetters({
      activeLanguage: "languages/activeLanguage",
      defaultLanguage: "languages/defaultLanguage",
    }),
    materialData: {
      get() {
        return this.value
      },
      set(data) {
        this.$emit("input", data)
      },
    },
    hasOriginalContent() {
      return this.materialData.file || this.materialData.file_url || this.materialData.link
    },
  },
  methods: {
    getFileType(data) {
      return helpers.getLearningmaterialFileType(data)
    },
    handleUpdate(data) {
      this.$emit("update", data)
    },
    handleReset(data) {
      this.$emit("reset", data)
    },
    clearModel() {
      let data = JSON.parse(JSON.stringify(this.materialData))
      data.file_type = null
      data.file_url = null
      data.file = null
      data.link = null
      data.download_disabled = false
      data.show_watermark = false
      this.materialData = data
    },
    checkIfTranslationHasFile(translation) {
      return translation.file || translation.file_url || translation.link
    },
    refresh() {
      if (this.$refs.fileSelector) {
        this.$refs.fileSelector.refresh()
      }
    },
  },
  components: {
    Preview,
    FileSelector,
  },
}
</script>
