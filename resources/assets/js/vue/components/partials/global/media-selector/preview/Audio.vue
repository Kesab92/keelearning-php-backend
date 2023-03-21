<template>
  <div>
    <audio controls>
      <source
        :src="attachment.url"
        :type="type">
      Your browser does not support the audio element.
    </audio>
  </div>
</template>

<script>
export default {
  props: ['attachment'],

  computed: {
    type() {
     const extension = this.attachment.url.split('.').pop()

      switch (extension.trim().toLowerCase()) {
       case 'mp3':
         return 'audio/mpeg'
      }
    }
  }
}
</script>
