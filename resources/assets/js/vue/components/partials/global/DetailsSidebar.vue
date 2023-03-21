<template>
  <v-navigation-drawer
    v-model="drawerCurrentlyOpen"
    fixed
    right
    disable-resize-watcher
    disable-route-watcher
    temporary
    :width="width"
    class="js-sidebar"
  >
    <v-layout
      v-if="isLoading || loadingError"
      align-center
      justify-center
      fill-height
    >
      <v-progress-circular
        v-if="isLoading"
        :size="70"
        :width="3"
        color="primary"
        indeterminate
      />
      <div v-if="loadingError">
        Dieser Eintrag konnte leider nicht geladen werden. Bitte wenden Sie sich an den Support.
      </div>
    </v-layout>
    <v-layout
      v-else-if="remoteData || dataGetter === null"
      row
      class="c-drawer__container">
      <v-flex
        v-show="!isWide"
        shrink
        class="c-drawer__sidebar">
        <v-navigation-drawer
          permanent
          class="c-drawer__sidebarNavigation">
          <div class="s-drawerNavigation__header">
            <div class="s-drawerNavigation__close">
              <v-icon
                @click="drawerCurrentlyOpen = false"
                medium
                title="Detailansicht schließen">arrow_back</v-icon>
            </div>
            <div class="s-drawerNavigation__title">
              <slot
                name="headerTitle"
                :data="remoteData" />
            </div>
            <slot
              name="headerExtension"
              :data="remoteData" />
          </div>

          <v-list
            class="pt-0 s-subnav"
            dense>
            <v-divider/>

            <v-list-tile
              v-for="item in getLinks(remoteData)"
              :key="item.label"
              :to="item.to"
            >
              <v-list-tile-content>
                <v-list-tile-title>{{ item.label }}</v-list-tile-title>
              </v-list-tile-content>
            </v-list-tile>
          </v-list>
        </v-navigation-drawer>
      </v-flex>
      <v-flex class="c-drawer__content">
        <slot
          name="default"
          :data="remoteData"
          :refresh="loadRemoteData"
          :refresh-silently="loadRemoteDataSilently"
          class="c-drawer__contentRouterView" />
      </v-flex>
    </v-layout>
  </v-navigation-drawer>
</template>

<script>
import isEqual from 'lodash/isEqual'

const MENU_WIDTH = 300

export default {
  props: {
    rootUrl: {
      type: Object,
      required: true,
    },
    drawerOpen: {
      type: Boolean,
      required: false,
      default: false,
    },
    dataAction: {
      type: String,
      required: false,
      default: null,
    },
    dataParams: {
      type: Object,
      default: null,
    },
    dataGetter: {
      type: Function,
      default: null,
    },
    getLinks: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      isLoading: false,
      loadingError: false,
      width: null,
    }
  },
  computed: {
    drawerCurrentlyOpen: {
      get() {
        return this.drawerOpen
      },
      set(isVisible) {
        if (!isVisible) {
          this.$router.push(this.rootUrl).catch(()=>{})
        }
      },
    },
    isWide() {
      return !!this.$route.meta.wideSidebar
    },
    remoteData: {
      get() {
        if(typeof this.dataGetter !== 'function') {
          return null
        }

        return this.dataGetter(this.dataParams)
      },
      set() {
        console.warn('Setting remote data is not supported')
      },
    },
  },
  watch: {
    drawerCurrentlyOpen: {
      handler() {
        if (this.drawerCurrentlyOpen) {
          this.loadRemoteData()
        }
      },
      immediate: true,
    },
    isWide() {
      this.setWidth()
    },
    dataParams: {
      deep: true,
      handler(newValue, oldValue) {
        if (!this.drawerCurrentlyOpen || isEqual(newValue, oldValue)) {
          return
        }
        this.loadRemoteData()
      },
    },
  },
  created() {
    this.setWidth()
    window.addEventListener('resize', this.setWidth)
  },
  destroyed() {
    window.removeEventListener('resize', this.setWidth)
  },
  methods: {
    loadRemoteData() {
      if (this.isLoading) {
        return
      }
      if(!this.dataAction) {
        this.isLoading = false
        return
      }
      this.isLoading = true
      this.$store.dispatch(this.dataAction, this.dataParams).then(() => {
        this.$nextTick(() => {
          this.loadingError = false
          this.isLoading = false
        })
      }).catch((e) => {
        this.loadingError = true
        this.isLoading = false
      })
    },
    loadRemoteDataSilently() {
      if(!this.dataAction) {
        return
      }
      this.$store.dispatch(this.dataAction, this.dataParams).then(() => {
        this.$nextTick(() => {
          this.loadingError = false
        })
      }).catch((e) => {
        this.loadingError = true
      })
    },
    setWidth() {
      this.width = Math.min(this.isWide ? 1300 : 1200, window.innerWidth - MENU_WIDTH)
    }
  },
}
</script>

<style lang="scss" scoped>
.color-elevation {
  box-shadow: 0px 6px 6px -3px rgba(64, 149, 171, .2), 0px 10px 14px 1px rgba(64, 149, 171, .14), 0px 4px 18px 3px rgba(64, 149, 171, .12) !important;
}
#app {
  .s-subnav.v-list ::v-deep .v-list__tile--link {
    margin: 10px 0;

    &:hover {
      background: transparent;
    }

    .v-list__tile__title {
      color: rgb(31,32,48);
      border-radius: 25px;
      padding: 0 15px 0 15px;
      height: 48px;
      display: flex;
      align-items: center;
    }

    &.v-list__tile--active .v-list__tile__title {
      background: rgba(31,32,48,0.06);
      color: #1976d2;
    }
  }

  .s-drawerNavigation__header {
    background-color: #f5f5f5;
    color: rgba(0, 0, 0, 0.87);
    border-bottom: #e0e0e0;
    padding: 0 24px 24px 24px;
  }

  .s-drawerNavigation__title {
    margin-bottom: 16px;
    font-size: 20px;
    font-weight: 500;
    letter-spacing: .02em;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
  }

  .s-drawerNavigation__header ::v-deep .s-drawerNavigation__subtitle {
    font-size: 16px;
    font-weight: 400;
  }

  .s-drawerNavigation__close {
    padding-top: 8px;
    margin-left: -3px;
  }
}

</style>
