export default function() {
  return {
    convert_urls: false,
    height: 600,
    menubar: false,
    statusbar: false,
    relative_urls : false,
    language: 'de',
    skin_url: '/js/skins/ui/oxide',
    contextmenu: false,
    paste_block_drop: false,
    paste_data_images: false,
    paste_as_text: false,
    external_plugins: {
      image: '/js/plugins/image/plugin.js',
      link: '/js/plugins/link/plugin.js',
      media: '/js/plugins/media/plugin.js',
      lists: '/js/plugins/lists/plugin.js',
      paste: '/js/plugins/paste/plugin.js',
    },
    plugins: [],
    toolbar1: 'insertfile undo redo | styleselect fontsizeselect | forecolor backcolor bold italic | removeformat | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media',
    images_upload_url: '/backend/api/v1/helpdesk/contents',
    images_upload_handler: function (image, success, failure) {
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
        this.snackbar = true
      })
    },
    style_formats: [
      {
        title: 'Headers',
        items: [
          { title: 'Header 1', format: 'h2' },
          { title: 'Header 2', format: 'h3' },
        ]
      },
      {
        title: 'Inline', items: [
          { title: 'Bold', icon: 'bold', format: 'bold' },
          { title: 'Italic', icon: 'italic', format: 'italic' },
          { title: 'Underline', icon: 'underline', format: 'underline' },
          { title: 'Strikethrough', icon: 'strikethrough', format: 'strikethrough' },
          { title: 'Superscript', icon: 'superscript', format: 'superscript' },
          { title: 'Subscript', icon: 'subscript', format: 'subscript' },
          { title: 'Code', icon: 'code', format: 'code' }
        ]
      },
      {
        title: 'Blocks', items: [
          { title: 'Paragraph', format: 'p' },
          { title: 'Blockquote', format: 'blockquote' },
          { title: 'Div', format: 'div' },
          { title: 'Pre', format: 'pre' }
        ]
      },
      {
        title: 'Alignment', items: [
          { title: 'Left', icon: 'alignleft', format: 'alignleft' },
          { title: 'Center', icon: 'aligncenter', format: 'aligncenter' },
          { title: 'Right', icon: 'alignright', format: 'alignright' },
          { title: 'Justify', icon: 'alignjustify', format: 'alignjustify' }
        ]
      },
    ],
  }
}
