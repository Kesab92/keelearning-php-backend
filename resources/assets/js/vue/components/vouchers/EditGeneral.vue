<template>
  <div v-if="voucherData">
    <VoucherToolbar
      :voucher="voucherData"
      :is-saving="isSaving"
      :disabled-saving="!isValid"
      @save="save"
    />
    <div class="pa-4">
      <v-form
        v-model="isValid"
        ref="form"
        lazy-validation>
        <v-text-field
          v-model="voucherData.name"
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
              v-model="voucherData.validity_duration"
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
              v-model="voucherData.validity_interval"
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
        <v-alert
          v-show="containsInvalidTags"
          type="warning"
        >
          Diese Voucher haben ungültige TAG-Einstellungen. Bitte überprüfen Sie die korrigierten Angaben und drücken Sie
          auf "Speichern".
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
          clearable
        />

        <div v-if="!voucherData.archived">
          <h4 class="mb-0 mt-4">Codes hinzufügen</h4>
          <div class="pb-4 pt-0">
            <VoucherAmount :voucher="voucherData" />
          </div>
        </div>
      </v-form>
    </div>
  </div>
</template>

<script>
import VoucherToolbar from "./VoucherToolbar"
import VoucherAmount from "./VoucherAmount"
import {mapGetters} from "vuex";

export default {
  props: ["voucher"],
  data() {
    return {
      isValid: false,
      voucherData: null,
      isSaving: false,
      errorResponse: false,
      successResponse: false,
      loading: false,
      message: null,
      indefinite: false,
      selectedTagsByGroup: {},
      selectedTagsWithoutGroup: [],
      containsInvalidTags: false,
      tagRules: [
        v => !!v && v.length > 0 || 'Es muss mindestens ein TAG ausgewählt werden.'
      ],
      nameRules: [
        v => !!v || 'Der Voucher benötigt einen Namen.',
        v => !!v && v.length > 2 || 'Der Name des Vouchers muss mindestens 3 Zeichen lang sein'
      ],
    }
  },
  computed: {
    ...mapGetters({
      tagGroups: 'vouchers/tagGroups',
      tagsWithoutGroup: 'vouchers/tagsWithoutGroup',
      tagsRequired: 'vouchers/tagsRequired',
    }),
    areTagsRequired() {
      return this.tagsRequired ? this.tagRules : []
    },
    validityIntervals() {
      const duration = parseInt(this.voucherData.validity_duration, 10)
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
    selectableTagGroups() {
      if (!this.tagGroups) {
        return []
      }
      return this.tagGroups.filter(tg => !tg.signup_selectable)
    },
  },
  watch: {
    voucher: {
      handler() {
        if(!this.voucher) {
          return
        }

        this.voucherData = JSON.parse(JSON.stringify(this.voucher))
        if(!this.voucher.validity_duration) {
          this.indefinite = true
        }
        this.parseTags()
      },
      immediate: true,
    },
    tagGroups() {
      if(!this.voucher) {
        return
      }

      this.parseTags()
    },
    indefinite: {
      handler() {
        if(this.indefinite) {
          this.voucherData.validity_duration = null
        } else {
          this.voucherData.validity_duration = 1
          this.voucherData.validity_interval = this.$constants.VOUCHERS.INTERVAL_MONTHS
        }
      },
    },
  },
  methods: {
    async save() {
      if (this.isSaving) {
        return
      }

      if (!this.$refs.form.validate()) {
        return
      }

      // flatten the selected tags by group back into the voucher object
      this.saveTags()

      this.isSaving = true
      await this.$store.dispatch("vouchers/saveVoucher", this.voucherData)
      this.isSaving = false
    },
    parseTags() {
      this.containsInvalidTags = false
      // sort the selected tags by group
      if (!this.voucherData.selectedTags || (!this.tagGroups && !this.selectedTagsWithoutGroup)) {
        return
      }
      this.selectedTagsWithoutGroup = []
      this.selectedTagsByGroup = {}
      for (let i = 0; i < this.voucherData.selectedTags.length; i++) {
        let tagId = this.voucherData.selectedTags[i]
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
      this.voucherData.selectedTags = tags
    },
  },
  components: {
    VoucherToolbar,
    VoucherAmount,
  }
}
</script>
