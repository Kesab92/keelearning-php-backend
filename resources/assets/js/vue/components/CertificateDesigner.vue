<template>
  <div v-if="!canCreateCertificate && !isReadonly">
    <v-alert
      :value="true"
      outline
      type="info"
    >
      Bitte legen Sie das Zertifikat erst in der <a :href="`/setlang/${defaultLanguage}`">Primärsprache</a> an.
    </v-alert>
  </div>
  <div v-else-if="!certificateId && isReadonly">
    <v-alert
      :value="true"
      outline
      type="info"
    >
      Sie sind nicht berechtigt, ein Zertifikat anzulegen.
    </v-alert>
  </div>
  <div v-else>
    <v-toolbar
      color="white"
      v-if="!loading && image">

      <slot name="header" />

      <template v-if="!isReadonly">
        <v-btn
          color="primary"
          @click="addElement">
          <v-icon left>add</v-icon>
          Textbox einfügen
        </v-btn>
        <span
          class="add-element-info"
          v-if="!elements.length"><v-icon>arrow_back</v-icon> 2. Fügen Sie eine Textbox ein</span>

        <v-btn
          :disabled="!elements.length"
          color="success"
          @click="save(false)">
          Speichern
        </v-btn>
        <v-menu
          offset-y
          bottom
          right>
          <v-btn
            slot="activator"
            icon
          >
            <v-icon>more_vert</v-icon>
          </v-btn>
          <v-list>
            <v-list-tile
              @click="reset"
            >
              <v-list-tile-title>Zertifikat zurücksetzen</v-list-tile-title>
            </v-list-tile>
          </v-list>
        </v-menu>
      </template>
    </v-toolbar>

    <div
      class="progressbar-container"
      v-if="loading">
      <v-progress-circular indeterminate/>
    </div>
    <div
      v-show="!loading && !image"
      class="no-image-container">
      1. Bitte laden Sie ein Hintergrundbild hoch:<br>
      <v-btn
        color="primary"
        @click="pickFile">
        <v-icon left>cloud_upload</v-icon>
        Bild auswählen
        <input
          type="file"
          ref="image"
          accept="image/*"
          name="backgroundImage"
          @change="onFilePicked">
      </v-btn>
    </div>
    <div
      class="paper"
      v-if="!loading">
      <div class="content">
        <div
          class="editor-title"
          v-if="image"
        >Editor
        </div>
        <img
          v-if="image"
          :src="image"
          ref="backgroundImage"
          @load="imageLoaded"
          v-bind="sizeAttributes">
        <div
          class="placeholder-legend"
          v-if="isImageLoaded"
          :style="{width: this.$refs.backgroundImage.offsetWidth + 'px'}">
          Platzhalter:
          <ul>
            <li><strong>%submission_id% - Pflichtfeld: ID des Testergebnisses</strong></li>
            <li>%username% - Benutzername</li>
            <li>%firstname% - Vorname</li>
            <li>%lastname% - Nachname</li>
            <li>%realname_or_username%</li>
            <template v-if="type === 'course'">
              <li>%course_name% - Kursname</li>
              <li>%course_start_date% - Startdatum des Kurses</li>
              <li>%course_end_date% - Enddatum des Kurses</li>
              <li>%certificate_awarded_year% - Jahr in dem der Kurs bestanden wurde</li>
            </template>
            <template v-else-if="type === 'test'">
              <li v-if="showPassedPercentage">%passed_percentage% - Prozentzahl mit der der das Zertifikate erworben wurde</li>
              <li>%test_name% - Name des Tests</li>
            </template>
            <li>%passed_date% - Datum zu dem das Zertifikate erworben wurde</li>
            <li
              v-for="(metaField, key) in metaFields"
              :key="key"
            >
              %meta_{{ key }}% - {{ metaField.label }}
            </li>
          </ul>
        </div>
      </div>
      <div
        class="text-element-container"
        :class="{
          'no-pointer-events': isReadonly,
        }"
        :style="{
          width: this.$refs.backgroundImage.offsetWidth - 4 + 'px',
          height: this.$refs.backgroundImage.offsetHeight - 4 + 'px',
        }"
        v-if="isImageLoaded"
      >
        <VueDragResize
          v-for="element in elements"
          :key="element.id"
          :is-active="element.selected"
          :x="element.left"
          :y="element.top"
          :w="element.width"
          :h="element.height"
          :parent-limitation="true"
          :sticks="['br']"
          drag-handle=".dragHandle"
          drag-cancel=".element"
          @dragstop="dragstop($event, element)"
          @resizestop="resizestop($event, element)"
        >
          <v-icon class="dragHandle">drag_indicator</v-icon>
          <div
            class="element"
            style="height: 100%">
            <Editor
              :ref="'editor-' + element.id"
              :data-element-id="element.id"
              @onBlur="handleFocus(false, element)"
              @onFocus="handleFocus(true, element)"
              style="width: 100%; height: 100%;"
              :init="editorOptions"
              v-model="element.text"
              inline
            />
          </div>
        </VueDragResize>
      </div>
      <div
        class="preview"
        v-if="image && showPreview && elements.length">
        <div class="editor-title">Vorschau</div>
        <iframe
          v-bind="sizeAttributes"
          frameBorder="0"
          ref="preview"
          :src="previewUrl"/>
      </div>
    </div>
  </div>
</template>

<script>
  import Editor from "@tinymce/tinymce-vue"
  import VueDragResize from "vue-drag-resize"
  import {mapGetters} from "vuex"

  export default {
    props: {
      certificateId: {
        default: null,
        required: false,
        type: Number,
      },
      type: {
        type: String,
        required: true,
      },
      foreignId: {
        type: Number,
        required: true,
      },
      showPassedPercentage: {
        type: Boolean,
        required: true,
      },
      isReadonly: {
        type: Boolean,
        required: false,
        default: false,
      },
    },
    data() {
      let self = this
      return {
        image: null,
        elements: [],
        backgroundImageSize: null,
        showPreview: false,
        metaFields: {},
        editorOptions: {
          menubar: false,
          statusbar: false,
          relative_urls: false,
          contextmenu: false,
          language_url: '/js/langs/de.js',
          skin_url: '/js/skins/ui/oxide',
          external_plugins: {},
          plugins: [],
          fontsize_formats: "4pt 5pt 6pt 8pt 10pt 12pt 14pt 16pt 18pt 20pt 22pt 24pt 36pt",
          font_formats: 'Arial alt.=LiberationSans,Arial;Roboto=Roboto;Open Sans=Open Sans;Noto Sans=Noto Sans;Ubuntu=Ubuntu;Lora=Lora',
          toolbar1: 'undo redo | fontselect fontsizeselect styleselect',
          toolbar2: 'bold italic forecolor removeformat | alignleft aligncenter alignright alignjustify | bullist numlist | removeElement',
          setup: (editor) => {
            editor.ui.registry.addButton('removeElement', {
              text: 'Element löschen',
              onAction() {
                if (confirm('Möchten Sie diese Textbox löschen?')) {
                  let editorElement = document.querySelector('#' + editor.id + '[data-element-id]')
                  if (editorElement) {
                    if (self.elements) {
                      self.elements = self.elements.filter(item => item.id !== editorElement.getAttribute('data-element-id'))
                    }
                  }
                }
              },
            })
          },
        },
        loading: false,
        message: null,
        isImageLoaded: false,
      }
    },
    created() {
      this.loading = true
      let url = '/backend/api/v1/certificates/' + this.type
      if(this.certificateId) {
        url += '/' + this.certificateId
      }
      axios.get(url).then(response => {
        this.updateResponse(response.data)
      }).catch(() => {
        alert('Die Primärdaten für das Zertifikat konnten leider nicht abgerufen werden. Bitte versuchen Sie es später erneut.')
      }).finally(() => {
        this.loading = false
      })
    },
    methods: {
      onFilePicked(e) {
        this.isImageLoaded = false
        let files = e.target.files
        if (files) {
          let fileReader = new FileReader()
          fileReader.onload = (e) => {
            this.image = e.target.result
          }
          fileReader.readAsDataURL(files[0])
        }
      },
      reset() {
        if (!confirm('Dadurch wird das Hintergrundbild und alle Texte entfernt. Fortfahren?')) {
          return
        }
        this.image = null
        this.elements = []
        this.isImageLoaded = false
        this.$refs.image.value = ''
        this.showPreview = false
        if (this.certificateId) {
          this.save(true)
        }
      },
      pickFile() {
        this.$refs.image.click()
      },
      imageLoaded() {
        this.isImageLoaded = true
      },
      addElement() {
        this.elements.push({
          id: 'el-' + (new Date()).getTime(),
          left: 10,
          top: 10,
          width: 400,
          height: 200,
          isActive: false,
          text: 'Hier den gewünschten Text eingeben',
        })
        this.isImageLoaded = false
        this.$nextTick(() => {
          this.isImageLoaded = true
        })
      },
      save(reset) {
        let url = '/backend/api/v1/certificates/' + this.type
        if(this.certificateId) {
          url += '/' + this.certificateId
        }

        const formData = new FormData()

        if (reset) {
          formData.append('reset', true)
        } else {
          if (this.$refs.image.files && this.$refs.image.files.length > 0) {
            formData.append('background_image', this.$refs.image.files[0], this.$refs.image.files[0].name)
          }
          let elements = null
          let hasSubmissionId = false
          if (this.elements) {
            elements = JSON.stringify(this.elements.filter(item => item.text && item.text.length > 0))
            hasSubmissionId = typeof this.elements.find(item => item.text && item.text.indexOf('%submission_id%') !== -1) !== 'undefined'
          }
          if(!hasSubmissionId) {
            this.showSnackbar('Bitte fügen Sie den Platzhalter %submission_id% ein.', false)
            return false
          }
          formData.append('background_image_size', JSON.stringify({
            width: this.$refs.backgroundImage.width + 4,
            height: this.$refs.backgroundImage.height + 4,
          }))
          formData.append('elements', elements)
          formData.append('foreign_id', this.foreignId)
        }

        axios.post(url, formData).then(response => {
          if (response.data.success) {
            this.updateCertificateData(response.data.certificate)
            this.$emit('saved', response.data.certificate)
            this.showSnackbar('Das Zertifikat wurde erfolgreich gespeichert.', true)
            if (!reset) {
              this.showPreview = true
            }
            if (typeof this.$refs.preview !== 'undefined') {
              this.$refs.preview.contentWindow.location.reload()
            }
          }
        }).catch(error => {
          this.showSnackbar('Es ist ein Fehler beim Speichern aufgetreten.', false)
        })
      },
      updateResponse(data) {
        this.metaFields = data.metaFields
        let certificate = data.certificate
        if(certificate) {
          this.updateCertificateData(certificate)
        }
        if (this.elements && this.image) {
          this.showPreview = true
        }
      },
      updateCertificateData(certificate) {
        this.elements = certificate.elements ? JSON.parse(certificate.elements) : []
        this.image = certificate.background_image_url
        this.certificateId = certificate.id
        this.metaKey = certificate.metaFields
        this.backgroundImageSize = certificate.background_image_size ? JSON.parse(certificate.background_image_size) : null
      },
      showSnackbar(message, success) {
        this.$store.dispatch('snackbar/showMessage', {
          color: success ? 'success' : 'error',
          message,
        })
      },
      handleFocus(isSelected, element) {
        this.$set(element, 'selected', isSelected)
      },
      dragstop(data, element) {
        this.$refs['editor-' + element.id][0].editor.render()
        this.$set(element, 'left', data.left)
        this.$set(element, 'top', data.top)
      },
      resizestop(data, element) {
        this.$set(element, 'width', data.width)
        this.$set(element, 'height', data.height)
      },
    },
    computed: {
      ...mapGetters({
        activeLanguage: 'languages/activeLanguage',
        defaultLanguage: 'languages/defaultLanguage',
      }),
      canCreateCertificate() {
        if(this.certificateId) {
          return true
        }
        return this.activeLanguage === this.defaultLanguage
      },
      sizeAttributes() {
        if (!this.backgroundImageSize) {
          return {}
        }
        return {
          width: this.backgroundImageSize.width + 'px',
          height: this.backgroundImageSize.height + 'px',
        }
      },
      previewUrl() {
        if(this.type === 'test') {
          return '/tests/' + this.foreignId + '/certificates/preview#toolbar=0&navpanes=0'
        } else if (this.type === 'course') {
          return '/courses/certificates/' + this.foreignId + '/preview#toolbar=0&navpanes=0'
        }
      }
    },
    components: {
      VueDragResize,
      Editor,
    },
  }
</script>

<style lang="scss">
  .mce-btn-group:not(:first-child) {
    border-left: #212121;
  }

  #app .vdr.active .dragHandle {
    display: block;
  }

  #app .mce-content-body {
    font-family: Arial !important;
    p {
      margin: 0;
      line-height: 1.2;
    }
  }
</style>

<style lang="scss" scoped>
  #app {
    input[type="file"] {
      display: none;
    }

    .paper {
      overflow: auto;
      position: relative;
      margin-top: 20px;
      display: inline-block;

      .content {
        padding: 0 0 20px 0;
        text-align: left;
        display: inline-block;
        margin-right: 30px;
        vertical-align: top;

        img {
          max-height: 768px;
          max-width: 1024px;
          border: 2px solid #2a7acc;
        }
      }

      .text-element-container {
        top: 30px;
        left: 2px;
        right: 35px;
        bottom: 76px;
        position: absolute;
      }
    }

    .dragHandle {
      position: absolute;
      top: 0px;
      cursor: move;
      right: -25px;
      width: 25px;
      height: 25px;
      background: #e8e8e8;;
      display: none;

    }

    .progressbar-container {
      width: 100%;
      padding: 20px;
      text-align: center;
    }

    .no-image-container {
      text-align: center;
      padding-top: 100px;
      background: #e2e2e2;
      padding-bottom: 100px;
    }

    .add-element-info .v-icon {
      vertical-align: -7px;
    }

    .preview {
      display: inline-block;
    }

    .editor-title {
      background: #2a7acc;
      width: 100%;
      padding: 0 7px;
      color: white;
      font-size: 18px;
      line-height: 30px;
      height: 28px;
    }

    .placeholder-legend {
      background: #2a7acc;
      padding: 10px 10px;
      color: white;
      font-size: 18px;
      line-height: 25px;
      margin-top: -6px;
    }
  }
</style>
