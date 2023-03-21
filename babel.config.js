module.exports = {
  presets: [
    [
      '@vue/cli-plugin-babel/preset',
      {
        exclude: ['es.promise', 'web.url'],
      },
    ],
  ],
}
