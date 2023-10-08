<template>
  <div class="spares mb-3">
    <div class="d-flex ju-center">
      <div class="col-6">
        <div class="form-group">
          <label for="wo-from" class="w-100 input-title text-white">From</label>
          <datepicker
            format="dd/MM/yyyy"
            input-class="form-control date-selector"
            v-model="dateFrom"
            name="calibration_due" />
        </div>
      </div>
      <div class="col-6">
        <div class="form-group">
          <label for="wo-to" class="w-100 input-title text-white">TO</label>
          <datepicker
            format="dd/MM/yyyy"
            input-class="form-control date-selector"
            v-model="dateTo"
            :disabled-dates="{to: dateFrom}"
            name="calibration_due" />
        </div>
      </div>
    </div>
    <div class="text-center mb-2 cpx-2">
      <button class="btn btn-primary" :disabled="disabled" @click.stop="onClickGenerate">Generate</button>
    </div>
    <div class="form-search">
      <input
        type="text"
        class="input"
        placeholder="Search Part No ..."
        v-model="inputSearch" />
    </div>
    <div class="table-scroller mt-3 mb-3">
      <data-table2 :getData="getSparesTorqueWrench"
          :limit="10"
          :column="10"
          :widthTable="'100%'"
          @DataTable:finish="onDataTableFinished"
          ref="datatable">
<!--          <th class="text-center">WO#</th>-->
          <th class="text-center">Service/WO #</th>
          <th class="text-center">Trans Date</th>
          <th class="text-center">Vehicle#</th>
          <th class="text-center">Platform</th>
          <th class="text-center">Item Details</th>
          <th class="text-center" data-sort-field="spares.part_no">Part No</th>
          <th class="text-center" data-sort-field="torque_wrench_areas.torque_value">Torque No</th>
          <th class="text-center">Area Use</th>
          <th class="text-center">Issue To</th>
<!--          <th class="text-center">Expiry</th>-->
          <th class="text-center">Calibration Date</th>
        <template slot="body" slot-scope="props">
          <tr>
<!--            <td :title="props.item.wo" >-->
<!--              <div class="text ellipsis">{{ props.item.wo }}</div>-->
<!--            </td>-->
            <td :title="props.item.card_num" >
              <div class="text ellipsis">{{ props.item.card_num }}</div>
            </td>
            <td :title="props.item.issued_date | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY')" >
              <div class="text ellipsis">{{ props.item.issued_date | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY') }}</div>
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
            <td :title="props.item.torque_value | formatQuantity" >
              <div class="text ellipsis">{{ props.item.torque_value | formatQuantity }}</div>
            </td>
            <td :title="props.item.torque_area" >
              <div class="text ellipsis">{{ props.item.torque_area }}</div>
            </td>
            <td :title="props.item.issued_to" >
              <div class="text ellipsis">{{ props.item.issued_to }}</div>
            </td>
<!--            <td class="mw_110px maw_145x" :title="props.item.expiry_date | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY')" >-->
<!--              <div class="text ellipsis">{{ props.item.expiry_date | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY') }}</div>-->
<!--            </td>-->
            <td class="mw_110px maw_145x" :title="props.item.calibration_due | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY')" >
              <div class="text ellipsis">{{ props.item.calibration_due | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY') }}</div>
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

    <torque-wrenches-print
      :data="printData"
      ref="torqueWrenchesPrint" />

    <send-report-to-email-modal />
  </div>
</template>
<style>
</style>
<style lang="scss" scoped>
.spares {
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
  //   background-color: #cdd4ea;
  // }
  .form-search {
    display: flex;
    flex-direction: row-reverse;
    margin-top: 20px;
    .input {
      width: 300px;
    }
  }
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
import { chain, cloneDeep, remove, isEmpty, debounce } from 'lodash'
import TorqueWrenchesPrint from './TorqueWrenchesPrint'
import SendReportToEmailModal from '../SendReportToEmailModal'
import Datepicker from 'vuejs-datepicker'

export default {
  components : {
    TorqueWrenchesPrint,
    SendReportToEmailModal,
    Datepicker
  },

  data () {
    return {
      inputSearch: null,
      printData: [],
      dateFrom: '',
      dateTo: '',
      Const
    }
  },

  computed: {
    dateFromFormat () {
      if(!this.dateFrom) return
      return moment(this.dateFrom).startOf('day').utc().format(Const.DATETIME_PATTERN)
    },

    dateToFormat () {
      if(!this.dateTo) return
      return moment(this.dateTo).endOf('day').utc().format(Const.DATETIME_PATTERN)
    },

    disabled () {
      return isEmpty(this.dateFromFormat) || isEmpty(this.dateToFormat)
    }
  },

  watch: {
    inputSearch: debounce(function () {
      this.$nextTick(() => {
        this.$refs.datatable.refresh()
      })
    }, 300)
  },

  methods: {
    getSparesTorqueWrench(params) {
      params = {
        ...params,
        search_key: this.inputSearch,
        issued_date: {
          start: this.dateFromFormat,
          end: this.dateToFormat
        }
      }
      return rf.getRequest('SpareRequest').getSparesTorqueWrench(params)
    },

    onDataTableFinished () {
      this.data = this.$refs.datatable.rows
      // Do something.
    },

    onClickPrint () {
      const params = {
        no_pagination: true,
        issued_date: {
          start: this.dateFromFormat,
          end: this.dateToFormat
        }
      }
      rf.getRequest('SpareRequest').getSparesTorqueWrench(params)
        .then(res => {
          this.printData = res.data

          this.$nextTick(() => {
            this.$refs.torqueWrenchesPrint.print();
          })
        })
    },

    onClickShowEmailModal (item) {
      const callback = async (emails) => {
        const params = {
          emails: emails,
          search_key: this.inputSearch,
          issued_date: {
            start: this.dateFromFormat,
            end: this.dateToFormat
          }
        }
        return await rf.getRequest('SpareRequest').sendSparesTorqueWrenchReport(params)
          .catch(error => {
            this.showError(error.response.data.message)
          })
      }
      this.$modal.show('send-report-to-email-modal', { callback })
    },

    onClickExportCsv() {
      const params = {
        issued_date: JSON.stringify({
          start: this.dateFromFormat,
          end: this.dateToFormat
        }),
        no_pagination: true,
      }
      const qs = '?' + new URLSearchParams(params).toString()
      window.location.href = `/torque-wrench/export` + qs;
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
