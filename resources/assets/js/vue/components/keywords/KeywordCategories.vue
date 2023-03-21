<template>
  <div>
    <CategoryIntro />
    <KeywordTabs />
    <ContentCategories
      :type="$constants.CONTENT_CATEGORIES.TYPE_KEYWORDS"
      root-route-name="keywords.categories"
      edit-route-name="keywords.categories.edit.general" />
  </div>
</template>
<script>
import KeywordTabs from "./KeywordTabs"
import ContentCategories from "../partials/content-categories/Index"
import CategoryIntro from "./CategoryIntro"
export default {
  components: {
    CategoryIntro,
    KeywordTabs,
    ContentCategories,
  },
}
</script>
