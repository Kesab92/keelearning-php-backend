<template>
  <div>
    <img
      style="width: 300px;"
      :src="attachment.url">
  </div>
</template>

<script>
export default {
  props: ['attachment'],
}
</script>
