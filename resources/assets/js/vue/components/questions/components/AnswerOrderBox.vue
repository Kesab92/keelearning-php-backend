<template>
  <div
    class="d-inline-block o-rounded --circle s-answerOrderBox__circle mb-2 text-xs-center"
    v-bind="$attrs">
    <div
      class="d-inline-flex"
      style="vertical-align: middle;">
      <slot></slot>
    </div>
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
}
</script>

<style>
.s-answerOrderBox__circle{
  width:32px;
  height:32px;
  line-height: 32px;
  box-sizing: border-box;
}
</style>
