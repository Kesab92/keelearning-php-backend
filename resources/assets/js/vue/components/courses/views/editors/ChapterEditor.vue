<template>
  <div>
    <v-text-field
      v-model="value.title"
      label="Kapitel Name"
      hide-details
      class="mb-3"
      :disabled="isReadonly"
      box />
  </div>
</template>

<script>
  import {mapGetters} from "vuex";

  export default {
    props: [
      'course',
      'value',
    ],
    computed: {
      ...mapGetters({
        myRights: 'app/myRights',
      }),
      isReadonly() {
        return !this.myRights['courses-edit']
      }
    },
    methods: {
      save() {
        return axios.post(`/backend/api/v1/courses/${this.course.id}/chapter/${this.value.id}`, {
          title: this.value.title,
        })
      },
    },
  }
</script>
