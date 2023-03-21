<template>
  <div>
    <audio controls>
      <source
        :src="materialData.file_url"
        :type="materialData.file_type">
      Your browser does not support the audio element.
    </audio>
  </div>
</template>

<script>
export default {
  props: ['materialData'],
}
</script>
