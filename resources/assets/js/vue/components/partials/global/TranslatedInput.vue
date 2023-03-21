<template>
  <div class="s-inputWrapper">
    <div
      class="s-defaultTranslation__wrapper"
      v-if="showOnlyOriginal">
      <div class="s-defaultTranslation__label">
        {{ label }}
      </div>
      <slot name="default-translation">
        <div class="s-defaultTranslation">
          <div
            v-if="defaultTranslationContent"
            v-html="defaultTranslationContent" />
          <div
            v-else-if="placeholder"
            v-html="placeholder" />
          <div
            v-else
            class="grey--text">n/a</div>
        </div>
      </slot>
      <div class="s-defaultTranslation__description">
        <div class="s-defaultTranslation__explainer">Originalinhalt</div> {{ defaultLanguage.toLocaleUpperCase() }} |
        <a
          @click.prevent="enableForceTranslation"
          href="#">
          Überschreiben
        </a>
      </div>
    </div>
    <slot
      v-else
      name="override-translation">
      <div
        @blur="handleWrapperBlur"
        @focus="handleWrapperFocus"
        tabindex="0"
        class="s-inputEditWrapper"
        :class="{
          '-focused': inputFocus,
      }">
        <div
          v-if="inputType === 'texteditor'"
          class="subheading">{{ inputLabel }}</div>
        <v-text-field
          v-if="inputType === 'input'"
          class="s-input"
          ref="input"
          v-model="model"
          v-bind="$attrs"
          :background-color="backgroundColor"
          outline
          :label="inputLabel"
          browser-autocomplete="chrome-off"
          :hide-details="!$attrs.rules || !$attrs.rules.length"
          :disabled="readOnly"
          :placeholder="placeholder"
          @blur="handleInputBlur"
          @focus="handleInputFocus"
        />
        <v-textarea
          v-else-if="inputType === 'textarea'"
          class="s-input"
          ref="input"
          v-model="model"
          v-bind="$attrs"
          :background-color="backgroundColor"
          outline
          :label="inputLabel"
          browser-autocomplete="chrome-off"
          :hide-details="!$attrs.rules || !$attrs.rules.length"
          :disabled="readOnly"
          :placeholder="placeholder"
          @blur="handleInputBlur"
          @focus="handleInputFocus"
        />
        <TextEditor
          v-else-if="inputType === 'texteditor'"
          v-model="model"
          v-bind="$attrs"
          class="s-input"
          ref="input"
          :height="height"
          @blur="handleInputBlur"
          :disabled="readOnly"
          @focus="handleInputFocus"
        />
        <v-sheet
          :elevation="14"
          v-if="isForeignLanguage && (wrapperFocus || inputFocus)"
          :style="{
            paddingTop: sheetPadding + 'px',
            marginTop: sheetMargin + 'px',
          }"
          class="s-defaultText"
        >
          <div class="s-defaultText__label">
            {{ label }} ({{ defaultLanguage }})
          </div>
          <div
            class="s-defaultText__content"
            v-html="defaultTranslationContent"/>
        </v-sheet>
      </div>
    </slot>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import TextEditor from "./TextEditor"

export default {
  inheritAttrs: false,
  components: {
    TextEditor,
  },
  props: {
    label: {
      type: String,
      required: true,
    },
    value: {
      type: String,
      required: false,
      default: null,
    },
    attribute: {
      type: String,
      required: true,
    },
    translations: {
      type: Array,
      required: true,
    },
    inputType: {
      type: String,
      default: 'input',
      required: false,
    },
    translationContainsValue: {
      type: Function,
      default: null,
      required: false,
    },
    height: {
      type: Number,
      default: 300,
      required: false,
    },
    readOnly: {
      type: Boolean,
      required: false,
      default: false,
    },
    placeholder: {
      type: String,
      required: false,
      default: null,
    },
    backgroundColor: {
      type: String,
      required: false,
      default: '',
    },
  },
  data() {
    return {
      forceTranslation: false,
      inputFocus: false,
      wrapperFocus: false,
      sheetPadding: 0,
      sheetMargin: 0,
    }
  },
  watch: {
    showOnlyOriginal: {
      handler() {
        if(!this.showOnlyOriginal) {
          this.$nextTick(() => {
            this.sheetMargin = 0
            if(this.inputType === 'input' || this.inputType === 'textarea') {
              this.sheetPadding = this.$refs.input.$el.offsetHeight
            } else if(this.inputType === 'texteditor') {
              let that = this
              this.getTinymceEditor().on('init', (e) => {
                that.sheetPadding = e.target.getContainer().offsetHeight
                that.sheetMargin = e.target.getContainer().offsetTop
              })
            }
          })
        }
      },
      immediate: true,
    },
    forceTranslation() {
      this.$emit('forceTranslation')
    },
  },
  computed: {
    ...mapGetters({
      activeLanguage: 'languages/activeLanguage',
      defaultLanguage: 'languages/defaultLanguage',
    }),
    model: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      },
    },
    showOnlyOriginal() {
      if(!this.isForeignLanguage) {
        return false
      }
      return !this.isTranslated && !this.forceTranslation
    },
    isTranslated() {
      const translation = this.translations.find(translation => translation.language === this.activeLanguage)
      if(!translation) {
        return false
      }
      if(this.translationContainsValue) {
        return this.translationContainsValue(translation)
      } else {
        return !!translation[this.attribute]
      }
    },
    isForeignLanguage() {
      return this.activeLanguage !== this.defaultLanguage
    },
    defaultTranslationContent() {
      let translation = this.translations.find(translation => translation.language === this.defaultLanguage)
      if(!translation) {
        return ''
      }
      return translation[this.attribute]
    },
    inputLabel() {
      let label = this.label
      if(this.isForeignLanguage) {
        label += ' (' + this.activeLanguage + ')'
      }
      return label
    },
  },
  methods: {
    getTinymceEditor() {
      return this.$refs.input.$refs.editor.editor
    },
    enableForceTranslation() {
      this.forceTranslation = true
      this.model = ''
      this.$nextTick(() => {
        if(this.inputType === 'input' || this.inputType === 'textarea') {
          this.$refs.input.$el.querySelector('input').focus()
        } else if(this.inputType === 'texteditor') {
          this.$refs.input.$refs.editor.editor.on('init', (e) => {
            e.target.focus()
          })
        }
      })
    },
    handleWrapperBlur() {
      this.wrapperFocus = false
    },
    handleWrapperFocus() {
      this.wrapperFocus = true
    },
    handleInputBlur() {
      this.inputFocus = false
    },
    handleInputFocus() {
      this.inputFocus = true
    },
  },
}
</script>

<style lang="scss" scoped>
#app {
  .s-inputWrapper {
    position: relative;
  }

  .s-input {
    position: relative;
    z-index: 2;

    .-focused & ::v-deep .v-input__slot {
      border-bottom-right-radius: 0;
      border-bottom-left-radius: 0;
    }
  }

  .s-defaultTranslation__wrapper {
    border: 2px dashed #dedede;
    padding: 10px;
    border-radius: 3px;
    position: relative;
  }

  .s-defaultTranslation__label {
    padding: 0 2px;
    background: white;
    position: absolute;
    top: -11px;
    left: 8px;
    color: rgba(0, 0, 0, 0.54);
  }

  .s-defaultTranslation {
    max-height: 300px;
    overflow-y: auto;
  }

  .s-defaultTranslation__explainer {
    width: 0;
    overflow: hidden;
    display: inline-block;
    transition: width .1s ease;
    vertical-align: top;
  }

  .s-defaultTranslation__description {
    padding: 0 2px;
    background: white;
    position: absolute;
    top: -11px;
    right: 8px;
    color: rgba(0, 0, 0, 0.54);

    &:hover {
      .s-defaultTranslation__explainer {
        width: 77px;
      }
    }
  }

  .s-defaultText {
    position: absolute;
    z-index: 1;
    top: 0;
    padding: 70px 15px 15px 15px;
    width: 100%;
  }

  .s-defaultText__label {
    margin-top: 15px;
    height: 20px;
    line-height: 20px;
    font-size: 12px;
    color: rgba(0, 0, 0, 0.54);
  }

  .s-defaultText__content {
    max-height: 300px;
    overflow-y: auto;
  }

  .s-inputEditWrapper {
    outline: none;

    ::v-deep .tox.tox-tinymce {
      position: relative;
      border: 2px solid #757575;
      z-index: 2;
      border-top-left-radius: 4px;
      border-top-right-radius: 4px;
    }

    &.-focused ::v-deep .tox.tox-tinymce {
      border: 2px solid #1976d2;
    }
  }
}
</style>
