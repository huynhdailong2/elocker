<template>
  <div class="spares mb-3">
    <div class="d-flex ju-center">
      <div class="col-4">
        <div class="form-group">
          <label for="wo-from" class="w-100 input-title text-white">From Date</label>
          <datepicker
            format="dd/MM/yyyy"
            input-class="form-control date-selector"
            v-model="fromDate"
            name="calibration_due" />
        </div>
      </div>
      <div class="col-4">
        <div class="form-group">
          <label for="wo-to" class="w-100 input-title text-white">To Date</label>
          <datepicker
            format="dd/MM/yyyy"
            input-class="form-control date-selector"
            v-model="toDate"
            :disabled-dates="{to: fromDate}"
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
    <div class="table-scroller mt-3 mb-3">
      <data-table2 :getData="getSparesWriteOff"
          :limit="10"
          :column="8"
          :widthTable="'100%'"
          @DataTable:finish="onDataTableFinished"
          ref="datatable">
          <th class="text-center mw_110px maw_145x">Item Details</th>
          <th class="text-center">Part No</th>
          <th class="text-center">Quantity</th>
          <th class="text-center">Item Location</th>
          <th class="text-center">Reason</th>
          <th class="text-center">Write Off By</th>
          <th class="text-center">Edited Time</th>
          <template v-if="isSuperAdmin">
          <th class="text-center">Action</th>
          </template>
        <template slot="body" slot-scope="props">
          <tr>
            <td class="mw_110px maw_145x" :title="props.item.name" >
              <div class="text ellipsis">{{ props.item.name }}</div>
            </td>
            <td class="mw_110px maw_145x" :title="props.item.part_no" >
              <div class="text ellipsis">{{ props.item.part_no }}</div>
            </td>
            <td class="mw_110px maw_145x" :title="props.item.quantity || 0" >
              <div class="text ellipsis">{{ props.item.quantity || 0 }}</div>
            </td>
            <td class="mw_110px maw_145x" :title="props.item.location" >
              <div class="text ellipsis">{{ props.item.location }}</div>
            </td>
            <td class="mw_110px maw_145x">{{ props.item.reason }}</td>
            <td class="mw_110px maw_145x" :title="props.item.write_off_name" >
              <div class="text ellipsis">{{ props.item.write_off_name }}</div>
            </td>
            <td class="mw_110px maw_145x" :title="props.item.created_at | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY')" >
              <div class="text ellipsis">{{ props.item.created_at | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY') }}</div>
            </td>
            <template v-if="isSuperAdmin">
            <td>
                <img src="/images/icons/icon-trash.svg" width="22px" @click.stop="onClickDelete(props.item)">
            </td>
            </template>
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

    <write-off-print
      :data="printData"
      ref="writeOff" />

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
import rf from 'requestfactory'
import moment from 'moment'
import Const from 'common/Const'
import { chain, isEmpty } from 'lodash'
import Datepicker from 'vuejs-datepicker'
import WriteOffPrint from './WriteOffPrint'
import SendReportToEmailModal from '../SendReportToEmailModal'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
import {mapState} from "vuex";

export default {
  components : {
    Datepicker,
    WriteOffPrint,
    SendReportToEmailModal,
  },

  mixins: [RemoveErrorsMixin],

  data () {
    return {
      printData: [],
      fromDate: moment().subtract(30, 'days').toDate(),
      toDate: moment().toDate(),
      Const,
      inputSearch: null,
    }
  },

  computed: {
    ...mapState(['user']),

    isSuperAdmin() {
      return this.user.role === Const.USER_ROLE_SUPER_ADMIN
    },

    fromDateFormat () {
      if(!this.fromDate) return
      return moment(this.fromDate).utc().startOf('day').format(Const.DATETIME_PATTERN)
    },

    toDateFormat () {
      if(!this.toDate) return
      return moment(this.toDate).utc().endOf('day').format(Const.DATETIME_PATTERN)
    },

    disabled () {
      return isEmpty(this.fromDateFormat) || isEmpty(this.toDateFormat)
    }
  },

  methods: {
    getSparesWriteOff(params) {
      params = {
        ...params,
        dates: {
          start: this.fromDateFormat,
          end: this.toDateFormat
        },
        search_key: this.inputSearch,
      }
      return rf.getRequest('SpareRequest').getSparesWriteOff(params)
    },

    onDataTableFinished () {
      this.data = this.$refs.datatable.rows
      // Do something.
    },

    onClickPrint () {
      const params = {
        dates: {
          start: this.fromDateFormat,
          end: this.toDateFormat
        },
        no_pagination: true
      }
      rf.getRequest('SpareRequest').getSparesWriteOff(params)
        .then(res => {
          this.printData = res.data

          this.$nextTick(() => {
            this.$refs.writeOff.print();
          })
        })
    },

    onClickShowEmailModal (item) {
      const callback = async (emails) => {
        return await rf.getRequest('SpareRequest').sendSparesWriteOffReport({ emails })
      }
      this.$modal.show('send-report-to-email-modal', { callback })
    },

    onClickExportCsv() {
      const params = {
        dates: JSON.stringify({
          start: this.fromDateFormat,
          end: this.toDateFormat
        }),
        no_pagination: true,
      }
      const qs = '?' + new URLSearchParams(params).toString()
      window.location.href = `/write-off/export` + qs;
    },

    onClickGenerate () {
      if (this.disabled) {
        return
      }
      this.$refs.datatable.refresh()
    },

    onClickDelete (item) {
      const callback = () => {
        rf.getRequest('SpareRequest').unwriteOffSpareExpired({ id: item.write_off_id })
          .then(res => {
            this.showSuccess('Successful!')
            this.$refs.datatable.refresh()
          })
          .catch(error => {
            this.processAndToastFirstError(error)
          })
      }

      this.confirmAction({
        callback,
        message: "Are you sure you want to delete this item?"
      })
    }
  }
}
</script>
