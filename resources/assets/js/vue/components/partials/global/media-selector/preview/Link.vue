<template>
  <a
    :href="attachment.link"
    target="_blank">{{ attachment.link }}</a>
</template>

<script>

export default {
  props: ['attachment'],
}
</script>
