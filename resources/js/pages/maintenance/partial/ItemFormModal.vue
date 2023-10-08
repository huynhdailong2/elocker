<template>
  <modal
    :name="name"
    :width="'850px'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    class="custom-modal">
    <div class="header">
      <div class="title">{{ spare ? 'Edit Item' : 'Create New Item' }}</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content">
      <item-form @item:saved="onItemSaved" :data="spare" />
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
      spare: null,
      callback: null
    }
  },

  methods: {
    beforeOpen (event) {
      const { params } = event
      this.spare = params?.spare
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
