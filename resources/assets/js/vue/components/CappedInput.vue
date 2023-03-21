<template>
    <div
        class="capwrap"
        :class="{
            big: !!big,
        }"
    >
        <textarea
            class="captext"
            :class="classes"
            :name="name"
            :maxlength="threshold"
            :placeholder="placeholder"
            :rows="rows?rows:3"
            v-model="value"
        ></textarea>
        <div
            v-if="warning || threshold"
            class="capindicator"
            :class="{
                warning  : warning && stringlength >= warning,
                threshold: threshold && stringlength >= threshold,
            }"
        >
            <div :style="'width:' + (stringlength / threshold * 100) + '%'"></div>
        </div>
        <div
            class="latex-error"
            v-show="latexError"
        >
            {{ latexError }}
        </div>
        <div
          v-if="untranslated"
          class="original-phrase"
        >
          <p>
            <span class="float-right">{{ defaultLanguageEmoji }}</span>
              {{ untranslated }}
          </p>
        </div>
    </div>
</template>

<script>
export default {
    props: [
        'big',
        'content',
        'maxlength',
        'name',
        'placeholder',
        'rows',
        'untranslated',
        'warnlength',
    ],
    data() {
        return {
            threshold: 300,
            value: '',
            warning: 80,
        }
    },
    created() {
        if (this.content) {
            this.value = this.content
        }
        if (typeof this.warnlength !== 'undefined') {
            this.warning = this.warnlength
        }
        if (typeof this.maxlength !== 'undefined') {
            this.threshold = this.maxlength
        }
    },
    computed: {
        classes() {
            let classes = {}
            classes[this.name] = true
            if (this.latexError) {
                classes.invalid = true
            }
            return classes
        },
        defaultLanguageEmoji() {
          return document.head.querySelector('meta[name="app-default-language-emoji"]').content
        },
        latexError() {
            if (typeof window.katex === 'undefined') {
                return null
            }
            if (this.value.indexOf('/latex') !== 0) {
                return null
            }
            try {
                window.katex.renderToString(this.value.substring('/latex'.length).trim())
            } catch (error) {
                return error.message.replace('KaTeX parse error: ', 'Fehler: ')
            }
            return null
        },
        stringlength() {
            if (!this.value) {
                return 0
            }
            return this.value.length
        },
    },
}
</script>

<style scoped lang="scss">
.capwrap {
    font-size: 14px;
    margin: 8px 0;
    padding-bottom: 3px;
    position: relative;

    &.big {
        font-size: 16px;
        margin: 0;
        padding-bottom: 0;

        .capindicator {
            height: 5px;
            margin: 0;
        }
    }
}

.capindicator {
    background-color: #dddddd;
    height: 2px;
    margin: 0 3px;
    position: relative;

    div {
        background-color: #8bc34a;
        position: absolute;
        bottom: 0;
        left: 0;
        top: 0;
        transition: background-color 0.3s ease;
    }

    &.warning div {
        background-color: orange;
    }

    &.threshold div {
        background-color: red;
    }
}

.captext {
    border: 1px solid rgba(34, 36, 38, 0.15);
    display: block;
    font-size: inherit;
    line-height: 1.4;
    outline: none;
    padding: 0.5rem !important;
    resize: none;
    transition: border-color 0.3s ease;
    width: 100%;

    &.invalid {
        border-color: red !important;
    }
}

.latex-error {
    color: red;
    font-size: 12px;
    margin: 4px;
}
</style>
