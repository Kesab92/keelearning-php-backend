<template>
  <div>
    <ModuleIntro>
      <template v-slot:title>
        TAG Kategorien
      </template>
    </ModuleIntro>
    <TagTabs />
    <ContentCategories
      :type="$constants.CONTENT_CATEGORIES.TYPE_TAGS"
      root-route-name="tags.categories"
      edit-route-name="tags.categories.edit.general" />
  </div>
</template>
<script>
import TagTabs from "./TagTabs"
import ContentCategories from "../partials/content-categories/Index"
import ModuleIntro from "../partials/global/ModuleIntro"
export default {
  components: {
    ModuleIntro,
    TagTabs,
    ContentCategories,
  },
}
</script>
