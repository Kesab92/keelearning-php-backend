<template>
  <v-text-field
    v-model="value"
    outline
    :hint="hint"
    :label="label" />
</template>

<script>
  export default {
    props: ['type', 'setting', 'settings', 'items', 'label', 'hint'],
    computed: {
      value: {
        get() {
          return this.settings[this.setting].value
        },
        set(value) {
          this.$emit('updateSetting', {
            type: this.type,
            setting: this.setting,
            value: value
          })
        },
      },
    },
  }
</script>
