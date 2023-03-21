<template>
  <v-data-table
    :headers="headers"
    :items="advertisements"
    :loading="isLoading"
    :pagination.sync="pagination"
    :rows-per-page-items="[50, 100, 200]"
    :total-items="advertisementsCount"
    class="elevation-1 advertisements-table"
    item-key="id">
    <tr
      @click="editAdvertisement(props.item.id)"
      class="clickable"
      slot="items"
      slot-scope="props">
      <td class="pa-2 pr-0">
        <img
          v-if="props.item.rectangle_image_url"
          class="s-advertisement__coverImage"
          :src="props.item.rectangle_image_url" >
      </td>
      <td>
        {{ props.item.name }}
      </td>
      <td>
        <v-chip
          :key="`${props.item.id}-${tag.id}`"
          disabled
          small
          v-for="tag in props.item.tags">
          {{ tag.label }}
        </v-chip>
      </td>
      <td>
        <v-chip
          :key="`${props.item.id}-${position}`"
          disabled
          small
          v-for="position in props.item.positions">
          {{ getPositionLabel(position) }}
        </v-chip>
      </td>
      <td>
        <v-icon v-if="props.item.visible">visibility</v-icon>
        <v-icon v-else>visibility_off</v-icon>
      </td>
      <td>
        {{ props.item.id }}
      </td>
    </tr>
    <template slot="no-data">
      <v-alert
        :value="true"
        type="info"
        v-show="(!advertisements || advertisements.length === 0) && !isLoading">
        Es wurden keine Banner gefunden.
      </v-alert>
    </template>
    <template slot="actions-prepend">
      <div class="page-select">
        Page:
        <v-select
          :items="pageSelectOptions"
          v-model="pagination.page"
          class="pagination" />
      </div>
    </template>
  </v-data-table>
</template>


<script>
import {debounce} from "lodash"
import {getPositionLabel} from "./advertisements"
import { mapGetters } from "vuex"

export default {
  data() {
    return {
      headers: [
        {
          text: "",
          value: "image",
          width: "110px",
          sortable: false,
        },
        {
          text: "Name",
          value: "name",
          width: "200px",
        },
        {
          text: "Benutzergruppen",
          value: "tags",
          sortable: false,
        },
        {
          text: "Positionen",
          value: "positions",
          sortable: false,
        },
        {
          text: "Sichtbar",
          value: "visible",
          width: "100px",
        },
        {
          text: "ID",
          value: "id",
          width: "90px",
        },
      ],
    }
  },
  watch: {
    pagination: {
      handler() {
        this.loadData()
      },
      deep: true,
    },
    search: debounce(function () {
      this.loadData()
    }, 500),
    selectedTags() {
      this.loadData()
    },
  },
  computed: {
    ...mapGetters({
      advertisementsCount: 'advertisements/advertisementsCount',
      advertisements: 'advertisements/advertisements',
      isLoading: 'advertisements/listIsLoading'
    }),
    pagination: {
      get() {
        return this.$store.state.advertisements.pagination
      },
      set(data) {
        this.$store.commit('advertisements/setPagination', data)
      },
    },
    pageSelectOptions() {
      if (!this.advertisementsCount || !this.pagination.rowsPerPage) {
        return [1]
      }
      const max = Math.ceil(this.advertisementsCount / this.pagination.rowsPerPage)
      const options = []
      for (let i = 1; i <= max; i++) {
        options.push(i)
      }
      return options
    },
  },
  methods: {
    editAdvertisement(advertisementId) {
      this.$router.push({
        name: 'advertisements.edit.general',
        params: {
          advertisementId,
        },
      })
    },
    loadData() {
      this.$store.dispatch('advertisements/loadAdvertisements')
    },
    getPositionLabel(position) {
      return getPositionLabel(position)
    },
  },
}
</script>

<style lang="scss">

#app .advertisements-table {
  .v-datatable__actions__select {
    max-width: 180px;
  }

  .page-select {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-right: 14px;
    height: 58px; // IE11 fix
    margin-bottom: -6px;
    color: rgba(0, 0, 0, 0.54);

    // IE11 fixes
    .v-select__slot, .v-select__selections {
      height: 32px;
    }

    .v-select {
      flex: 0 1 0;
      margin: 13px 0 13px 34px;
      font-size: 12px;
    }
  }
}

#app .s-advertisement__coverImage {
  width: 110px;
  display: block;
  height: 100%;
  min-height: 70px;
  max-height: 150px;
  object-fit: cover;
}
</style>
