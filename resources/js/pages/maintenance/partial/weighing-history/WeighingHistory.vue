<template>
  <div class="spares mb-3">
    <div class="d-flex ju-center">
      <div class="col-6">
        <div class="form-group">
          <label class="w-100 input-title text-white">From (Transaction Date)</label>
          <datepicker
            format="dd/MM/yyyy"
            input-class="form-control date-selector"
            v-model="transactionFrom"
            name="calibration_due" />
        </div>
      </div>
      <div class="col-6">
        <div class="form-group">
          <label class="w-100 input-title text-white">To (Transaction Date)</label>
          <datepicker
            format="dd/MM/yyyy"
            input-class="form-control date-selector"
            v-model="transactionTo"
            :disabled-dates="{to: transactionFrom}"
            name="calibration_due" />
        </div>
      </div>
    </div>
    <div class="text-center mb-2 cpx-2">
      <button class="btn btn-primary" :disabled="disabled" @click.stop="onClickGenerate">Generate</button>
    </div>
    <div class="table-scroller mt-3 mb-3">
      <data-table2 :getData="getTransactionsWeighingSystem"
          :limit="10"
          :column="7"
          :widthTable="'100%'"
          @DataTable:finish="onDataTableFinished"
          ref="datatable">
          <th class="text-center">User Name</th>
          <th class="text-center">Card ID</th>
          <th class="text-center">Trans Date</th>
          <th class="text-center">Item Name</th>
          <th class="text-center">Device ID</th>
          <th class="text-center">Qty Change</th>
          <th class="text-center">OH Qty</th>
        <template slot="body" slot-scope="props">
          <tr>
            <td :title="props.item.weighing_history.name" >
              <div class="text ellipsis">{{ props.item.weighing_history.name }}</div>
            </td>
            <td :title="props.item.weighing_history.card_id" >
              <div class="text ellipsis">{{ props.item.weighing_history.card_id }}</div>
            </td>
            <td :title="props.item.created_at" >
              <div class="text ellipsis">{{ props.item.created_at | dateFormatter(Const.DATETIME_PATTERN, Const.DATETIME) }}</div>
            </td>
            <td :title="props.item.name" >
              <div class="text ellipsis">{{ props.item.name }}</div>
            </td>
            <td :title="props.item.bin_id" >
              <div class="text ellipsis">{{ props.item.bin_id }}</div>
            </td>
            <td :title="props.item.change_quantity" >
              <div class="text ellipsis">{{ props.item.change_quantity }}</div>
            </td>
            <td :title="props.item.quantity" >
              <div class="text ellipsis">{{ props.item.quantity }}</div>
            </td>
          </tr>
        </template>
      </data-table2>
    </div>
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

    <WeighingHistoryPrint :data="printData" ref="weighinHistoryPrint" />

    <send-report-to-email-modal />
    <WeighingHistoryDetailModal :name="weighingHistoryDetailModal"/>
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
import Const from 'common/Const'
import { chain, isEmpty } from 'lodash'
import SendReportToEmailModal from '../SendReportToEmailModal'
import Datepicker from 'vuejs-datepicker'
import WeighingHistoryDetailModal from "./WeighingHistoryDetailModal";
import WeighingHistoryPrint from "./WeighingHistoryPrint";

export default {
  components : {
    WeighingHistoryPrint,
    WeighingHistoryDetailModal,
    SendReportToEmailModal,
    Datepicker
  },

  data () {
    return {
      printData: [],
      transactionFrom: moment().subtract(30, 'days').toDate(),
      transactionTo: moment().toDate(),
      Const,
      weighingHistoryDetailModal: 'weighing-history-detail-modal'
    }
  },

  computed: {
    transactionFromFormat () {
      if(!this.transactionFrom) return
      return moment(this.transactionFrom).utc().startOf('day').format(Const.DATETIME_PATTERN)
    },
    transactionToFormat () {
      if(!this.transactionTo) return
      return moment(this.transactionTo).utc().endOf('day').format(Const.DATETIME_PATTERN)
    },
    disabled () {
      return isEmpty(this.transactionFromFormat) || isEmpty(this.transactionToFormat)
    }
  },

  methods: {
    getTransactionsWeighingSystem(params) {
      params = {
        ...params,
        transaction_date: {
          start: this.transactionFromFormat,
          end: this.transactionToFormat
        },
      }
      return rf.getRequest('WeightRequest').getTransactionsWeighingSystem(params)
    },

    onDataTableFinished () {

    },

    onClickPrint () {
      const params = {
        issued_date: {
          start: this.transactionFromFormat,
          end: this.transactionToFormat
        },
        no_pagination: true
      }
      rf.getRequest('WeightRequest').getTransactionsWeighingSystem(params)
        .then(res => {
          this.printData = res.data

          this.$nextTick(() => {
            this.$refs.weighinHistoryPrint.print();
          })
        })
    },

    onClickShowEmailModal (item) {
      const callback = async (emails) => {
        const params = {
          emails: emails,
          transaction_date: {
            start: this.transactionFromFormat,
            end: this.transactionToFormat
          },
        }

        return await rf.getRequest('WeightRequest').sendWeighingSystemReport(params)
      }
      this.$modal.show('send-report-to-email-modal', { callback })
    },

    onClickExportCsv() {
      const params = {
        transaction_date: JSON.stringify({
          start: this.transactionFromFormat,
          end: this.transactionToFormat
        }),
        no_pagination: true,
      }
      const qs = '?' + new URLSearchParams(params).toString()
      window.location.href = `/weighing-system/export` + qs;
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

    onClickDetail(item) {
      const callback = () => {
        this.$refs.datatable.refresh()
      }

      this.$modal.show(this.weighingHistoryDetailModal, { data: item.transactions, callback })
    }
  }
}
</script>
