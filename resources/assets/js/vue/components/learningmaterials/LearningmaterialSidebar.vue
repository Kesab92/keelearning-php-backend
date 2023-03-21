<template>
  <details-sidebar
    :root-url="{
      name: rootUrl,
      params: {
        folderId: this.$route.params.folderId,
      }
    }"
    :drawer-open="(typeof $route.params.learningmaterialId) !== 'undefined'"
    data-action="learningmaterials/loadLearningmaterial"
    :data-getter="(params) => $store.getters['learningmaterials/material'](params.learningmaterialId)"
    :data-params="{learningmaterialId: $route.params.learningmaterialId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: learningmaterial, refresh }">
      <router-view
        :material="learningmaterial"
        @refresh="refresh"
      />
    </template>
    <template v-slot:headerTitle="{ data: learningmaterial }">
      {{ learningmaterial.title }}
    </template>
    <template v-slot:headerExtension="{ data: learningmaterial }">
      Typ: Datei<br>
      Erstellt am {{ learningmaterial.created_at | date }}<br>
      <v-tooltip bottom>
        <template slot="activator">
          <span>
            <v-icon small>visibility</v-icon>
            {{ learningmaterial.viewcount_total }}
          </span>
        </template>
        <span>Aufrufe</span>
      </v-tooltip>
      <v-tooltip bottom>
        <template slot="activator">
          <span>
            <v-icon class="ml-2" small>favorite</v-icon>
            {{ learningmaterial.likes }}
          </span>
        </template>
        <span>Likes</span>
      </v-tooltip>
    </template>
  </details-sidebar>
</template>

<script>
import {mapGetters} from 'vuex'

export default {
  props: {
    rootUrl: {
      default: 'learningmaterials.index',
      required: false,
      type: String,
    },
    routePrefix: {
      default: '',
      required: false,
      type: String,
    },
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
      myRights: 'app/myRights',
    }),
  },
  methods: {
    getLinks(learningmaterial) {
      let tabs = [
        {
          label: 'Allgemein',
          to: {
            name: `${this.routePrefix}learningmaterials.edit.general`,
            params: {
              folderId: learningmaterial.learning_material_folder_id,
              learningmaterialId: learningmaterial.id,
            },
          },
        },
        {
          label: 'Design',
          to: {
            name: `${this.routePrefix}learningmaterials.edit.design`,
            params: {
              folderId: learningmaterial.learning_material_folder_id,
              learningmaterialId: learningmaterial.id,
            },
          },
        },
        {
          label: 'Beschreibung',
          to: {
            name: `${this.routePrefix}learningmaterials.edit.description`,
            params: {
              folderId: learningmaterial.learning_material_folder_id,
              learningmaterialId: learningmaterial.id,
            },
          },
        },
        {
          label: 'Verknüpfungen',
          to: {
            name: `${this.routePrefix}learningmaterials.edit.usages`,
            params: {
              folderId: learningmaterial.learning_material_folder_id,
              learningmaterialId: learningmaterial.id,
            },
          },
        },
      ]
      if (this.appSettings.module_comments == 1 && this.myRights['comments-personaldata']) {
        tabs.push({
          label: 'Kommentare',
          to: {
            name: `${this.routePrefix}learningmaterials.edit.comments`,
            params: {
              folderId: learningmaterial.learning_material_folder_id,
              learningmaterialId: learningmaterial.id,
            },
          },
        })
      }
      return tabs
    }
  },
}
</script>
