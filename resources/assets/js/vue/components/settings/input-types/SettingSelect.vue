<template>
  <div>
    <v-select
      v-model="value"
      :items="items"
      item-text="label"
      item-value="id"
      :label="label"
      :placeholder="placeholder"
      :hint="description"
      persistent-hint />
  </div>
</template>

<script>
  export default {
    props: ['type', 'setting', 'settings', 'items', 'label', 'description', 'placeholder'],
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
