<template>
  <div class="page">
    <div class="title-page">
      <div class="text-center">Physical Cycle Count</div>
    </div>

    <div class="block">
      <div class="table">
        <div class="row">
          <div class="col-6 item head">Item Type</div>
          <div class="col-6 item head">Number of Item Type</div>
        </div>
        <div class="row" v-for="item in cycleCounts">
          <div class="col-6 item head">{{ item.label }}</div>
          <div class="col-6 item">
            <input
              type="number"
              class="input"
              placeholder="Number Item"
              v-model.trim="item.number" >
          </div>
        </div>
        <div class="row">
          <span class="invalid-feedback mt-2" v-show="errors.has('number_cycle_count')">
            &nbsp;{{ errors.first('number_cycle_count') }}
          </span>
        </div>
      </div>

      <div class="print">
        <div class="lalbel">Print Now:</div>
        <div class="btn btn-primary" @click.stop="onClickPrint(false)">With OH Qty</div>
        <div class="btn btn-primary" @click.stop="onClickPrint(true)">Without OH Qty</div>
      </div>

      <div class="schedule">
        <schedule ref="schedule" :mail-type="mailType" />
      </div>

    </div>

    <cycle-count-print
      :uuid="cycleCountUuid"
      :data="spares"
      :config="config"
      ref="cycleCountPrinter" />

  </div>
</template>
<style lang="scss" scoped>
  .page {
    margin: auto;
    width: 70%;
    .table {
      margin: auto;
      width: 50%;
      .row {
        .item {
          border: 1px solid #363A47;
          background-color: #11131D;
          color: #20222B;
          font-size: 15px;
          line-height: 20px;
          padding: 10px;
          text-align: center;
          &.head {
            background-color: #212430;
            color: #fff;
            font-weight: bold;
          }
        }
      }
    }
    .print {
      margin-top: 10px;
      display: flex;
      justify-content: center;
      align-content: center;
      .lalbel {
        font-size: 16px;
        font-weight: bold;
        line-height: 65px;
        margin-right: 30px;
      }
      .btn {
        margin: 10px;
      }
    }
    .schedule {
      padding: 30px 20px;
      display: flex;
      justify-content: center;
      align-content: center;
      border: dotted #363A47;
      border-radius: 30px;
    }
  }
</style>
<script>
import rf from 'requestfactory'
import { chain, size, toLower } from 'lodash'
import Const from 'common/Const'
import Schedule from './partials/Schedule';
import CycleCountPrint from './partials/CycleCountPrint';

export default {
  components: {
    Schedule,
    CycleCountPrint
  },

  data () {
    return {
      itemList: null,
      cycleCountUuid: '',
      spares: [],
      config: {
        withoutQty: false
      },
      cycleCounts: []
    }
  },

  computed: {
    mailType () {
      return Const.RECEIVER_EMAIL_TYPE.CYCLE_COUNT
    }
  },

  watch: {
    cycleCounts: {
      deep: true,
      handler () {
        this.resetError()
      }
    }
  },

  mounted () {
    this.cycleCounts = chain(Const.ITEM_TYPE)
      .map(type => {
        const label = type.name
        type = toLower(type.value)
        return { type, label, number: null }
      })
      .value()

    this.getSpares()
  },

  methods: {
    getSpares () {
      const params = {
        no_pagination: true
      }
      rf.getRequest('AdminRequest').getSparesAssignedBin(params)
        .then(res => {
          this.itemList = res.data
        })
    },

    onClickPrint (withoutQty = false) {
      this.resetError()

      const cycleCounts = chain(this.cycleCounts)
        .filter(item => !!item.number)
        .value()

      if (!cycleCounts.length) {
        this.errors.add({field: 'number_cycle_count', msg: 'Please input a number item.'})
        return
      }

      rf.getRequest('SpareRequest').generateCycleCount(cycleCounts)
        .then(res => {
          this.cycleCountUuid = res.data.uuid
          this.spares = res.data.spares
          this.config.withoutQty = withoutQty

          this.$nextTick(() => {
            this.$refs.cycleCountPrinter.print();
          })
        })
    },

    resetError () {
      this.errors.clear()
    }
  }
}
</script>
