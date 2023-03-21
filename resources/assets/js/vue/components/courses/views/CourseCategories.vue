<template>
  <div>
    <CategoryIntro />
    <CourseTabs />
    <ContentCategories
      :type="$constants.CONTENT_CATEGORIES.TYPE_COURSES"
      :read-only="isReadOnly"
      root-route-name="courses.categories"
      edit-route-name="courses.categories.edit.general" />
  </div>
</template>
<script>
import CourseTabs from "../components/CourseTabs"
import ContentCategories from "../../partials/content-categories/Index"
import CategoryIntro from "../components/CategoryIntro"
import {mapGetters} from "vuex";
export default {
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    isReadOnly() {
      return !this.myRights['courses-edit']
    }
  },
  components: {
    CategoryIntro,
    CourseTabs,
    ContentCategories,
  },
}
</script>
