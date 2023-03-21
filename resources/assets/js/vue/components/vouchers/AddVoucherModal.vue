<template>
  <span>
    <v-snackbar
      color="success"
      v-model="successResponse"
      :top="true">
      Der Voucher wurde erfolgreich gespeichert.
    </v-snackbar>
    <v-snackbar
      color="error"
      v-model="errorResponse"
      :top="true">
      {{ message }}
    </v-snackbar>
    <v-dialog
      v-model="dialog"
      max-width="500px"
      persistent>
      <v-btn
        slot="activator"
        :color="!voucher.id ? 'primary' : ''">
        <span v-if="!voucher.id">Voucher hinzufügen</span>
        <span v-else>
          <v-icon dark>edit</v-icon>
        </span>
      </v-btn>
      <v-card>
        <v-toolbar
          card
          dark
          color="primary">
          <v-btn
            icon
            dark
            @click.native="dialog = false">
            <v-icon>close</v-icon>
          </v-btn>
          <v-toolbar-title v-if="!voucher.id">Voucher hinzufügen</v-toolbar-title>
          <v-toolbar-title v-else>Voucher bearbeiten</v-toolbar-title>
          <v-spacer/>
          <v-toolbar-items>
            <v-btn
              dark
              flat
              :disabled="loading"
              :loading="loading"
              @click.native="save">
              Speichern
            </v-btn>
          </v-toolbar-items>
        </v-toolbar>

        <v-card-text>
          <v-form
            ref="form"
            lazy-validation>
            <v-text-field
              v-model="voucher.name"
              placeholder="Name"
              :rules="nameRules"
              label="Name"
              required
            />
            <v-switch
              label="Voucher erlaubt zeitlich unbeschränkten Zugang"
              v-model="indefinite"
            />
            <v-layout
              v-if="!indefinite"
              row>
              <v-flex
                xs12
                md6>
                <v-text-field
                  v-model="voucher.validity_duration"
                  label="Gültigkeitsdauer"
                  class="mr-4"
                  hide-details
                />
              </v-flex>
              <v-flex
                xs12
                md6>
                <v-select
                  item-text="text"
                  item-value="value"
                  v-model="voucher.validity_interval"
                  :items="validityIntervals"
                  label="Zeitraum"
                  required
                  hide-details
                />
              </v-flex>
            </v-layout>
            <v-alert
              v-if="!indefinite"
              :value="true"
              color="info"
              icon="info"
              class="mt-2"
              outline>
              Die Zeitdauer gilt jeweils ab dem Einlösedatum des Vouchers.
            </v-alert>
            <v-switch
              :disabled="!!voucher.id"
              :label="type === 'SINGLE_CODE' ? 'Ein Code für mehrere Benutzer':'Ein Code pro Benutzer'"
              value="SINGLE_CODE"
              v-model="type"
            />
            <template v-if="type === 'SINGLE_CODE'">
              <v-text-field
                :disabled="!!voucher.id"
                v-model="voucher.code"
                :rules="codeRules"
                label="Geben Sie hier einen individuellen Code ein z.B. Seminar April 2019"
                required
              />
            </template>
            <v-text-field
              :disabled="!!voucher.id"
              type="number"
              v-model="voucher.amount"
              :rules="amountRules"
              :label="type === 'SINGLE_CODE' ? 'Hinterlegen Sie, wie oft dieser Code eingelöst werden kann' : 'Anzahl an Voucher Codes'"
              required
            />
            <v-alert
              v-show="containsInvalidTags"
              type="warning"
            >
              Diese Voucher haben ungültige TAG-Einstellungen. Bitte überprüfen Sie die korrigierten Angaben und drücken Sie auf "Speichern".
            </v-alert>
            <v-select
              item-text="label"
              item-value="id"
              v-model="selectedTagsWithoutGroup"
              :items="tagsWithoutGroup"
              label="TAGs"
              multiple
            />
            <v-select
              v-for="tagGroup in selectableTagGroups"
              :key="`taggroup-${tagGroup.id}`"
              item-text="label"
              item-value="id"
              v-model="selectedTagsByGroup[tagGroup.id]"
              :items="tagGroup.tags"
              :rules="areTagsRequired"
              :label="`${tagGroup.name} (optional)`"
            />
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>
  </span>
</template>

<script>
    export default {
      props: {
        tagsWithoutGroup: {
          type: Array,
          default() {
            return []
          },
          required: false,
        },
        tagGroups: {
          type: Array,
          default() {
            return []
          },
          required: false,
        },
        voucherData: {
          type: Object,
          default() {
            return {
              type: '',
              name: '',
              amount: 1,
              validity_duration: null,
              validity_interval: 0,
              selectedTags: [],
            }
          },
          required: false
        },
        tagsRequired: {
          type: Boolean,
          required: true,
        },
      },
      data() {
        return {
          errorResponse: false,
          successResponse: false,
          dialog: false,
          loading: false,
          message: null,
          voucher: null,
          indefinite: false,
          selectedTagsByGroup: {},
          selectedTagsWithoutGroup: [],
          containsInvalidTags: false,
          codeRules: [
            v => !!v || 'Der Voucher benötigt einen individuellen Code'
          ],
          nameRules: [
            v => !!v || 'Der Voucher benötigt einen Namen.',
            v => !!v && v.length > 2 || 'Der Name des Vouchers muss mindestens 3 Zeichen lang sein'
          ],
          tagRules: [
            v => !!v && v.length > 0 || 'Es muss mindestens ein TAG ausgewählt werden.'
          ],
          amountRules: [
            v => !!v || 'Der Voucher muss mindestens 1 Code enthalten.'
          ],
        }
      },
      created() {
        this.setVoucherData()
        this.parseTags()
      },
      computed: {
        areTagsRequired() {
          return this.tagsRequired ? this.tagRules : []
        },
        validityIntervals() {
          const duration = parseInt(this.voucher.validity_duration, 10)
          return [
            {
              value: this.$constants.VOUCHERS.INTERVAL_DAYS,
              text: duration === 1 ? 'Tag' : 'Tage'
            },
            {
              value: this.$constants.VOUCHERS.INTERVAL_MONTHS,
              text: duration === 1 ? 'Monat' : 'Monate'
            },
          ]
        },
        type: {
          set(value) {
            if (!value || value === 'MULTIPLE_CODE') {
              this.voucher.type = 0
            } else if (value === 'SINGLE_CODE') {
              this.voucher.type = 1
            }
          },
          get() {
            if (this.voucher.type === 1) {
              return 'SINGLE_CODE'
            } else if (this.voucher.type === 0) {
              return 'MULTIPLE_CODE'
            }
          }
        },
        selectableTagGroups() {
          if (!this.tagGroups) {
            return []
          }
          return this.tagGroups.filter(tg => !tg.signup_selectable)
        },
      },
      watch: {
        tagGroups() {
          this.parseTags()
        },
        indefinite() {
          if(this.indefinite) {
            this.voucher.validity_duration = null
          } else {
            this.voucher.validity_duration = 1
            this.voucher.validity_interval = this.$constants.VOUCHERS.INTERVAL_MONTHS
          }
        },
      },
      methods: {
        setVoucherData() {
          this.voucher = JSON.parse(JSON.stringify(this.voucherData))
          if(!this.voucher.validity_duration) {
            this.indefinite = true
          }
        },
        save() {
          if (!this.$refs.form.validate()) {
            return
          }

          // Removes the code if the user decides before to use other type of vouchers
          if (this.voucher.type === 0) {
              this.voucher.code = null
          }

          // flatten the selected tags by group back into the voucher object
          this.saveTags()

          let apiUrl = this.voucher.id ? '/backend/api/v1/vouchers/' + this.voucher.id : '/backend/api/v1/vouchers'
          this.loading = true
          axios.post(apiUrl, this.voucher).then(response => {
            if (response.data.success) {
              this.successResponse = true
              if (!this.voucher.id) {
                this.$refs.form.reset()
                this.selectedTagsWithoutGroup = []
                this.selectedTagsByGroup = {}
                this.setVoucherData()
              }
              this.$emit('update')
              this.dialog = false
            } else {
              this.message = response.data.error
              this.errorResponse = true
            }
            this.loading = false
          }).catch(error => {
            this.message = 'Es ist ein unerwarteter Fehler aufgetreten.'
            this.errorResponse = true
          })
        },
        parseTags() {
          this.containsInvalidTags = false
          // sort the selected tags by group
          if (!this.voucher.selectedTags || (!this.tagGroups && !this.selectedTagsWithoutGroup)) {
            return
          }
          this.selectedTagsWithoutGroup = []
          this.selectedTagsByGroup = {}
          for (let i = 0; i < this.voucher.selectedTags.length; i++) {
            let tagId = this.voucher.selectedTags[i]
            if (this.tagsWithoutGroup.findIndex(t => t.id == tagId) != -1) {
              this.selectedTagsWithoutGroup.push(tagId)
              continue
            }
            for (let j = 0; j < this.tagGroups.length; j++) {
              if (this.tagGroups[j].tags.findIndex(t => t.id == tagId) != -1) {
                if (!this.tagGroups[j].can_have_duplicates && this.selectedTagsByGroup[this.tagGroups[j].id]) {
                  // more than one tag from same group was previously selected?
                  this.containsInvalidTags = true
                  break
                }
                if (this.tagGroups[j].signup_selectable) {
                  // tag from a signup-selectable group?
                  this.containsInvalidTags = true
                  break
                }
                this.selectedTagsByGroup[this.tagGroups[j].id] = tagId
                break
              }
            }
          }
        },
        saveTags() {
          let tags = this.selectedTagsWithoutGroup.slice()
          for (let tagGroup in this.selectedTagsByGroup) {
            if (this.selectedTagsByGroup.hasOwnProperty(tagGroup)) {
              tags.push(this.selectedTagsByGroup[tagGroup])
            }
          }
          this.voucher.selectedTags = tags
        },
      },
    }
</script>
