<template>
  <v-data-table
    :headers="headers"
    :items="forms"
    :loading="isLoading"
    :pagination.sync="pagination"
    :rows-per-page-items="[50, 100, 200]"
    :total-items="formCount"
    class="elevation-1 forms-table table-layout-fixed"
    item-key="id">
    <tr
      @click="editForm(props.item.id)"
      class="clickable"
      slot="items"
      slot-scope="props">
      <td>
        {{ props.item.title }}
      </td>
      <td>
        <TagDisplay :tags="props.item.tags" />
      </td>
      <td>
        <v-chip
          disabled
          small
          :text-color="getStatus(props.item).textColor"
          :color="getStatus(props.item).color">
          {{ getStatus(props.item).status }}
        </v-chip>
      </td>
      <td>
        <content-category-list
          :categories="props.item.categories"
          :type="$constants.CONTENT_CATEGORIES.TYPE_FORMS"/>
      </td>
    </tr>
  </v-data-table>
</template>

<script>
import {mapGetters} from "vuex"
import TagDisplay from "../../partials/global/TagDisplay"

export default {
  data() {
    return {
      headers: [
        {
          text: "Bezeichnung",
          value: "title",
          width: "200px",
        },
        {
          text: "Benutzergruppen",
          sortable: false,
          width: "250px",
        },
        {
          text: "Status",
          width: "150px",
          sortable: false,
        },
        {
          text: "Kategorie",
          width: "200px",
          sortable: false,
        },
      ]
    }
  },
  computed: {
    ...mapGetters({
      formCount: 'forms/formCount',
      forms: 'forms/forms',
      isLoading: 'forms/listIsLoading',
    }),
    pagination: {
      get() {
        return this.$store.state.forms.pagination
      },
      set(data) {
        this.$store.commit('forms/setPagination', data)
      },
    },
  },
  watch: {
    pagination: {
      handler() {
        this.loadData()
      },
      deep: true,
    },
  },
  methods: {
    editForm(formId) {
      this.$router.push({
        name: 'forms.edit.general',
        params: {
          formId: formId,
        },
      })
    },
    getStatus(form) {
      if (form.is_draft) {
        return {
          color: 'yellow',
          textColor: 'dark',
          status: 'Entwurf',
        }
      }

      if (form.is_archived) {
        return {
          color: 'gray',
          textColor: 'dark',
          status: 'Archiviert',
        }
      }

      return {
        color: 'green',
        textColor: 'white',
        status: 'Verfügbar',
      }
    },
    loadData() {
      this.$store.dispatch('forms/loadForms')
    },
  },
  components: {
    TagDisplay,
  },
}
</script>

<style lang="scss">
#app .forms-table {
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

#app .s-form__coverImage {
  width: 110px;
  display: block;
  height: 100%;
  min-height: 70px;
  max-height: 150px;
  object-fit: cover;
  margin-bottom: 3px;
}
</style>
