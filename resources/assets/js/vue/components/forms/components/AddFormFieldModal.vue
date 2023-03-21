<template>
  <v-card
    v-click-outside="close"
    class="s-modal"
    :class="{
      '-up': up,
    }">
    <v-toolbar
      card
      dense>
      <v-toolbar-title class="body-2 black--text">Wählen Sie einen Formular-Baustein</v-toolbar-title>
    </v-toolbar>
    <v-layout row class="fill-height">
      <v-flex xs6>
        <div class="s-types">
          <FormFieldTypeListEntry
            v-for="type in types"
            :key="type.type"
            :type="type.type"
            :active="activeType"
            @click.native.stop="create(type.type)"
            @select="selectType"/>
        </div>
      </v-flex>
      <v-flex xs6 style="align-items: stretch;">
        <div class="s-details">
          <component :is="typeExplanationComponent"/>
        </div>
      </v-flex>
    </v-layout>
  </v-card>
</template>

<script>
import ClickOutside from 'vue-click-outside'
import FormFieldTypeListEntry from "./add-form-field-modal/FormFieldTypeListEntry"
import TypeExplanationTextarea from "./add-form-field-modal/TypeExplanationTextarea"
import TypeExplanationRating from "./add-form-field-modal/TypeExplanationRating"
import TypeExplanationHeader from "./add-form-field-modal/TypeExplanationHeader"
import TypeExplanationSeparator from "./add-form-field-modal/TypeExplanationSeparator"
import constants from "../../../logic/constants"

export default {
  props: {
    up: {
      default: false,
      type: Boolean,
    },
  },
  data() {
    return {
      activeType: null,
      types: [
        {
          type: constants.FORMS.TYPE_HEADER,
        },
        {
          type: constants.FORMS.TYPE_SEPARATOR,
        },
        {
          type: constants.FORMS.TYPE_RATING,
        },
        {
          type: constants.FORMS.TYPE_TEXTAREA,
        },
      ]
    }
  },
  created() {
    this.activeType = this.$constants.FORMS.TYPE_TEXTAREA
  },
  computed: {
    typeExplanationComponent() {
      const map = {
        [constants.FORMS.TYPE_TEXTAREA]: TypeExplanationTextarea,
        [constants.FORMS.TYPE_RATING]: TypeExplanationRating,
        [constants.FORMS.TYPE_HEADER]: TypeExplanationHeader,
        [constants.FORMS.TYPE_SEPARATOR]: TypeExplanationSeparator,
      }
      return map[this.activeType]
    },
  },
  methods: {
    selectType(type) {
      this.activeType = type
    },
    create(type) {
      this.$emit('create', type)
    },
    close() {
      // We do this next tick, to give the modal enough time to emit any events
      this.$nextTick(() => {
        this.$emit('close')
      })
    },
  },
  directives: {
    ClickOutside,
  },
  components: {
    FormFieldTypeListEntry,
  },
}
</script>

<style lang="scss" scoped>
#app .s-modal {
  cursor: default;
  height: 223px;
  left: 30px;
  pointer-events: all;
  position: absolute;
  top: 65px;
  width: 400px;
  z-index: 5;
  overflow: hidden;

  &.-up {
    bottom: 65px;
    top: auto;
  }
}

.s-types {
  height: 100%;
  overflow-y: auto;
}

#app .s-details {
  background: #f1f1f1;
  padding: 12px 24px;
  border-left: 1px solid #dedede;
  width: 100%;
  height: 100%;
}
</style>
