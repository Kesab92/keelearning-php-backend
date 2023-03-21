<template>
  <div>
    <ModuleIntro>
      <template v-slot:title>
        Formular-Kategorien
      </template>
      <template v-slot:description>
        Erstellen und verwalten Sie hier Ihre Formular-Kategorien.
      </template>
    </ModuleIntro>
    <FormTabs />
    <ContentCategories
      :type="$constants.CONTENT_CATEGORIES.TYPE_FORMS"
      :read-only="!this.myRights['forms-edit']"
      root-route-name="forms.categories"
      edit-route-name="forms.categories.edit.general" />
  </div>
</template>
<script>
import {mapGetters} from "vuex"
import FormTabs from "../components/FormTabs"
import ContentCategories from "../../partials/content-categories/Index"
import ModuleIntro from "../../partials/global/ModuleIntro"
export default {
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
  },
  components: {
    ModuleIntro,
    FormTabs,
    ContentCategories,
  },
}
</script>
