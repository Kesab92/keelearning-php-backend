<template>
  <div>
    <input
      type="file"
      ref="uploader"
      accept=".xls, .xlsx"
      @change="handleFileSelect">

    <v-dialog
      v-model="isOpen"
      persistent
      width="400"
    >
      <v-card>
        <v-card-title class="headline grey lighten-2">
          Fragen Import
        </v-card-title>
        <template v-if="!fileSelected">
          <v-card-text>
            Wählen Sie aus, für welche Sprache bestehende Fragen importiert werden sollen:

            <v-select
              v-model="importLanguage"
              :items="availableLanguages"
              class="mt-2"
              box
              label="Import Sprache" />
          </v-card-text>
          <v-card-actions>
            <v-btn
              color="primary"
              :disabled="!this.importLanguage"
              @click="uploadPrompt">
              <v-icon left dark>cloud_upload</v-icon>
              Datei wählen
            </v-btn>
            <v-spacer />
            <v-btn @click="isOpen = false">Schließen</v-btn>
          </v-card-actions>
        </template>

        <template v-else-if="!fileChecked">
          <v-card-text v-if="!checkError && !checkSuccess">
            Bitte haben Sie einen Moment Geduld, die Daten werden geprüft.
            <v-progress-linear
              indeterminate
              color="primary"
              class="mb-0" />
          </v-card-text>

          <v-card-text v-if="checkError">
            <v-layout
              class="mb-3"
              row>
              <v-flex shrink class="mr-2"><v-icon large color="red">error</v-icon></v-flex>
              <v-flex>
                <strong>Der Import ist fehlerhaft</strong><br>
                {{ checkError }}
              </v-flex>
            </v-layout>

            Neue Sprache wählen:
            <v-select
              v-model="importLanguage"
              :items="availableLanguages"
              class="mt-2"
              box
              label="Import Sprache" />
          </v-card-text>

          <v-card-text v-if="checkSuccess">
            Der Import wurde geprüft und kann durchgeführt werden. Es werden Überarbeitungen für insgesamt <strong>{{ checkData.questionCount }} Fragen</strong> importiert.
          </v-card-text>

          <v-card-actions v-if="checkError">
            <v-btn
              color="primary"
              :disabled="!this.importLanguage"
              @click="uploadPrompt">
              <v-icon left dark>cloud_upload</v-icon>
              Neue Datei wählen
            </v-btn>
            <v-spacer />
            <v-btn @click="isOpen = false">Schließen</v-btn>
          </v-card-actions>

          <v-card-actions v-if="checkSuccess">
            <v-btn
              color="primary"
              @click="importSelectedFile">
              <v-icon left dark>cloud_upload</v-icon>
              Import starten
            </v-btn>
            <v-spacer />
            <v-btn @click="isOpen = false">Schließen</v-btn>
          </v-card-actions>
        </template>

        <template v-else>
          <v-card-text v-if="!importError && !importSuccess">
            Bitte haben Sie einen Moment Geduld, die Daten werden importiert.<br>
            Wenn viele Fragen importiert werden, kann dieser Vorgang einige Minuten dauern.
            <v-progress-linear
              indeterminate
              color="primary"
              class="mb-0" />
          </v-card-text>
          <template v-else>
            <v-card-text v-if="importError">
              <v-layout row>
                <v-flex shrink class="mr-2"><v-icon large color="red">error</v-icon></v-flex>
                <v-flex>
                  <strong>Der Import konnte nicht durchgeführt werden</strong><br>
                  {{ importError }}
                </v-flex>
              </v-layout>
            </v-card-text>
            <v-card-text v-if="importSuccess">
              <v-icon color="green">done</v-icon> Der Import wurde erfolgreich durchgeführt.
            </v-card-text>
            <v-card-actions>
              <v-spacer />
              <v-btn @click="isOpen = false">Schließen</v-btn>
            </v-card-actions>
          </template>
        </template>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
  const defaultData = {
    isLoading: false,
    isOpen: false,
    importLanguage: null,
    fileSelected: false,
    fileChecked: false,
    checkError: null,
    checkSuccess: null,
    checkData: null,
    importError: null,
    importSuccess: false,
    selectedFile: null,
  }
  export default {
    props: {
      availableLanguages: {
        type: Array,
        required: true,
      },
    },
    data() {
      return {...defaultData}
    },
    watch: {
      isOpen() {
        if(!this.isOpen) {
          this.resetDialog()
        }
      },
    },
    methods: {
      uploadPrompt() {
        if(!this.importLanguage) {
          return alert('Bitte wählen Sie erste eine Sprache')
        }
        this.$refs.uploader.click()
      },
      handleFileSelect(e) {
        this.fileSelected = true
        this.checkError = null
        this.checkSuccess = null
        this.checkData = null
        this.importError = null
        this.importSuccess = false
        this.getUploadedFile(e)
        this.checkSelectedFile()
      },
      getUploadedFile(e) {
        this.selectedFile = e.target.files[0]
        e.target.value = null
      },
      checkSelectedFile() {
        let formData = new FormData()
        formData.append("file", this.selectedFile)
        axios.post("/questions/import/check/" + this.importLanguage, formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then(response => {
          if (response.data.success) {
            this.checkSuccess = true
            this.checkData = response.data
          } else {
            this.checkError = response.data.error
          }
        })
        .catch(() => {
          this.checkError = "Die Verbindung mit dem Server ist fehlgeschlagen. Bitte probieren Sie es später erneut."
        })
      },
      importSelectedFile() {
        this.fileChecked = true
        let formData = new FormData()
        formData.append("file", this.selectedFile)
        axios.post("/questions/import", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
          .then(response => {
            if (response.data.success) {
              this.$emit('imported')
              this.importSuccess = true
            } else {
              this.importError = response.data.error
            }
          })
          .catch(() => {
            this.importError = "Die Verbindung mit dem Server ist fehlgeschlagen. Bitte probieren Sie es später erneut."
          })
          .finally(() => {
            this.isLoading = false
          })
      },
      open() {
        this.isOpen = true
      },
      resetDialog() {
        Object.keys(defaultData).forEach(key => {
          this[key] = defaultData[key]
        })
      }
    },
  }
</script>


<style lang="scss" scoped>
  input[type="file"] {
    display: none;
  }
</style>
