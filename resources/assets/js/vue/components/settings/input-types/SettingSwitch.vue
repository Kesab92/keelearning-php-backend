<template>
  <v-layout
    v-if="isVisible"
    row
    class="s-settingSwitch"
    :class="{
      '-disabled': disabled,
    }">
    <v-flex shrink>
      <v-switch
        hide-details
        height="30"
        v-model="isActive" />
    </v-flex>
    <v-flex
      v-if="superadminOnly"
      shrink>
      <v-tooltip bottom>
        <v-icon slot="activator">admin_panel_settings</v-icon>
        Nur für Superadmins
      </v-tooltip>
    </v-flex>
    <v-flex
      @click="toggle"
      align-self-center>
      <div class="s-settingSwitch__label">
        {{ label }}
        <slot name="append_label"></slot>
      </div>
      <div
        v-if="description"
        class="s-settingSwitch__description">
        {{ description }}
      </div>
      <div
        class="s-settingSwitch__contactSales"
        v-if="isContactSalesTextVisible">
        Bitte kontaktieren Sie
        <a
          href="mailto:sales@keeunit.de"
          target="_blank">sales@keeunit.de</a>
        um dieses Modul zu aktivieren.
      </div>
    </v-flex>
  </v-layout>
</template>

<script>
  export default {
    props: ['type', 'setting', 'settings', 'label', 'description', 'disabled', 'isCandy'],
    computed: {
      isVisible() {
        return typeof this.settings[this.setting] !== 'undefined'
      },
      superadminOnly() {
        // These settings are allowed for candy app admins, but not for non-candy admins
        const oldSuperadminSettings = {
          profileSetting: [
            'module_news',
            'module_learningmaterials',
            'module_powerlearning',
            'module_indexcards',
            'module_quiz',
            'module_quiz_teams',
            'module_suggested_questions',
            'module_competitions',
            'module_tests',
            'module_webinars',
            'module_courses',
            'module_advertisements',
            'module_keywords',
            'module_comments',
            'signup_enabled',
            'signup_show_firstname',
            'signup_show_lastname',
            'signup_show_email',
            'signup_show_voucher',
            'signup_has_temporary_accounts',
            'quiz_enable_bots',
            'tablet_light_background',
            'allow_custom_avatars',
          ],
          appSetting: [],
          notificationSetting: [],
        }
        if(!this.isCandy && oldSuperadminSettings[this.type].includes(this.setting)) {
          return true
        }
        return this.settings[this.setting].superadmin
      },
      isActive: {
        get() {
          return this.settings[this.setting].value
        },
        set(value) {
          this.$emit('updateSetting', {
            type: this.type,
            setting: this.setting,
            value: value
          })
        },
      },
      isContactSalesTextVisible() {
        if(!this.disabled) {
          return false
        }
        const profileSettingsWithoutText = [
          'enable_sso_registration'
        ]

        return !profileSettingsWithoutText.includes(this.setting)
      }
    },
    methods: {
      toggle() {
        if(this.disabled) {
          return
        }
        this.isActive = !this.isActive
      }
    }
  }
</script>

<style lang="scss">
  #app .s-settingSwitch {
    cursor: pointer;
    margin-bottom: 30px;

    &.-disabled {
      filter: grayscale(100%);
      opacity: 0.8;
      cursor: not-allowed;
      pointer-events: none;

      .s-settingSwitch__contactSales {
        cursor: default;
        pointer-events: all;
      }
    }

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

  #app .s-settingSwitch__disabled {
    margin-top: 3px;
  }

  #app .s-settingSwitch__description {
    margin-top: 3px;
    line-height: 1.5;
    font-size: 12px;
    color: #444;
  }
</style>
