<template>
    <v-data-table
      :headers="headers"
      :items="items"
      :loading="isLoading"
      :pagination.sync="paginationData"
      hide-actions
      class="elevation-0"
      :class="{'withScrollbar': stickyHeader}"
      v-bind="$attrs"
    >
      <template #items="scope">
        <slot name="items" v-bind="scope" />
      </template>
    </v-data-table>
</template>

<script>

export default {
  inheritAttrs: false,
  props: {
    headers: {
      type: Array,
      required: true,
    },
    items: {
      type: Array,
      required: true,
    },
    isLoading: {
      type: Boolean,
      required: false,
      default: false,
    },
    stickyHeader: {
      type: Boolean,
      required: false,
      default: false,
    },
    pagination:{
      type: Object,
      required: false,
      default: {},
    },
  },
  data () {
    return {
      paginationData: {},
    }
  },
  created() {
    this.paginationData = JSON.parse(JSON.stringify(this.pagination))
  },
}
</script>

<style lang="scss">
#app .withScrollbar .v-table__overflow {
  height: 300px;
  overflow-y: auto;
}

#app .withScrollbar .v-datatable th {
  background: #eeeeee;
  position: sticky;
  top: 0;
  z-index: 99;
}
</style>
