<template>
  <Editor
    v-model="content"
    :init="settings"
    :placeholder="placeholder"
    :disabled="isInitialized ? disabled : false"
    ref="editor"
    @onInit="onInitialization"
  />
</template>

<script>
import tinyMCEOptions from '../../../logic/tinyMCEOptions'
import Editor from '@tinymce/tinymce-vue'

export default {
  props: {
    value: String,
    placeholder: String,
    height: {
      type: Number,
      default: 300,
      required: false,
    },
    disabled: {
      type: Boolean,
      default: false,
      required: false,
    }
  },
  data() {
    return {
      isInitialized: false,
    }
  },
  computed: {
    content: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      },
    },
    settings() {
      const options = tinyMCEOptions()
      options.height = this.height
      options.plugins = [
        'link',
        'lists',
        'paste',
        'image',
        'table',
      ]
      options.external_plugins = {
        link: '/js/plugins/link/plugin.js',
        lists: '/js/plugins/lists/plugin.js',
        paste: '/js/plugins/paste/plugin.js',
        image: '/js/plugins/image/plugin.js',
        table: '/js/plugins/table/plugin.js',
      }
      options.images_upload_handler = this.uploadImage
      options.toolbar1 = `${options.toolbar1} table`
      return options
    },
  },
  methods: {
    uploadImage(image, success, failure) {
      let formData = new FormData()
      formData.append('file', image.blob(), image.filename())
      axios.post('/backend/api/v1/helpdesk/contents', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      }).then(response => {
        if (response.data.success) {
          success(response.data.data)
        } else {
          failure(response.data.error)
        }
      }).catch(error => {
        alert('Das Bild konnte leider nicht hochgeladen werden. Bitte versuchen Sie es später erneut.')
      })
    },
    onInitialization() {
      this.$refs.editor.editor.on('blur', this.handleBlur)
      this.$refs.editor.editor.on('focus', this.handleFocus)
      // we'll only pass the disabled flag after initialization due to a bug with TinyMCE
      // ignoring the disabled flag if passed at the wrong time
      this.isInitialized = true
    },
    handleBlur(e) {
      this.$emit('blur', e)
    },
    handleFocus(e) {
      this.$emit('focus', e)
    },
  },
  components: {
    Editor,
  },
}
</script>
