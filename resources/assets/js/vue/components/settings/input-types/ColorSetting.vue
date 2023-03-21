<template>
  <v-layout
    v-if="isVisible"
    row
    class="s-settingSwitch">
    <v-flex
      shrink
      class="mr-2">
      <verte
        v-model="color"
        picker="square"
        :show-history="false"
        model="hex"
        :enable-alpha="false"
        menu-position="right" />
    </v-flex>
    <v-flex
      v-if="superadminOnly"
      shrink>
      <v-tooltip bottom>
        <v-icon slot="activator">admin_panel_settings</v-icon>
        Nur für Superadmins
      </v-tooltip>
    </v-flex>
    <v-flex align-self-center>
      <div class="s-settingSwitch__label">
        {{ label }}
      </div>
      <div
        v-if="description"
        class="s-settingSwitch__description">
        {{ description }}
      </div>
    </v-flex>
  </v-layout>
</template>

<script>
  import Verte from 'verte'
  import '../../../../../css/vendor/verte.css'
  import { debounce } from 'lodash'

  export default {
    props: ['setting', 'settings', 'label', 'description', 'isCandy'],
    computed: {
      isVisible() {
        return typeof this.settings[this.setting] !== 'undefined'
      },
      superadminOnly() {
        const oldSuperadminSettings = [
          'color_primary',
          'color_secondary',
        ]
        if(!this.isCandy && oldSuperadminSettings.includes(this.setting)) {
          return true
        }
        return this.settings[this.setting].superadmin
      },
      color: {
        get() {
          return this.settings[this.setting].value
        },
        set: debounce(function(value) {
          if(value !== this.color) {
            this.$emit('updateSetting', {
              type: 'profileSetting',
              setting: this.setting,
              value: value
            })
          }
        }, 200)
      },
    },
    components: {
      Verte,
    },
  }
</script>

<style lang="scss">
  #app .s-settingSwitch {
    cursor: pointer;
    margin-bottom: 30px;
    .v-input--selection-controls {
      margin-top: 0;
      margin-right: 10px;
      padding-top: 0;
    }
  }

  #app .s-settingSwitch__label {
    font-size: 16px;
    font-weight: 500;
    color: #333;
  }

  #app .s-settingSwitch__description {
    margin-top: 3px;
    line-height: 1.5;
    font-size: 12px;
    color: #444;
  }
</style>
