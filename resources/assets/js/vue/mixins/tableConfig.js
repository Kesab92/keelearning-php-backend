export default {
  data() {
    return {
      configStoreActive: false,
    }
  },
  created() {
    this.restoreConfig()
  },
  methods: {
    storeConfig() {
      if(!this.configStoreActive) {
        return
      }
      const config = this.getCurrentTableConfig()
      const route = this.getBaseRoute()
      if(!route.params) {
        route.params = {}
      }
      route.params.config = JSON.stringify(config)
      this.$router.push(route)
    },
    restoreConfig() {
      let config = this.$route.params.config
      if(!config) {
        this.configStoreActive = true
        return
      }
      try {
      config = JSON.parse(config)
        Object.keys(config).forEach(key => {
          this.$set(this,key,config[key])
        })
      } catch(e) {
        console.log(e)
      }
      this.configStoreActive = true
    }
  },
}
