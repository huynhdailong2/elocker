<template>
  <div class="spares mb-3">
    <div class="d-flex ju-center">
      <div class="col-6">
        <div class="form-group">
          <label for="wo-from" class="w-100 input-title text-white">From (Expiry Date)</label>
          <datepicker
            format="dd/MM/yyyy"
            input-class="form-control date-selector"
            v-model="expiredFrom"
            name="calibration_due" />
        </div>
      </div>
      <div class="col-6">
        <div class="form-group">
          <label for="wo-to" class="w-100 input-title text-white">TO (Expiry Date)</label>
          <datepicker
            format="dd/MM/yyyy"
            input-class="form-control date-selector"
            v-model="expiredTo"
            :disabled-dates="{to: expiredFrom}"
            name="calibration_due" />
        </div>
      </div>
    </div>
    <div class="text-center mb-2 cpx-2">
      <button class="btn btn-primary" :disabled="disabled" @click.stop="onClickGenerate">Generate</button>
    </div>
    <div class="table-scroller mt-3 mb-3">
      <data-table2 :getData="getSparesReportByExpired"
          :limit="10"
          :column="7"
          :widthTable="'100%'"
          @DataTable:finish="onDataTableFinished"
          ref="datatable">
          <th class="text-center">Expiring Date</th>
          <th class="text-center mw_110px maw_145x">Item Details</th>
          <th class="text-center">Part No</th>
          <th class="text-center">Quantity</th>
          <th class="text-center">Lead Time</th>
          <th class="text-center">Item Location</th>
          <th class="text-center">Remarks</th>
        <template slot="body" slot-scope="props">
          <tr>
            <td class="mw_110px maw_145x" :title="props.item.expiry_date | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY')" >
              <div class="text ellipsis">{{ props.item.expiry_date | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY') }}</div>
            </td>
            <td class="mw_110px maw_145x" :title="props.item.name" >
              <div class="text ellipsis">{{ props.item.name }}</div>
            </td>
            <td class="mw_110px maw_145x" :title="props.item.part_no" >
              <div class="text ellipsis">{{ props.item.part_no }}</div>
            </td>
            <td class="mw_110px maw_145x" :title="props.item.quantity_oh || 0" >
              <div class="text ellipsis">{{ props.item.quantity_oh || 0 }}</div>
            </td>
            <td class="mw_110px maw_145x"></td>
            <td class="mw_110px maw_145x" :title="props.item.location" >
              <div class="text ellipsis">{{ props.item.location }}</div>
            </td>
            <td class="mw_110px maw_145x"></td>
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

    <ExpiredPrint
      :data="printData"
      ref="expiredPrint" />

    <send-report-to-email-modal />
  </div>
</template>
<style lang="scss" scoped>
::v-deep.spares {
  .cpx-2 {
    padding-left: 2px;
    padding-right: 2px;
  }
  .col-6 {
    padding-right: 2px;
    padding-left: 2px;
    max-width: 300px;
  }
  .w-100 {
    width: 100%;
  }
  .input-title {
    padding: 4px 6px;
    // background: linear-gradient(0deg, #417AF9 0%, #063694 100%);
  }
  // .form-control {
  //   background-color: #cdd4ea!important;
  // }
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
import { chain, cloneDeep, remove, isEmpty } from 'lodash'
import ExpiredPrint from './ExpiredPrint'
import SendReportToEmailModal from '../SendReportToEmailModal'
import Datepicker from 'vuejs-datepicker'

const BUTTON_FILTER = {
  ALL: 'all',
  REFRESH: 'refresh',
  EXPIRED: 'expired',
  ONE_MONTH: 'in 30 days',
  TWO_MONTHS: 'in 60 days'
}

export default {
  components : {
    ExpiredPrint,
    SendReportToEmailModal,
    Datepicker
  },

  data () {
    return {
      printData: [],
      expiredFrom: moment().subtract(30, 'days').toDate(),
      expiredTo: moment().toDate(),
      Const
    }
  },

  computed: {
    expiredFromFormat () {
      if(!this.expiredFrom) return
      return moment(this.expiredFrom).startOf('day').utc().format(Const.DATE_PATTERN)
    },
    expiredToFormat () {
      if(!this.expiredTo) return
      return moment(this.expiredTo).endOf('day').utc().format(Const.DATE_PATTERN)
    },
    disabled () {
      return isEmpty(this.expiredFromFormat) || isEmpty(this.expiredToFormat)
    }
  },

  methods: {
    getSparesReportByExpired(params) {
      params = {
        ...params,
        expiredFrom: this.expiredFromFormat,
        expiredTo: this.expiredToFormat,
      }
      return rf.getRequest('SpareRequest').getSparesReportByExpired(params)
    },

    onDataTableFinished () {
      this.data = this.$refs.datatable.rows
      // Do something.
    },

    onClickPrint () {
      const params = {
        expiredFrom: this.expiredFrom,
        expiredTo: this.expiredTo,
        no_pagination: true
      }
      rf.getRequest('SpareRequest').getSparesReportByExpired(params)
        .then(res => {
          this.printData = res.data

          this.$nextTick(() => {
            this.$refs.expiredPrint.print();
          })
        })
    },

    onClickShowEmailModal (item) {
      const callback = async (emails) => {
        return await rf.getRequest('SpareRequest').sendSparesByExpiredReport({ emails })
      }
      this.$modal.show('send-report-to-email-modal', { callback })
    },

    onClickGenerate () {
      if (this.disabled) {
        return
      }
      this.$refs.datatable.refresh()
    }
  }
}
</script>
