<template>
  <modal
    :name="name"
    :width="'70%'"
    height="auto"
    :clickToClose="true"
    @before-open="beforeOpen"
    class="custom-modal"
  >
    <div class="header">
      <div class="title" v-if="bin">
        Edit Bin: {{ bin.cluster_name }} - {{ bin.shelf_name }} -
        {{ bin.row }} - {{ bin.bin }}
      </div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22" />
      </div>
    </div>
    <div class="content">
      <bin-form @item:saved="onItemSaved" :data="bin" />
    </div>
  </modal>
</template>
<style lang="scss" scoped>
.content {
  width: 100%;
  max-height: 80vh;
  overflow-y: scroll;
}
</style>
<script>
import BinForm from "./BinForm";

export default {
  components: {
    BinForm,
  },

  props: {
    name: {
      type: String,
      default: "edit-bin-modal",
    },
  },

  data() {
    return {
      bin: null,
      callback: null,
    };
  },

  methods: {
    beforeOpen(event) {
      const { params } = event;
      this.bin = params?.bin;
      this.callback = params?.callback;
    },

    close() {
      if (this.callback) {
        this.callback();
      }
      this.$modal.hide(this.name);
    },

    onItemSaved() {
      this.close();
    },
  },
};
</script>
