<template>
  <details-sidebar
    :root-url="{
      name: rootUrl,
    }"
    :drawer-open="(typeof $route.params.formId) !== 'undefined'"
    data-action="forms/loadForm"
    :data-getter="(params) => $store.getters['forms/form'](params.formId)"
    :data-params="{formId: $route.params.formId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: form, refresh, refreshSilently }">
      <router-view
        :form="form"
        @refresh="refresh"
        @refresh-silently="refreshSilently" />
    </template>
    <template v-slot:headerTitle="{ data: form }">
      {{ form.title }}
    </template>
    <template v-slot:headerExtension="{ data: form }">
      <DraftLabel
        class="mb-2"
        :is-draft="!!form.is_draft"/>
      Bearbeitet: {{ form.updated_at | date }}, {{ form.last_updated_by_username }}<br>
      Erstellt am: {{ form.created_at | date }}, {{ form.created_by_username }}
    </template>
  </details-sidebar>
</template>

<script>

import {mapGetters} from "vuex"
import DraftLabel from "../partials/global/DraftLabel"
export default {
  props: {
    rootUrl: {
      default: 'forms.index',
      required: false,
      type: String,
    },
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    form() {
      return this.$store.getters['forms/form'](this.$route.params.formId)
    },
  },
  methods: {
    getLinks(form) {
      const tabs = [
        {
          label: 'Allgemein',
          to: {
            name: `forms.edit.general`,
            params: {
              formId: form.id,
            },
          },
        },
        {
          label: 'Verknüpfungen',
          to: {
            name: `forms.edit.usages`,
            params: {
              formId: form.id,
            },
          },
        },
      ]
      if (this.myRights['forms-stats']) {
        tabs.push({
          label: 'Antworten',
          to: {
            name: `forms.edit.stats`,
            params: {
              formId: form.id,
            },
          },
        })
      }
      return tabs
    }
  },
  components: {
    DraftLabel,
  },
}
</script>
