<template>
  <v-layout>
    <v-flex grow>
      <div
        class="s-progressBar"
        :class="{
          '-large': large,
          'grey lighten-2': !noBackground,
        }">
        <div
          class="s-progressBar__bar"
          :class="[{'-large': large}, color]"
          :style="`width: ${percentage}%;`" />
      </div>
    </v-flex>
    <div class="s-progressBar__label">
      {{ percentage }}%
    </div>
  </v-layout>
</template>

<script>
export default {
  props: {
    value: {
      type: Number,
      required: true,
    },
    color: {
      type: String,
      required: false,
      default: 'cyan lighten-1'
    },
    large: {
      type: Boolean,
      required: false,
      default: false,
    },
    noBackground: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  computed: {
    percentage() {
      return Math.round(this.value * 100)
    },
  },
}
</script>

<style lang="scss" scoped>
$height-default: 8px;
$height-large: 16px;

.s-progressBar {
  width: 100%;
  position: relative;
  overflow: hidden;
  height:$height-default;
  border-radius: $height-default * 0.5;
  display: inline-block;

  &.-large{
    height: $height-large;
    border-radius: $height-large * 0.5;
  }
}

.s-progressBar__bar{
  position: absolute;
  top:0;
  left:0;
  height:100%;
  border-radius: $height-default * 0.5;
  &.-large{
    border-radius: $height-large * 0.5;
  }
}

.s-progressBar__label {
  flex: 0 0 35px;
  text-align: right;
}
</style>
