<template>
  <div
    class="s-buttonWrapper"
    :class="{
      '-open': addFormFieldModalOpen,
    }">
    <v-btn
      fab
      dark
      color="primary"
      @click.stop="openModal"
      class="s-addButton">
      <v-icon
        dark>add</v-icon>
    </v-btn>
    <AddFormFieldModal
      v-if="addFormFieldModalOpen"
      :up="openModalUpwards"
      @close="addFormFieldModalOpen = false"
      @create="createFormField" />
  </div>
</template>

<script>
  import AddFormFieldModal from "./AddFormFieldModal"
  export default {
    props: ['form', 'formFieldId', 'position'],
    data() {
      return {
        addFormFieldModalOpen: false,
        isAdding: false,
        openModalUpwards: false,
      }
    },
    methods: {
      openModal() {
        // this button sticks to the bottom of the wrapping element
        let offsetElement = this.$el
        const yPosition = offsetElement.offsetTop + offsetElement.offsetHeight
        const sidebarHeight = offsetElement.closest('.js-sidebar').offsetHeight
        // open the modal up instead of down from the button if we can't fit it in the remaining height
        this.openModalUpwards = sidebarHeight - yPosition < 250
        this.addFormFieldModalOpen = true
      },
      createFormField(type) {
        if(this.isAdding) {
          return
        }
        this.isAdding = true
        axios.post(`/backend/api/v1/forms/${this.form.id}/fields`, {
          type,
          position: this.position,
        })
          .then((response) => {
            this.$emit('formFieldAdded', response.data)
            this.$nextTick(() => {
              // It's important to call this after emitting the event, because otherwise this component is destroyed before it can emit the event
              this.addFormFieldModalOpen = false
            })
          })
          .catch(() => {
            alert('Der Inhalt konnte leider nicht hinzugefügt werden. Bitte probieren Sie es später erneut.')
          })
        .finally(() => {
          this.isAdding = false
        })
      },
    },
    components: {
      AddFormFieldModal,
    },
  }
</script>

<style lang="scss" scoped>
  #app .s-buttonWrapper {
    position: relative;
    pointer-events: none;

    &:hover, &.-open {
      display: block;
    }
  }

  #app .s-addButton {
    height: 50px;
    pointer-events: all;
    width: 50px;
  }
</style>
