<template>
  <div v-if="formFields">
    <div
      v-if="!formFields.length"
      class="pa-2">
      Das gewählte Formular hat noch keinen Inhalt.
    </div>
    <v-layout
      v-else
      v-for="field in formFields"
      :key="field.id"
      align-center
      row
      class="py-2">
      <v-icon class="px-2" medium>{{ getIcon(field) }}</v-icon>
      <div>
        <div class="caption grey--text">{{ getLabel(field) }}</div>
        <div class="subheading">{{ field.title }}</div>
      </div>
    </v-layout>
  </div>
</template>

<script>
import formMixin from '../../../../forms/mixin'

export default {
  mixins: [formMixin],
  props: ['formFields'],
  methods: {
    getIcon(field) {
      return this.typeIcons[field.type]
    },
    getLabel(field) {
      return this.typeLabels[field.type]
    },
  }
}
</script>
