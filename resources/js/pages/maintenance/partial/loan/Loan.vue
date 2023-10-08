<template>
  <div class="spares mb-3">
    <div class="d-flex ju-center">
      <div class="col-4">
        <div class="form-group">
          <label for="wo-from" class="w-100 input-title text-white">From (Loan Date)</label>
          <datepicker
            format="dd/MM/yyyy"
            input-class="form-control date-selector"
            v-model="loanFrom"
            name="calibration_due" />
        </div>
      </div>
      <div class="col-4">
        <div class="form-group">
          <label for="wo-to" class="w-100 input-title text-white">To (Loan Date)</label>
          <datepicker
            format="dd/MM/yyyy"
            input-class="form-control date-selector"
            v-model="loanTo"
            :disabled-dates="{to: loanFrom}"
            name="calibration_due" />
        </div>
      </div>
      <div class="col-4 filter">
        <div class="form-group">
          <label class="w-100 input-title text-white">Search</label>
          <input
            type="text"
            class="input"
            placeholder="Search Part No ..."
            v-model="inputSearch" />
        </div>
      </div>
    </div>
    <div class="text-center mb-2 cpx-2">
      <button class="btn btn-primary" :disabled="disabled" @click.stop="onClickGenerate">Generate</button>
    </div>
    <div class="d-flex note">
      <button class="btn box bg-green">Fully Return</button>
      <button class="btn box bg-red">Not Yet Return</button>
    </div>
    <div class="table-scroller mt-3 mb-3">
      <data-table2 :getData="getSparesReportByLoan"
          :limit="10"
          :column="15"
          :widthTable="'100%'"
          @DataTable:finish="onDataTableFinished"
          ref="datatable">
          <th class="text-center">WO/Svc#</th>
          <th class="text-center">Loan Date</th>
          <th class="text-center">Return Date</th>
          <th class="text-center">Vehicle</th>
          <th class="text-center">Platform</th>
          <th class="text-center">Item Details</th>
          <th class="text-center">Part No</th>
<!--          <th class="text-center">Area Use</th>-->
          <th class="text-center">Loan Qty</th>
          <th class="text-center">Returned Qty</th>
          <th class="text-center">Qty</th>
          <th class="text-center">Loan By</th>
          <th class="text-center">Loan To</th>
<!--          <th class="text-center">Trans</th>-->
          <th class="text-center">Load/Hydrostatic Test Due</th>
          <th class="text-center">Calibration / Inspection Due</th>
          <th class="text-center">Expiry Date</th>
        <template slot="body" slot-scope="props">
          <tr :class="{'bg-green': props.item.fully_returned, 'bg-red': props.item.expired_return_time }">
            <td :title="props.item.wo" >
              <div class="text ellipsis">{{ props.item.card_num }}</div>
            </td>
            <td :title="props.item.issued_date | dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY')" >
              <div class="text ellipsis">{{ props.item.issued_date | dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY') }}</div>
            </td>
            <td :title="props.item.issued_update_date | dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY')" >
              <div class="text ellipsis" v-if="props.item.returned_quantity">{{ props.item.issued_update_date | dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY') }}</div>
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
<!--            <td :title="props.item.torque_area" >-->
<!--              <div class="text ellipsis">{{ props.item.torque_area }}</div>-->
<!--            </td>-->
            <td :title="props.item.issued_quantity" >
              <div class="text ellipsis">{{ props.item.issued_quantity }}</div>
            </td>
            <td :title="props.item.returned_quantity" >
              <div class="text ellipsis">{{ props.item.returned_quantity }}</div>
            </td>
            <td :title="props.item.quantity" >
              <div class="text ellipsis">{{ props.item.quantity }}</div>
            </td>
            <td :title="props.item.issued_by" >
              <div class="text ellipsis">{{ props.item.issued_by }}</div>
            </td>
            <td :title="props.item.issued_to" >
              <div class="text ellipsis">{{ props.item.issued_to }}</div>
            </td>
<!--            <td :title="props.item.tnx" >-->
<!--              <div class="text ellipsis">{{ props.item.tnx }}</div>-->
<!--            </td>-->
            <td class="mw_110px maw_145x"
              :title="props.item.load_hydrostatic_test_due | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY')" >
              <div class="text ellipsis">
                {{ props.item.load_hydrostatic_test_due | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY') }}
              </div>
            </td>
            <td class="mw_110px maw_145x" :title="props.item.calibration_due | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY')" >
              <div class="text ellipsis">{{ props.item.calibration_due | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY') }}</div>
            </td>
            <td class="mw_110px maw_145x" :title="props.item.expiry_date | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY')" >
              <div class="text ellipsis">{{ props.item.expiry_date | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY') }}</div>
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
      <div class="float-left">
        <button class="btn btn-primary" @click.stop="onClickShowEmailModal">
          Email
        </button>
        <button class="btn btn-primary" @click.stop="onClickExportCsv">
          Export
        </button>
      </div>
      <div class="float-right">
        <button class="btn btn-primary" @click.stop="onClickPrint()">
          Print
        </button>
      </div>
    </div>

    <LoanPrint
      :data="printData"
      ref="loanPrint" />

    <send-report-to-email-modal />
  </div>
</template>
<style lang="scss" scoped>
::v-deep.spares {
  .filter {
    .input {
      margin: 0 0;
      padding: 5px 15px;
    }
  }
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
  .bg-red {
    background-color: #f21501;
  }
  .bg-yellow {
    background-color: #f5f576;
  }
  .bg-green {
    background-color: #5bd65b;
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
    .box_table {
      td {
        background-color: initial;
      }
    }
  }
  .note {
    justify-content: flex-end;
    .box {
      // border: 1px solid;
      border-radius: 0;
      padding: 5px 10px;
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
import LoanPrint from './LoanPrint'
import SendReportToEmailModal from '../SendReportToEmailModal'
import Datepicker from 'vuejs-datepicker'

export default {
  components : {
    LoanPrint,
    SendReportToEmailModal,
    Datepicker
  },

  data () {
    return {
      printData: [],
      loanFrom: moment().subtract(30, 'days').toDate(),
      loanTo: moment().toDate(),
      Const,
      inputSearch: null,
    }
  },

  computed: {
    loanFromFormat () {
      if(!this.loanFrom) return
      return moment(this.loanFrom).startOf('day').utc().format(Const.DATETIME_PATTERN)
    },
    loanToFormat () {
      if(!this.loanTo) return
      return moment(this.loanTo).endOf('day').utc().format(Const.DATETIME_PATTERN)
    },
    disabled () {
      return isEmpty(this.loanFromFormat) || isEmpty(this.loanToFormat)
    }
  },

  methods: {
    getSparesReportByLoan(params) {
      params = {
        ...params,
        issued_date: {
          start: this.loanFromFormat,
          end: this.loanToFormat
        },
        search_key: this.inputSearch,
      }
      return rf.getRequest('SpareRequest').getSparesReportByLoan(params)
    },

    onDataTableFinished () {
      this.data = this.$refs.datatable.rows
      // Do something.
      chain(this.data)
        .each(record => {
          this.$set(record, 'expired_return_time', this.isExpiredReturnTime(record))
        })
        .value()
    },

    onClickPrint () {
      const params = {
        issued_date: {
          start: this.loanFromFormat,
          end: this.loanToFormat
        },
        no_pagination: true
      }
      rf.getRequest('SpareRequest').getSparesReportByLoan(params)
        .then(res => {
          this.printData = res.data
          chain(this.printData)
            .each(record => {
              this.$set(record, 'expired_return_time', this.isExpiredReturnTime(record))
            })
            .value()

          this.$nextTick(() => {
            this.$refs.loanPrint.print();
          })
        })
    },

    onClickShowEmailModal (item) {
      const callback = async (emails) => {
        return await rf.getRequest('SpareRequest').sendSparesByLoanReport({ emails })
      }
      this.$modal.show('send-report-to-email-modal', { callback })
    },

    onClickExportCsv() {
      const params = {
        issued_date: JSON.stringify({
          start: this.loanFromFormat,
          end: this.loanToFormat
        }),
        no_pagination: true,
      }
      const qs = '?' + new URLSearchParams(params).toString()
      window.location.href = `/spares-loan/export` + qs;
    },

    onClickGenerate () {
      if (this.disabled) {
        return
      }
      this.$refs.datatable.refresh()
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
  }
}
</script>
