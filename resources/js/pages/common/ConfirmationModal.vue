<template>
  <modal
    :name="name"
    :width="'500px'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    class="page custom-modal">
    <div class="header">
      <div class="title">Confirmation</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content text-center">
      <div v-html="`${message || 'Do you want to perform this action?'}`"></div>
    </div>
    <div class="footer">
      <button class="btn btn-primary"@click.stop="onClickSubmit">Submit</button>
      <button class="btn btn-second ml-3" @click.stop="close">Cancel</button>
    </div>
  </modal>
</template>
<style lang="scss" scoped>
.footer {
  justify-content: center;
  display: flex;
  padding-top: 10px;
  padding-bottom: 10px; 
  margin: 20px;
  .btn {
    padding-left: 30px;
    padding-right: 30px;
  }
}
</style>
<script>

export default {
  props: {
    name: {
      type: String,
      default: 'confirmation-modal'
    }
  },

  data () {
    return {
      message: null,
      callback: null,
      data: null
    }
  },

  methods: {
    beforeOpen (event) {
      const { params } = event

      this.message  = params.message
      this.callback = params.callback
      this.data     = params.data
    },

    close () {
      this.$modal.hide(this.name)
    },

    onClickSubmit () {
      this.$emit('done')
      this.close()

      if (this.callback) {
        this.callback(this.data)
      }
    }
  }
}
</script>
