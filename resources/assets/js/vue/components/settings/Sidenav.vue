<template>
  <div class="s-sidenav">
    <div class="s-navArea primary--text">
      <v-icon
        color="primary"
        small>settings</v-icon> Adminseite
    </div>
    <div
      @click="select('admin.options')"
      :class="{'-active': selected === 'admin.options'}"
      class="s-navItem">
      Optionen
    </div>
    <div
      v-if="superadmin"
      @click="select('admin.disabled')"
      :class="{'-active': selected === 'admin.disabled'}"
      class="s-navItem">
      <v-layout row>
        <v-flex shrink>
          <v-tooltip bottom>
            <v-icon
              small
              slot="activator">admin_panel_settings</v-icon>
            Nur für Superadmins
          </v-tooltip>
        </v-flex>
        <v-flex align-self-center>
          Deaktivierte Bereiche
        </v-flex>
      </v-layout>
    </div>
    <div
      @click="select('admin.legacy')"
      :class="{'-active': selected === 'admin.legacy'}"
      class="s-navItem">
      Legacy
    </div>
    <div class="s-navArea s-profileMenu__wrapper">
      <div
        ref="profileMenuEntry"
        @click="profileMenuOpen = !profileMenuOpen"
        class="s-navArea__frontend primary--text">
        <v-icon
          color="primary"
          small>
          smartphone
        </v-icon>
        Benutzeransicht
        <div
          v-if="profiles.length > 1"
          class="s-navArea__profile">
          <span
            class="s-navArea__profileName"
            :class="{
              '-secondary': !currentProfile.is_default,
          }">
            {{ currentProfile.name }}
          </span>
          <v-icon
            size="13"
            color="black">arrow_drop_down</v-icon>
        </div>
      </div>
      <v-list
        v-if="profiles.length > 1"
        class="s-profileMenu__list"
        dense
        :class="{'-active': profileMenuOpen}">
        <v-list-tile
          v-for="profile in profiles"
          :key="profile.id"
          @click="selectProfile(profile.id)">
          <v-list-tile-title>
            {{ profile.name }}
          </v-list-tile-title>
        </v-list-tile>
      </v-list>
    </div>
    <div
      v-if="!onlyForSuperadmins('profile.general') || superadmin"
      @click="select('profile.general')"
      :class="{'-active': selected === 'profile.general'}"
      class="s-navItem">
      <v-layout row>
        <v-flex
          v-if="onlyForSuperadmins('profile.general')"
          shrink>
          <v-tooltip bottom>
            <v-icon
              small
              slot="activator">admin_panel_settings</v-icon>
            Nur für Superadmins
          </v-tooltip>
        </v-flex>
        <v-flex align-self-center>
          Allgemein
        </v-flex>
      </v-layout>
    </div>
    <div
      v-if="appSettings.module_homepage_components === '1'"
      @click="select('profile.home')"
      :class="{'-active': selected === 'profile.home'}"
      class="s-navItem">
      <v-layout row>
        <v-flex align-self-center>
          Homepage
        </v-flex>
      </v-layout>
    </div>
    <div
      @click="select('profile.contact')"
      :class="{'-active': selected === 'profile.contact'}"
      class="s-navItem">
      <v-layout row>
        <v-flex align-self-center>
          Kontaktinformationen
        </v-flex>
      </v-layout>
    </div>
    <div
      v-if="!onlyForSuperadmins('profile.modules') || superadmin"
      @click="select('profile.modules')"
      :class="{'-active': selected === 'profile.modules'}"
      class="s-navItem">
      <v-layout row>
        <v-flex
          v-if="onlyForSuperadmins('profile.modules')"
          shrink>
          <v-tooltip bottom>
            <v-icon
              small
              slot="activator">admin_panel_settings</v-icon>
            Nur für Superadmins
          </v-tooltip>
        </v-flex>
        <v-flex align-self-center>
          Module
        </v-flex>
      </v-layout>
    </div>
    <div
      v-if="!onlyForSuperadmins('profile.signup') || superadmin"
      @click="select('profile.signup')"
      :class="{'-active': selected === 'profile.signup'}"
      class="s-navItem">
      <v-layout row>
        <v-flex
          v-if="onlyForSuperadmins('profile.signup')"
          shrink>
          <v-tooltip bottom>
            <v-icon
              small
              slot="activator">admin_panel_settings</v-icon>
            Nur für Superadmins
          </v-tooltip>
        </v-flex>
        <v-flex align-self-center>
          Login & Registrierung
        </v-flex>
      </v-layout>
    </div>
    <div
      @click="select('profile.quiz')"
      :class="{'-active': selected === 'profile.quiz'}"
      class="s-navItem">
      Quiz
    </div>
    <div
      @click="select('profile.test')"
      :class="{'-active': selected === 'profile.test'}"
      class="s-navItem">
      Test
    </div>
    <div
      v-if="!onlyForSuperadmins('profile.design') || superadmin"
      @click="select('profile.design')"
      :class="{'-active': selected === 'profile.design'}"
      class="s-navItem">
      <v-layout row>
        <v-flex
          v-if="onlyForSuperadmins('profile.design')"
          shrink>
          <v-tooltip bottom>
            <v-icon
              small
              slot="activator">admin_panel_settings</v-icon>
            Nur für Superadmins
          </v-tooltip>
        </v-flex>
        <v-flex align-self-center>
          Design
        </v-flex>
      </v-layout>
    </div>
    <div
      v-if="!onlyForSuperadmins('profile.smtp') || superadmin"
      @click="select('profile.smtp')"
      :class="{'-active': selected === 'profile.smtp'}"
      class="s-navItem">
      <v-layout row>
        <v-flex
          v-if="onlyForSuperadmins('profile.smtp')"
          shrink>
          <v-tooltip bottom>
            <v-icon
              small
              slot="activator">admin_panel_settings</v-icon>
            Nur für Superadmins
          </v-tooltip>
        </v-flex>
        <v-flex align-self-center>
          SMTP Einstellungen
        </v-flex>
      </v-layout>
    </div>
    <div
      v-if="!onlyForSuperadmins('profile.notifications') || superadmin"
      @click="select('profile.notifications')"
      :class="{'-active': selected === 'profile.notifications'}"
      class="s-navItem">
      <v-layout row>
        <v-flex
          v-if="onlyForSuperadmins('profile.notifications')"
          shrink>
          <v-tooltip bottom>
            <v-icon
              small
              slot="activator">admin_panel_settings</v-icon>
            Nur für Superadmins
          </v-tooltip>
        </v-flex>
        <v-flex align-self-center>
          Notifications
        </v-flex>
      </v-layout>
    </div>
    <div
      v-if="superadmin"
      @click="select('profile.translations')"
      :class="{'-active': selected === 'profile.translations'}"
      class="s-navItem">
      <v-layout row>
        <v-flex shrink>
          <v-tooltip bottom>
            <v-icon
              small
              slot="activator">admin_panel_settings</v-icon>
            Nur für Superadmins
          </v-tooltip>
        </v-flex>
        <v-flex align-self-center>
          Übersetzungen
        </v-flex>
      </v-layout>
    </div>

    <template v-if="superadmin">
      <div class="s-navArea primary--text">
        <v-icon
          color="primary"
          small>emoji_people</v-icon> Kunde
      </div>
      <div
        @click="select('customer.info')"
        :class="{'-active': selected === 'customer.info'}"
        class="s-navItem">
        <v-layout row>
          <v-flex shrink>
            <v-tooltip bottom>
              <v-icon
                small
                slot="activator">admin_panel_settings</v-icon>
              Nur für Superadmins
            </v-tooltip>
          </v-flex>
          <v-flex align-self-center>
            Module & Mehr
          </v-flex>
        </v-layout>
      </div>
      <a
        href="/accesslogs"
        class="s-navItem">
        <v-layout row>
          <v-flex shrink>
            <v-tooltip bottom>
              <v-icon
                small
                slot="activator">admin_panel_settings</v-icon>
              Nur für Superadmins
            </v-tooltip>
          </v-flex>
          <v-flex align-self-center>
            Event Logs
          </v-flex>
        </v-layout>
      </a>
    </template>

  </div>
</template>

<script>
  import { mapGetters } from 'vuex'
  export default {
    props: [
      'isCandy',
      'profileId',
      'profiles',
      'selected',
      'superadmin',
    ],
    data() {
      return {
        profileMenuOpen: false,
      }
    },
    computed: {
      ...mapGetters({
        appSettings: 'app/appSettings',
      }),
      currentProfile() {
        return this.profiles.find(profile => profile.id === this.profileId)
      },
    },
    methods: {
      select(area) {
        this.$emit("select", area)
      },
      selectProfile(profileId) {
        this.profileMenuOpen = false
        this.$router.push({
          name: 'settings',
          params: {
            ...this.$route.params,
            profileId,
          }
        })
      },
      onlyForSuperadmins(area) {
        if(this.isCandy) {
          return false
        }
        // These areas are only accessible for superadmins in non-candy apps
        const superadminAreas = [
            'profile.general',
            'profile.modules',
            'profile.signup',
            'profile.design',
        ]
        return superadminAreas.includes(area)
      },
    },
  }
</script>

<style lang="scss" scoped>
  #app .s-sidenav {
    width: 270px;
    background: #edeff1;
    padding-top: 20px;
    padding-left: 20px;
  }

  #app .s-navArea {
    font-size: 16px;
    font-weight: bold;
    margin-top: 20px;

    &:first-child {
      margin-top: 0;
    }

    .v-icon {
      vertical-align: text-top;
      margin-right: 5px;
    }
  }

  #app .s-navArea__frontend {
    cursor: pointer;

    .v-icon {
      vertical-align: middle;
    }
  }

  #app .s-navArea__profile {
    color: #444;
    font-weight: normal;
    font-size: 11px;
    padding-left: 25px;
    margin-top: -3px;
  }

  #app .s-navArea__profileName {
    &.-secondary {
      box-shadow: 0 0 0 2px red;
      color: black;
      padding: 1px 3px 0px 3px;
      border-radius: 2px;
      margin-top: 4px;
      display: inline-block;
    }
  }

  #app .s-navItem {
    font-size: 14px;
    padding-bottom: 4px;
    padding-top: 4px;
    color: #333;
    cursor: pointer;
    padding-left: 23px;
    display: block;

    &:hover {
      color: black;
    }

    &.-active {
      font-weight: bold;
      color: black;
    }
  }

  .s-profileMenu__wrapper {
    position: relative;
  }

  .s-profileMenu__list {
    display: none;
    position: absolute;
    top: 39px;
    left: 23px;

    &.-active {
      display: block;
    }
  }
</style>
