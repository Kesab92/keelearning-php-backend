<template>
  <div>
    <img
      style="width: 300px;"
      :src="materialData.file_url">
  </div>
</template>

<script>
export default {
  props: ['materialData'],
}
</script>
