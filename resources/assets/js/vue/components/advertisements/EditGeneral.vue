<template>
  <v-form
    v-if="advertisementData"
    v-model="isValid">
    <details-sidebar-toolbar>
      <v-btn
        :disabled="!isValid"
        :loading="isSaving"
        color="primary"
        @click="save"
      >
        Speichern
      </v-btn>
      <v-spacer/>
      <v-btn
        :loading="isSaving"
        color="red"
        outline
        @click="remove"
      >
        Löschen
      </v-btn>
    </details-sidebar-toolbar>

    <div class="pa-4">
      <v-text-field
        v-model="advertisementData.name"
        browser-autocomplete="chrome-off"
        hint="Dieser Name ist für die Benutzer nicht sichtbar"
        label="Name"
        outline/>

      <v-layout
        class="mb-4"
        row>
        <v-flex shrink>
          <v-switch
            v-model="advertisementData.visible"
            class="s-visibilitySwitch"
            height="30"
            hide-details/>
        </v-flex>
        <v-flex align-self-center>
          Sichtbar
        </v-flex>
      </v-layout>

      <v-alert
        v-if="isOnLogin"
        :value="true"
        class="mb-4"
        color="info"
        icon="info"
        outline>
        Ein Banner, der auf der Login-Seite sichtbar ist, kann nicht über Benutzergruppen gesteuert werden.
      </v-alert>
      <tag-select
        v-else
        v-model="advertisementData.tags"
        color="blue-grey lighten-2"
        label="Sichtbar für folgende User"
        multiple
        outline
        placeholder="Alle"
      />

      <v-autocomplete
        ref="positionSelect"
        v-model="advertisementData.positions"
        :items="positions"
        :menu-props="{
          contentClass: 's-positionDropdown',
          closeOnClick: false,
          closeOnContentClick: false,
          openOnClick: false,
          maxHeight: 400,
        }"
        deletable-chips
        dense
        label="Positionen"
        multiple
        outline
        placeholder="Wo dieser Banner angezeigt werden soll"
        small-chips>
        <template v-slot:item="data">
          <v-list-tile-content>
            <v-list-tile-title><strong>{{ data.item.text }}</strong></v-list-tile-title>
            <v-list-tile-sub-title v-if="data.item.onlyMobileImage">Zeigt nur mobile Grafik</v-list-tile-sub-title>
          </v-list-tile-content>
        </template>
      </v-autocomplete>

      <v-alert
        v-if="advertisementData.is_ad"
        :value="true"
        color="warning"
        icon="priority_high"
        outline
        class="mb-2">
        Bitte stellen Sie sicher, dass Ihre Werbung den App-Store Richtlinien entspricht, wenn diese dort zu sehen
        ist:<br>
        <a
          href="https://support.google.com/googleplay/android-developer/answer/9857753?hl=en"
          target="_blank">Apple Richtlinien</a>,
        <a
          href="https://searchads.apple.com/policies/"
          target="_blank">Google Richtlinien</a>
      </v-alert>
      <v-layout
        align-center
        class="mb-4"
        row>
        <v-flex shrink>
          <v-switch
            v-model="advertisementData.is_ad"
            class="s-visibilitySwitch"
            height="30"
            hide-details/>
        </v-flex>
        <v-flex shrink>
          Bei diesem Banner handelt es sich um Werbung
        </v-flex>
        <v-flex
          ref="adInfo"
          v-click-outside="closeAdInfoTooltip"
          shrink
          @click="openAdInfoTooltip"
        >
          <div
            class="pa-3"
            style="cursor: pointer;">
            <v-icon>info</v-icon>
          </div>
          <v-tooltip
            :activator="$refs.adInfo"
            :value="adInfoToolip"
            bottom
            nudge-top="20"
            content-class="black v-tooltip__content--solid body-2"
            max-width="300">
            <div>
              Werbeanzeigen werden in der App mit "Werbung" markiert und müssen den Richtlinien entsprechen.
            </div>
          </v-tooltip>
        </v-flex>
        <v-spacer/>
      </v-layout>

      <ImageUploader
        :current-image="advertisementData.rectangle_image_url"
        :url="`/backend/api/v1/advertisements/assets/rectangle/${advertisementData.id}`"
        :validate-file="validateRectangle"
        class="mb-4"
        description="<br>Breite: 300px<br>Höhe: 90-250px"
        max-height="250px"
        name="Grafik (Mobil)"
        width="300px"
        @newImage="handleNewRectangle"
      />

      <ImageUploader
        :current-image="advertisementData.leaderboard_image_url"
        :url="`/backend/api/v1/advertisements/assets/leaderboard/${advertisementData.id}`"
        :validate-file="validateLeaderboard"
        class="mb-4"
        description="<br>Breite: 728px<br>Höhe: 90-180px"
        max-height="180px"
        name="Grafik (Desktop)"
        width="728px"
        @newImage="handleNewLeaderboard"
      />

      <translated-input
        v-model="advertisementData.link"
        :translations="advertisementData.translations"
        :rules="[$rules.url]"
        placeholder="https://ihre.domain.com"
        attribute="link"
        class="mb-4"
        label="Link"/>

      <translated-input
        v-model="advertisementData.description"
        input-type="texteditor"
        label="Text Inhalt"
        :translations="advertisementData.translations"
        attribute="description" />

      <DeleteDialog
        v-model="deleteDialogOpen"
        :deletion-url="`/backend/api/v1/advertisements/${advertisementData.id}`"
        :dependency-url="`/backend/api/v1/advertisements/${advertisementData.id}/delete-information`"
        :entry-name="advertisementData.name"
        :redirect-url="afterDeletionRedirectURL"
        type-label="Banner"
        @deleted="handleAdvertisementDeleted"/>
    </div>
  </v-form>
</template>

<script>
import DeleteDialog from "../partials/global/DeleteDialog"
import TagSelect from "../partials/global/TagSelect"
import ImageUploader from "../partials/global/ImageUploader"
import helpers from "../../logic/helpers"
import * as advertisements from "./advertisements"
import ClickOutside from "vue-click-outside"

export default {
  props: ["advertisement"],
  data() {
    return {
      advertisementData: null,
      isSaving: false,
      isValid: true,
      deleteDialogOpen: false,
      adInfoToolip: false,
    }
  },
  watch: {
    advertisement: {
      handler() {
        this.advertisementData = JSON.parse(JSON.stringify(this.advertisement))
      },
      immediate: true,
    },
  },
  computed: {
    afterDeletionRedirectURL() {
      return "/advertisements#/advertisements"
    },
    positions() {
      const labels = advertisements.getPositionLabels()
      const data = []
      const onlyMobileImage = [
        this.$constants.ADVERTISEMENTS.POSITION_LOGIN,
        this.$constants.ADVERTISEMENTS.POSITION_NEWS,
        this.$constants.ADVERTISEMENTS.POSITION_POWERLEARNING,
      ]
      advertisements.getOrderedPositions().forEach(key => {
        data.push({
          value: key,
          text: labels[key],
          onlyMobileImage: onlyMobileImage.includes(key),
        })
      })
      return data
    },
    isOnLogin() {
      return this.advertisementData.positions.includes(this.$constants.ADVERTISEMENTS.POSITION_LOGIN)
    },
  },
  methods: {
    async save() {
      if (this.isSaving) {
        return
      }
      if (this.advertisementData.visible && !this.advertisementData.rectangle_image_url && !this.advertisementData.description) {
        alert("Bitte fügen Sie einen Text Inhalt oder eine mobile Grafik ein, bevor Sie den Banner sichtbar schalten.")
        return
      }
      if (this.advertisementData.visible && !this.advertisementData.positions) {
        alert("Bitte wählen Sie mindestens eine Position an der der Banner erscheinen soll.")
        return
      }
      let tags = this.advertisementData.tags
      if (this.isOnLogin) {
        tags = []
      }
      this.isSaving = true
      await this.$store.dispatch("advertisements/saveAdvertisement", {
        id: this.advertisementData.id,
        name: this.advertisementData.name,
        description: this.advertisementData.description,
        tags,
        positions: this.advertisementData.positions,
        is_ad: this.advertisementData.is_ad,
        visible: this.advertisementData.visible,
        link: this.advertisementData.link,
        rectangle_image_url: this.advertisementData.rectangle_image_url,
        leaderboard_image_url: this.advertisementData.leaderboard_image_url,
      })
      this.isSaving = false
    },
    remove() {
      this.deleteDialogOpen = true
    },
    handleAdvertisementDeleted() {
      this.$store.commit("advertisements/deleteAdvertisement", this.advertisementData.id)
      this.$store.dispatch("advertisements/loadAdvertisements")
    },
    handleNewRectangle(image) {
      this.advertisementData.rectangle_image_url = image
    },
    handleNewLeaderboard(image) {
      this.advertisementData.leaderboard_image_url = image
    },
    async validateRectangle(file, done) {
      const dimensions = await helpers.getImageDimensions(file)
      if (dimensions.height < 90 || dimensions.height > 250) {
        done("Das Bild muss zwischen 90-250px hoch sein.")
      } else if (dimensions.width !== 300) {
        done("Das Bild muss 300px breit sein.")
      } else {
        done()
      }
    },
    async validateLeaderboard(file, done) {
      const dimensions = await helpers.getImageDimensions(file)
      if (dimensions.height < 90 || dimensions.height > 180) {
        done("Das Bild muss zwischen 90-180px hoch sein.")
      } else if (dimensions.width !== 728) {
        done("Das Bild muss 728px breit sein.")
      } else {
        done()
      }
    },
    openAdInfoTooltip() {
      this.adInfoToolip = true
    },
    closeAdInfoTooltip(e) {
      if(e.target.closest('.v-tooltip__content')) {
        return
      }
      this.adInfoToolip = false
    },
  },
  components: {
    ImageUploader,
    TagSelect,
    DeleteDialog,
  },
  directives: {
    ClickOutside,
  },
}
</script>

<style lang="scss" scoped>
#app .s-visibilitySwitch {
  margin-top: 0;
  padding-top: 0;
}

</style>

<style lang="scss">

#app .s-positionDropdown {
  .v-list__tile__content {
    padding: 5px 0;
  }

  .v-list__tile {
    border-bottom: 1px solid #dedede;
    height: auto !important;
  }

  .v-list__tile--active {
    border-left: 4px solid #1976D3 !important;
    padding-left: 12px;
    background: #f3f3f3;
  }
}
</style>
