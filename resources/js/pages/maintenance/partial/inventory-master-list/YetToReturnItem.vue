<template>
  <div class="spares mb-3">
    <div class="table-scroller mt-3 mb-3">
      <data-table2 :getData="getYetToReturnSpares"
          :limit="10"
          :column="11"
          :widthTable="'100%'"
          @DataTable:finish="onDataTableFinished"
          ref="datatable">
          <th class="text-center">Load/Hydrostatic Test Due</th>
          <th class="text-center">Calibration Due</th>
          <th class="text-center">Loan Date</th>
          <th class="text-center">Yet to Return</th>
          <th class="text-center">WO#</th>
          <th class="text-center">Vehicle</th>
          <th class="text-center">Platform</th>
          <th class="text-center">Item Details</th>
          <th class="text-center">Part No</th>
          <th class="text-center">Quantity</th>
          <th class="text-center">Loan By</th>
        <template slot="body" slot-scope="props">
          <tr  :style="{ 'background-color': props.item.expired_return_time ? '#f21501' : '' }">
            <td class="mw_110px maw_145x"
              :title="props.item.load_hydrostatic_test_due | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY')" >
              <div class="text ellipsis">
                {{ props.item.load_hydrostatic_test_due | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY') }}
              </div>
            </td>
            <td class="mw_110px maw_145x" :title="props.item.calibration_due | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY')" >
              <div class="text ellipsis">{{ props.item.calibration_due | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY') }}</div>
            </td>
            <td :title="props.item.issued_date | dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY')" >
              <div class="text ellipsis">{{ props.item.issued_date | dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY') }}</div>
            </td>
            <td :title="props.item.yet_to_return" >
              <div class="text ellipsis">{{ props.item.yet_to_return }}</div>
            </td>
            <td :title="props.item.wo" >
              <div class="text ellipsis">{{ props.item.wo }}</div>
            </td>
            <td :title="props.item.vehicle_num" >
              <div class="text ellipsis">{{ props.item.vehicle_num }}</div>
            </td>
            <td :title="props.item.platform" >
              <div class="text ellipsis">{{ props.item.platform }}</div>
            </td>
            <td :title="props.item.spare_name" >
              <div class="text ellipsis">{{ props.item.spare_name }}</div>
            </td>
            <td :title="props.item.part_no" >
              <div class="text ellipsis">{{ props.item.part_no }}</div>
            </td>
            <td :title="props.item.issued_quantity" >
              <div class="text ellipsis">{{ props.item.issued_quantity }}</div>
            </td>
            <td :title="props.item.issued_by" >
              <div class="text ellipsis">{{ props.item.issued_by }}</div>
            </td>
          </tr>
        </template>
      </data-table2>
    </div>
<!-- 
    <div class="text-right">
      <button class="btn btn-primary"
          v-if="replenishForm"
          @click.stop="onCheckout"
          :disabled="noSelectedData">
          Checkout
      </button>
      <button class="btn btn-primary"
          @click.stop="nextReplenishForm"
          :disabled="noSelectedData" v-else >
          Go to Cart
      </button>
    </div>

    <replenish-form-modal @done="handleReplenishFormFinished"/> -->
    <div class="action d-flex">
      <button class="btn btn-primary" @click.stop="onClickShowEmailModal">
        Email
      </button>
      <button class="btn btn-primary" @click.stop="onClickPrint()">
        Print
      </button>
    </div>

    <YetToReturnItemPrint
      :data="printData"
      ref="yetToReturnItemsPrinter" />

    <send-report-to-email-modal />

  </div>
</template>
<style lang="scss" scoped>
.spares {
  .table-scroller {
    // min-height: 430px;
    .form-input {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 10px;
      .circle {
        border: 1px solid #c7cbce;
        border-radius: 50%;
        padding: 8px 15px;
        cursor: pointer;
        font-weight: bold;
        background-color: #fff;
        color: #000;
        &:hover {
          border-color: #3490dc;
        }
      }
      .number {
        border: 1px solid #c7cbce;
        height: 35px;
        width: 50px;
        line-height: 35px;
        margin: 10px;
        text-align: center;
      }
    }
    ::v-deep .box_table {
      td {
        // background-color: initial;
      }
    }
  }
  .note {
    justify-content: flex-end;
    .box {
      border: 1px solid;
      border-radius: 0;
      padding: 4px 6px;
      & + .box {
        border-left: none;
      }
      &.active {
        box-shadow: 0 0 0 0.2rem rgb(52 144 220 / 25%);
      }
    }
  }
  .action {
    padding-top: 30px;
    justify-content: space-between;
    button {
      min-width: 100px;
    }
  }
}
</style>
<script>
import moment from 'moment'
import rf from 'requestfactory'
import { mapState } from 'vuex'
import Const from 'common/Const'
import { chain, cloneDeep, remove } from 'lodash'
import YetToReturnItemPrint from './YetToReturnItemPrint'
import SendReportToEmailModal from '../SendReportToEmailModal'

const BUTTON_FILTER = {
  ALL: 'all',
  REFRESH: 'refresh',
  EXPIRED: 'expired',
  ONE_MONTH: 'in 30 days',
  TWO_MONTHS: 'in 60 days'
}

export default {
  components : {
    YetToReturnItemPrint,
    SendReportToEmailModal
  },

  data () {
    return {
      data: [],
      printData: [],
      Const
    }
  },

  methods: {
    getYetToReturnSpares(params) {
      return rf.getRequest('SpareRequest').getYetToReturnSpares(params)
    },

    onDataTableFinished () {
      this.data = this.$refs.datatable.rows

      chain(this.data)
        .each(record => {
          this.$set(record, 'expired_return_time', this.isExpiredReturnTime(record))
        })
        .value()
    },

    isExpiredReturnTime (record) {
      const limitHours = 16
      const issuedDatetime = moment.utc(record.issued_date, Const.DATETIME_PATTERN).local()
      const limitDatetime = issuedDatetime.clone().startOf('day').add(limitHours, 'hours')

      const now = moment()
      const isSameDay = now.diff(limitDatetime, 'days') === 0

      if (issuedDatetime > limitDatetime && isSameDay) {
        return false
      }

      return now > limitDatetime
    },

    onClickPrint () {
      const params = {
        no_pagination: true
      }
      rf.getRequest('SpareRequest').getYetToReturnSpares(params)
        .then(res => {
          this.printData = res.data
          chain(this.printData)
            .each(record => {
              this.$set(record, 'expired_return_time', this.isExpiredReturnTime(record))
            })
            .value()

          this.$nextTick(() => {
            this.$refs.yetToReturnItemsPrinter.print();
          })
        })
    },

    onClickShowEmailModal (item) {
      const callback = async (emails) => {
        return await rf.getRequest('SpareRequest').sendYetToReturnSparesReport({ emails })
          .catch(error => {
            this.showError(error.response.data.message)
          })
      }
      this.$modal.show('send-report-to-email-modal', { callback })
    }
  }
}
</script>
