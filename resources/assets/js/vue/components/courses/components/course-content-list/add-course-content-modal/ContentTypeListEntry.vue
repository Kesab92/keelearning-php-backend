<template>
  <div
    class="s-type"
    :class="{
      '-active': active === type,
    }"
    @mouseenter="$emit('select', type)">
    {{ label }}
  </div>
</template>

<script>
  import courses from "../../../../../logic/courses"

  export default {
    props: ['type', 'active'],
    computed: {
      label() {
        return courses.contentTypeLabels[this.type]
      },
    }
  }
</script>

<style lang="scss" scoped>
  #app .s-type {
    cursor: pointer;
    width: 100%;
    padding: 12px 24px;
    &:hover, &.-active {
      background: #f1f1f1;
    }
  }
</style>
