<template>
  <div>
    <v-layout
      class="s-wrapper py-2"
      align-center
      justify-space-between
      row
    >
      <div class="s-iconWrapper d-flex align-center">
        <v-icon class="px-2" medium>{{ icon }}</v-icon>
      </div>
      <div class="s-inputWrapper">
        <hr
          v-if="formFieldData.type === $constants.FORMS.TYPE_SEPARATOR"
          class="black--text"/>
        <translated-input
          v-else-if="formFieldData.type === $constants.FORMS.TYPE_HEADER"
          v-model="formFieldData.title"
          :translations="formFieldData.translations"
          attribute="title"
          input-type="input"
          :label="label"
          :style="`z-index: ${zIndex}`"
        />
        <translated-input
          v-else-if="formFieldData.type === $constants.FORMS.TYPE_TEXTAREA || formFieldData.type === $constants.FORMS.TYPE_RATING"
          v-model="formFieldData.title"
          :translations="formFieldData.translations"
          attribute="title"
          input-type="textarea"
          :label="label"
          auto-grow
          rows="3"
          :style="`z-index: ${zIndex}`"
        />
      </div>
      <div class="s-checkboxWrapper">
        <v-checkbox
          v-if="canBeRequired"
          v-model="formFieldData.is_required"
          label="verpflichtend"/>
      </div>
      <div>
        <v-btn
          color="red"
          flat
          icon
          @click="deleteDialogOpen = true">
          <v-icon dark>delete</v-icon>
        </v-btn>
      </div>
    </v-layout>

    <DeleteDialog
      v-model="deleteDialogOpen"
      :deletion-url="`/backend/api/v1/forms/${formFieldData.form_id}/fields/${formFieldData.id}`"
      :dependency-url="`/backend/api/v1/forms/${formFieldData.form_id}/fields/${formFieldData.id}/delete-information`"
      :entry-name="formFieldData.title"
      type-label="Field"
      @deleted="handleFormFieldDeleted">
    </DeleteDialog>
  </div>
</template>

<script>
import constants from "../../../../logic/constants"
import DeleteDialog from "../../../partials/global/DeleteDialog"
import formMixin from '../../mixin'

export default {
  mixins: [formMixin],
  components: {DeleteDialog},
  props: ["formField", 'zIndex'],
  data() {
    return {
      deleteDialogOpen: false,
      formFieldData: null,
      typeLabels: {
        [constants.FORMS.TYPE_TEXTAREA]: 'Freitextfrage an Ihre User',
        [constants.FORMS.TYPE_RATING]: 'Frage zum Sterne-Rating',
      },
    }
  },
  watch: {
    formField: {
      handler() {
        this.formFieldData = JSON.parse(JSON.stringify(this.formField))
      },
      immediate: true,
    },
    'formFieldData.title': {
      handler() {
        this.$emit('updateFormField', this.formFieldData)
      },
    },
    'formFieldData.is_required': {
      handler() {
        this.$emit('updateFormField', this.formFieldData)
      },
    },
  },
  computed: {
    icon() {
      return this.typeIcons[this.formFieldData.type]
    },
    label() {
      return this.typeLabels[this.formFieldData.type]
    },
    canBeRequired() {
      return [this.$constants.FORMS.TYPE_TEXTAREA, this.$constants.FORMS.TYPE_RATING].includes(this.formFieldData.type)
    }
  },
  methods: {
    handleFormFieldDeleted() {
      this.deleteDialogOpen = false
      this.$emit('formFieldDeleted', this.formFieldData)
    },
  }
}
</script>

<style lang="scss" scoped>
.s-wrapper {
  cursor: pointer;
  gap: 16px;

  &:hover {
    cursor: pointer;
    background: #f1f1f1;
  }
}
.s-iconWrapper {
  align-self: stretch;
  cursor: move;
}
.s-inputWrapper{
  flex-grow: 1;
}
.s-checkboxWrapper{
  min-width: 145px;
}
</style>
