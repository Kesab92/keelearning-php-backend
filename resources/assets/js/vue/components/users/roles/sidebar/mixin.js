import { mapGetters } from 'vuex'
import Toolbar from './Toolbar'

export default {
  props: ['userRole'],
  data() {
    return {
      isSaving: false,
      userRoleData: null,
      isValid: true,
    }
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
    }),
  },
  watch: {
    userRole: {
      handler() {
        this.userRoleData = JSON.parse(JSON.stringify(this.userRole))
      },
      immediate: true,
    },
  },
  components: {
    Toolbar,
  },
}
