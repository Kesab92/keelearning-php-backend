<template>
  <v-breadcrumbs
    class="s-path"
    :items="pathItems">
    <template v-slot:divider>
      <v-icon>chevron_right</v-icon>
    </template>
    <template v-slot:item="props">
      <li>
        <router-link
          :exact="props.item.exact"
          :to="props.item.to">
          <v-layout
            row
            align-center>
            <div>{{ props.item.text }}</div>
            <v-icon
              v-if="props.item.isLast"
              color="grey"
              medium>arrow_drop_down</v-icon>
          </v-layout>
        </router-link>
      </li>
    </template>
  </v-breadcrumbs>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  computed: {
    ...mapGetters({
      path: 'learningmaterials/path',
    }),
    pathItems() {
      const items = [{
        text: 'Mediathek',
        to: {
          name: 'learningmaterials.index'
        }
      }].concat(this.path.map(folder => {
        return {
          text: folder.name,
          id: folder.id,
          exact: true,
          isLast: false,
          to: {
            name: 'learningmaterials.index',
            params: {
              folderId: folder.id,
            }
          }
        }
      }))
      if(items.length > 1) {
        const lastItem = items[items.length - 1]
        lastItem.exact = false
        lastItem.isLast = true
        lastItem.to = {
          name: 'learningmaterials.folder.edit.general',
          params: {
            folderId: lastItem.id,
          },
        }
      }
      return items
    },
  },
}
</script>

<style lang="scss" scoped>
  #app .s-path {
    ::v-deep li {
      font-size: 18px;
      padding: 2px 10px;
      border-radius: 10px;

      &:hover {
        background: rgba(0, 0, 0, 0.03);

        a {
          color: #1976d2;
        }
      }
    }
  }
</style>
