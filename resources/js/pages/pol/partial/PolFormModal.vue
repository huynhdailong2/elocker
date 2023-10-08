<template>
  <modal
    :name="name"
    :width="'700px'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    class="custom-modal">
    <div class="header">
      <div class="title">{{ data ? 'Edit POL' : 'Create New POL' }}</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content">
      <item-form @item:saved="onItemSaved" :data="data" />
    </div>
  </modal>
</template>
<style lang="scss" scoped>
.content {
  width: 100%;
  max-height: 500px;
  overflow: auto;
}
</style>
<script>
import ItemForm from './ItemForm'

export default {
  components: {
    ItemForm
  },

  props: {
    name: {
      type: String,
      default: 'item-form-modal'
    }
  },

  data () {
    return {
      data: null,
      callback: null
    }
  },

  methods: {
    beforeOpen (event) {
      const { params } = event
      this.data = params?.data
      this.callback = params?.callback
    },

    close () {
      if (this.callback) {
        this.callback()
      }
      this.$modal.hide(this.name)
    },

    onItemSaved () {
      this.close()
    }
  }
}
</script>
