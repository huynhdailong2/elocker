<template>
  <modal
    :name="name"
    :width="'500'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    class="custom-modal">
    <div class="header">
      <div class="title">Return Completed</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content">
      <p>Click the button below to print this transaction.</p>
      <button class="btn btn-primary" @click.stop="onClickPrint">Print</button>
    </div>

    <return-items-print
      :data="data"
      :action="action"
      :receiver="receiver"
      ref="returnItemsPrint" />
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
import ReturnItemsPrint from './ReturnItemsPrint'

export default {
  components: {
    ReturnItemsPrint
  },

  props: {
    name: {
      type: String,
      default: 'IssueCardPrintModal'
    }
  },

  data () {
    return {
      data: [],
      action: [],
      receiver:  null,
    }
  },

  methods: {
    beforeOpen (event) {
      const { params } = event
      this.data = params?.data || []
      this.action = params?.action
      this.receiver = params?.receiver
    },

    close () {
      this.$modal.hide(this.name)
      return this.$router.push('/inventory')
    },

    onClickPrint () {
      // chain(this.data)
      //   .each(record => {
      //     const torque = chain(this.torqueAreas)
      //       .filter(item => item.id === record.torque_wrench_area_id)
      //       .head()
      //       .value()

      //     this.$set(record, 'torque_area', torque ? torque.area : null)
      //     this.$set(record, 'torque_value', torque ? parseInt(torque.torque_value) : null)
      //   })
      //   .value()

      this.$nextTick(() => {
        this.$refs.returnItemsPrint.print();
      })
    }
  }
}
</script>
