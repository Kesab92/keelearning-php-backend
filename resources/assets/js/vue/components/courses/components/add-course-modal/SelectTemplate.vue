<template>
  <div>
    <div class="subheading mb-2">
      Eigene Vorlagen
    </div>
    <v-layout row wrap>
      <v-flex
        class="s-template"
        @click="setTemplateId(0)"
        xs4 px-3 py-2>
        <div class="s-template__image">
          <img src="/img/template-placeholder.png">
        </div>
        <template v-if="createTemplate">
          Neue Kurs-Vorlage
        </template>
        <template v-else>
          Neuer Kurs
        </template>
      </v-flex>
    </v-layout>
    <v-layout v-if="templates.local.length" row wrap>
      <v-flex
        v-for="template in templates.local"
        :key="template.id"
        class="s-template"
        @click="setTemplateId(template.id)"
        xs4 px-3 py-2>
        <div class="s-template__image">
          <img :src="template.cover_image_url ? template.cover_image_url : '/img/no-connection.svg'">
        </div>
        {{ template.title }}
      </v-flex>
    </v-layout>
    <template v-if="templates.thirdParty && templates.thirdParty.length">
      <hr class="my-4">
      <div class="subheading mb-2">
        Externe Vorlagen
      </div>
      <v-layout row wrap>
        <v-flex
          v-for="template in templates.thirdParty"
          :key="template.id"
          class="s-template"
          @click="setTemplateId(template.id)"
          xs4 px-3 py-2>
          <div class="s-template__image">
            <img :src="template.cover_image_url || '/img/no-connection.svg'">
          </div>
          {{ template.title }}
        </v-flex>
      </v-layout>
    </template>
    <template v-if="templates.global.length">
      <hr class="my-4">
      <div class="subheading mb-2">
        keelearning Vorlagen
      </div>
      <v-layout row wrap>
        <v-flex
          v-for="template in templates.global"
          :key="template.id"
          class="s-template"
          @click="setTemplateId(template.id)"
          xs4 px-3 py-2>
          <div class="s-template__image">
            <img :src="template.cover_image_url || '/img/no-connection.svg'">
          </div>
          {{ template.title }}
        </v-flex>
      </v-layout>
    </template>
  </div>
</template>

<script>
export default {
  props: [
    'createTemplate',
    'templates',
    'value',
  ],
  methods: {
    setTemplateId(id) {
      this.$emit('input', id)
    },
  },
}
</script>

<style lang="scss" scoped>
#app {
  .s-template {
    cursor: pointer;
  }

  .s-template__image {
    position: relative;

    &::after {
      content: '';
      display: block;
      padding-bottom: 50%;
      width: 100%;
    }

    img {
      bottom: 0;
      height: 100%;
      left: 0;
      object-fit: cover;
      position: absolute;
      right: 0;
      top: 0;
      width: 100%;

      &[src$=".svg"] {
        object-fit: fill;
      }
    }
  }
}
</style>
