<template>
  <v-autocomplete
    ref="autocomplete"
    v-model="selectedCategories"
    :allow-overflow="false"
    :disabled="disabled"
    :items="items"
    :label="label"
    :multiple="multiple"
    :outline="outline"
    :placeholder="placeholder"
    :deletable-chips="multiple"
    :clearable="!multiple"
    dense
    item-text="name"
    item-value="id"
    no-data-text="Keine Kategorien gefunden"
    :small-chips="multiple">
    <div
      v-if="!hideCreation"
      slot="append-item"
      class="px-3 py-2">
      <v-btn
        v-if="!newCategoryInputVisible"
        flat
        block
        color="primary"
        @click="openNewCategoryInput">
        <v-icon left>
          add
        </v-icon>
        Neue Kategorie
      </v-btn>
      <v-form
        v-else
        @submit.prevent="createNewCategory">
        <v-text-field
          ref="newCategoryInput"
          v-model="newCategoryName"
          label="Name der Kategorie"
          :loading="newCategoryLoading"
          append-outer-icon="send"
          @click:append-outer="createNewCategory"
        />
      </v-form>
    </div>
  </v-autocomplete>
</template>

<script>
import {mapGetters} from "vuex"

export default {
  props: {
    value: {
      type: [Array, Number],
      required: true,
    },
    type: {
      type: String,
      required: true,
    },
    label: {
      type: String,
      default: "Kategorien",
      required: false,
    },
    placeholder: {
      type: String,
      default: "Ohne Kategorie",
      required: false,
    },
    multiple: {
      type: Boolean,
      default: false,
      required: false,
    },
    outline: {
      type: Boolean,
      default: false,
      required: false,
    },
    disabled: {
      type: Boolean,
      default: false,
      required: false,
    },
    hideCreation: {
      type: Boolean,
      default: false,
      required: false,
    },
  },
  data() {
    return {
      isLoading: false,
      newCategoryInputVisible: false,
      newCategoryName: null,
      newCategoryLoading: false,
    }
  },
  created() {
    this.isLoading = true
    this.$store.dispatch("contentCategories/updateCategories", this.type).then(() => {
      this.isLoading = false
    })
  },
  computed: {
    ...mapGetters({
      getCategories: "contentCategories/getCategories",
    }),
    categories() {
      return this.getCategories(this.type)
    },
    selectedCategories: {
      get() {
        return this.value
      },
      set(categories) {
        this.$emit("input", categories)
      },
    },
    items() {
      if (!this.categories) {
        return []
      }
      const categories = [...this.categories].sort((a, b) => {
        if (a.id === -1) {
          return -1
        }
        if (b.id === -1) {
          return 1
        }
        return a.name.localeCompare(b.name)
      })
      return categories
    },
  },
  methods: {
    openNewCategoryInput() {
      this.newCategoryName = this.$refs.autocomplete.lazySearch
      this.newCategoryInputVisible = true
      this.$nextTick(() => {
        this.$refs.newCategoryInput.focus()
      })
    },
    createNewCategory() {
      if(this.newCategoryLoading) {
        return
      }
      this.newCategoryLoading = true
      axios.post('/backend/api/v1/content-categories', {
        name: this.newCategoryName,
        type: this.type,
      }).then(response => {
        return this.$store.dispatch('contentCategories/updateCategories', this.type).then(() => {
          this.selectedCategories = [response.data.category.id]
          this.newCategoryInputVisible = false
          this.$refs.autocomplete.isMenuActive = false
        })
      }).catch(e => {
        console.log(e)
        alert('Die Kategorie konnte leider nicht erstellt werden')
      }).finally(() => {
        this.newCategoryLoading = false
      })
    }
  },
}
</script>
