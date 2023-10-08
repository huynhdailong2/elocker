<template>
  <modal
    :name="name"
    :width="'70%'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    class="custom-modal">
    <div class="header">
      <div class="title">Issue Completed</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content">
      <p>Click the button below to print this transaction.</p>
      <button class="btn btn-primary" @click.stop="onClickPrint">Print</button>
    </div>

    <issue-card-print
      :jobCard="jobCard"
      :data="data"
      ref="issueCardPrint" />
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
import IssueCardPrint from './IssueCardPrint'

export default {
  components: {
    IssueCardPrint
  },

  props: {
    name: {
      type: String,
      default: 'IssueCardPrintModal'
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
      this.jobCard = params?.jobCard
      this.data = params?.data || []
      this.torqueAreas = params?.torqueAreas || []
    },

    close () {
      this.$modal.hide(this.name)
      return this.$router.push('/inventory')
    },

    onClickPrint () {
      chain(this.data)
        .each(record => {
          const torque = chain(this.torqueAreas)
            .filter(item => item.id === record.torque_wrench_area_id)
            .head()
            .value()

          this.$set(record, 'torque_area', torque ? torque.area : null)
          this.$set(record, 'torque_value', torque ? parseInt(torque.torque_value) : null)
        })
        .value()

      this.$nextTick(() => {
        this.$refs.issueCardPrint.print();
      })
    }
  }
}
</script>
