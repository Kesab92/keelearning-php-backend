<template>
  <a
    :href="materialData.link"
    target="_blank">{{ materialData.link }}</a>
</template>

<script>

export default {
  props: ['materialData'],
}
</script>
