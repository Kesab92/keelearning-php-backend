<template>
  <v-btn-toggle
    v-model="isTrue"
    mandatory
  >
    <v-btn
      color="red"
      :outline="!!isTrue"
      :disabled="disabled"
      :class="{
              'white--text': !isTrue,
            }">
      Falsch
    </v-btn>
    <v-btn
      color="green"
      :outline="!isTrue"
      :disabled="disabled"
      :class="{
              'white--text': !!isTrue,
            }">
      Richtig
    </v-btn>
  </v-btn-toggle>
</template>

<script>
export default {
  props: ['value', 'disabled'],
  data () {
    return {
      isTrue: false,
    }
  },
  watch: {
    value: {
      handler() {
        this.isTrue = this.value
      },
      immediate: true,
    },
    isTrue() {
      this.$emit('input', this.isTrue)
    }
  },
}
</script>
