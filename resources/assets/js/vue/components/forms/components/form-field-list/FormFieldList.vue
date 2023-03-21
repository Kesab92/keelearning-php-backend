<template>
  <div
    class="s-contentsWrapper"
    ref="wrapper">
    <Draggable
      v-model="formFieldsData"
      @change="handleReorder">
      <FormField
        v-for="(field, index) in formFieldsData"
        :key="field.id"
        :form-field="field"
        :z-index="formFieldsData.length - index"
        @formFieldDeleted="handleDeleteField"
        @updateFormField="handleUpdateField"
      />
    </Draggable>
  </div>
</template>

<script>
import Draggable from 'vuedraggable'
import Helpers from "../../../../logic/helpers"
import FormField from "./FormField"

export default {
  props: ['formFields'],
  data() {
    return {
      formFieldsData: null,
    }
  },
  watch: {
    formFields: {
      handler() {
        this.formFieldsData = JSON.parse(JSON.stringify(this.formFields))
      },
      immediate: true,
    },
  },
  methods: {
    handleDeleteField(deletedField) {
      this.formFieldsData = this.formFieldsData.filter(field => {
        return field.id !== deletedField.id
      })

      this.$emit('formFieldDeleted', this.formFieldsData)
    },
    handleReorder(data) {
      this.formFieldsData.sort((a, b) => {
        return a.position - b.position
      })
      this.formFieldsData = Helpers.reorder(this.formFieldsData, data.moved.oldIndex, data.moved.newIndex)
      this.formFieldsData.forEach((field, idx) => {
        field.position = idx
      })
      this.$emit('update:formFields', this.formFieldsData)
    },
    handleUpdateField(updatedField) {
      this.formFieldsData.forEach((field, index) => {
        if(field.id === updatedField.id) {
          this.formFieldsData[index] = updatedField
        }
      })
      this.$emit('update:formFields', this.formFieldsData)
    },
  },
  components: {
    FormField,
    Draggable,
  }
}
</script>

<style lang="scss">
.s-contentsWrapper .sortable-ghost .s-wrapper {
  background: #616161 !important;
  color: white;
  box-shadow: inset 0 5px 5px -5px rgba(0, 0, 0, 0.42);
  * {
    color: white !important;
  }
}
</style>
