<template>
  <div>
    <v-radio-group v-model="newPublishType" row>
      <v-radio
        v-for="publishType in publishTypes"
        :key="publishType.value"
        :label="publishType.label"
        :value="publishType.value"
        :disabled="isReadonly"
      ></v-radio>
    </v-radio-group>
    <DatePicker
      v-if="publishType === $constants.PUBLISHED_AT_TYPES.PLANNED"
      v-model="newPublishedAt"
      :disabled="isReadonly"
      :clearable="false"
      :label="label"/>
  </div>
</template>

<script>
import DatePicker from "./Datepicker"

export default {
  props: {
    publishedAt: {
      type: String | null,
      required: true,
    },
    publishType: {
      type: String,
      required: true,
    },
    label: {
      type: String,
      default: 'Veröffentlichen am ...',
      required: false,
    },
    isReadonly: {
      type: Boolean,
      default: false,
      required: false,
    },
  },
  data() {
    return {
      publishTypes: [
        {
          label: 'Sofort',
          value: 'immediately',
        },
        {
          label: 'In der Zukunft',
          value: 'planned',
        },
      ]
    }
  },
  created() {
    if(!this.newPublishedAt) {
      this.publishType = this.$constants.PUBLISHED_AT_TYPES.IMMEDIATELY
    } else {
      this.publishType = this.$constants.PUBLISHED_AT_TYPES.PLANNED
    }
  },
  computed: {
    newPublishedAt: {
      get() {
        return this.publishedAt
      },
      set(publishedAt) {
        this.$emit('update:publishedAt', publishedAt)
      },
    },
    newPublishType: {
      get() {
        return this.publishType
      },
      set(publishType) {
        this.$emit('update:publishType', publishType)
      },
    },
  },
  components: {
    DatePicker,
  }
}
</script>
