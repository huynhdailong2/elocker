<template>
  <modal
    :name="name"
    :width="'70%'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    class="custom-modal">
    <div class="header">
      <div class="title">Replenish Completed</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content">
      <p>Click the button below to print this transaction.</p>
      <button class="btn btn-primary" @click.stop="onClickPrint">Print</button>
    </div>

    <replenish-manual-print
      :data="data"
      ref="replenishManualPrint" />
  </modal>
</template>
<style lang="scss" scoped>
.content {
  text-align: center;
  margin-bottom: 40px;
  .btn-primary {
    padding-left: 30px;
    padding-right: 30px;
  }
}
</style>
<script>
import { chain } from 'lodash'
import ReplenishManualPrint from './ReplenishManualPrint'

export default {
  components: {
    ReplenishManualPrint
  },

  props: {
    name: {
      type: String,
      default: 'ReplenishManualPrintModal'
    }
  },

  data () {
    return {
      jobCard: null,
      data: [],
      torqueAreas: [],
    }
  },

  methods: {
    beforeOpen (event) {
      const { params } = event
      this.data = params?.data || []
    },

    close () {
      this.$modal.hide(this.name)
      return this.$router.push('/inventory')
    },

    onClickPrint () {
      this.$nextTick(() => {
        this.$refs.replenishManualPrint.print();
      })
    }
  }
}
</script>
