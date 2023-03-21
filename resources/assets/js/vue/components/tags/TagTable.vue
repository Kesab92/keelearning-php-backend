<template>
  <div>
    <v-data-table
      :headers="headers"
      :items="tags"
      :loading="isLoading"
      :pagination.sync="pagination"
      :rows-per-page-items="[50, 100, 200]"
      :total-items="tagsCount"
      class="elevation-1"
      item-key="id">
      <tr
        @click="editTag(props.item.id)"
        class="clickable"
        slot="items"
        slot-scope="props">
        <td>
          {{ props.item.label }}
        </td>
        <td>
          <div
            :key="`${props.item.id}-${category.id}`"
            v-for="category in props.item.contentcategories">
            {{ category.name }}
          </div>
        </td>
        <td>
          <v-icon
            v-if="props.item.exclusive"
            color="success"
          >
            done
          </v-icon>
          <v-icon v-else>
            close
          </v-icon>
        </td>
        <td>
          {{ props.item.entryCount }}
        </td>
        <td>
          <v-icon
            v-if="!props.item.hideHighscore"
            color="success"
          >
            done
          </v-icon>
          <v-icon v-else>
            close
          </v-icon>
        </td>
        <td v-if="!appSettings.hide_tag_groups || appSettings.hide_tag_groups == 0">
          <template v-if="props.item.tag_group">
            {{ props.item.tag_group.name }}
          </template>
        </td>
        <td>
          <span class="d-block">{{ props.item.updated_at | dateTime }}</span>
        </td>
        <td>
          {{ props.item.id }}
        </td>
      </tr>
    </v-data-table>
  </div>
</template>

<script>
import {mapGetters} from "vuex";

export default {
  watch: {
    pagination: {
      handler() {
        this.loadData()
      },
      deep: true,
    },
  },
  computed: {
    ...mapGetters({
      tagsCount: 'tags/tagsCount',
      tags: 'tags/tagsList',
      isLoading: 'tags/listIsLoading',
      appSettings: 'app/appSettings'
    }),
    pagination: {
      get() {
        return this.$store.state.tags.pagination
      },
      set(data) {
        this.$store.commit('tags/setPagination', data)
      },
    },
    headers() {
      let headers = [
        {
          text: "Name",
          value: "label",
        },
        {
          text: "TAG-Kategorie",
          value: "contentcategories",
          sortable: false,
        },
        {
          text: "Exklusiv",
          value: "exclusive",
          sortable: false,
        },
        {
          text: "Verwendet",
          value: "entryCount",
          sortable: false,
        },
        {
          text: "in Statistik",
          value: "hideHighscore",
          sortable: false,
        }
      ]
      if (!this.appSettings.hide_tag_groups || this.appSettings.hide_tag_groups == 0) {
        headers.push({
          text: "TAG-Gruppe",
          value: "group",
          sortable: false,
        })
      }
      headers.push(
        {
          text: "Verändert am",
          value: "updated_at",
          width: "280px",
        },
        {
          text: "ID",
          value: "id",
          width: "90px",
        })
      return headers
    }
  },
  methods: {
    editTag(tagId) {
      this.$router.push({
        name: 'tags.edit.general',
        params: {
          tagId: tagId,
        },
      })
    },
    loadData() {
      this.$store.dispatch('tags/loadTags')
    },
  },
}
</script>
