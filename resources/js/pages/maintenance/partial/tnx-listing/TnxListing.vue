<template>
  <div class="spares mb-3">
    <div class="d-flex ju-center">
      <div class="col-4">
        <div class="form-group">
          <label for="wo-from" class="w-100 input-title text-white">From (Trans Date)</label>
          <datepicker format="dd/MM/yyyy" input-class="form-control date-selector" v-model="tnxFrom"
            name="calibration_due" />
        </div>
      </div>
      <div class="col-4">
        <div class="form-group">
          <label for="wo-to" class="w-100 input-title text-white">To (Trans Date)</label>
          <datepicker format="dd/MM/yyyy" input-class="form-control date-selector" v-model="tnxTo"
            :disabled-dates="{ to: tnxFrom }" name="calibration_due" />
        </div>
      </div>
      <div class="col-4 filter">
        <div class="form-group">
          <label class="w-100 input-title text-white">Search</label>
          <input type="text" class="input" placeholder="Search Part No ..." v-model="inputSearch" />
        </div>
      </div>
    </div>
    <div class="text-center mb-2 cpx-2">
      <button class="btn btn-primary" :disabled="disabled" @click.stop="onClickGenerate">Generate</button>
    </div>
    <div class="table-scroller mt-3 mb-3">
      <data-table2 :getData="getSparesReportByTnx" :limit="10" :column="13" :widthTable="'100%'"
        @DataTable:finish="onDataTableFinished" ref="datatable">
        <th class="text-center">Trans Id</th>
        <th class="text-center">Trans Date</th>
        <th class="text-center">WO#</th>
        <th class="text-center">Vehicle #</th>
        <th class="text-center">Platform</th>
        <th class="text-center">Location</th>
        <th class="text-center">Item Type</th>
        <th class="text-center">Item Details</th>
        <th class="text-center">Part #</th>
        <th class="text-center">Quantity</th>
        <th class="text-center">Area Use</th>
        <th class="text-center">By</th>
        <th class="text-center">Trans</th>
        <th class="text-center">Expiry</th>
        <template slot="body" slot-scope="props">
          <tr>
            <td :title="props.item.id">
              <div class="text ellipsis">{{ props.item.id }}</div>
            </td>
            <td :title="props.item.created_at | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY')">
              <div class="text ellipsis">{{ props.item.created_at | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss',
                'DD-MM-YYYY') }}</div>
            </td>
            <td :title="props.item.wo">
              <div class="text ellipsis">{{ props.item.wo }}</div>
            </td>
            <td :title="props.item.vehicle_num">
              <div class="text ellipsis">{{ props.item.vehicle_num }}</div>
            </td>
            <td :title="props.item.platform">
              <div class="text ellipsis">{{ props.item.platform }}</div>
            </td>
            <td :title="props.item.location">
              <div class="text ellipsis">{{ props.item.location.bin.cluster_id }}-{{ props.item.cabinet.id }}-{{
                props.item.location.bin.row }}-{{ props.item.location.bin.id }}</div>
            </td>
            <td :title="props.item.spare.type">
              <div class="text ellipsis">{{ props.item.spare.type }}</div>
            </td>
            <td :title="props.item.spare.name">
              <div class="text ellipsis">{{ props.item.spare.name }}</div>
            </td>
            <td :title="props.item.spare.name">
              <div class="text ellipsis">{{ props.item.spare.part_no }}</div>
            </td>
            <td :title="props.item.qty">
              <div class="text ellipsis">{{ props.item.qty }}</div>
            </td>
            <td :title="props.item.torque_area">
              <div class="text ellipsis">{{ props.item.torque_area || "N/A" }}</div>
            </td>
            <td :title="props.item.user.name">
              <div class="text ellipsis">{{ props.item.user.name }}</div>
            </td>
            <td :title="props.item.spare.type">
              <div class="text ellipsis">
                <div v-if="props.item.spare.type === 'consumable'">I</div>
                <div v-else-if="props.item.type === 'return'">R</div>
                <div v-else>L</div>
              </div>
            </td>
            <td title="N/A">
              <div class="text ellipsis">N/A</div>
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

    <TnxListingPrint :data="printData" ref="tnxListingPrint" />

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

      &+.box {
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
import TnxListingPrint from './TnxListingPrint'
import SendReportToEmailModal from '../SendReportToEmailModal'
import Datepicker from 'vuejs-datepicker'


export default {
  components: {
    TnxListingPrint,
    SendReportToEmailModal,
    Datepicker
  },

  data() {
    return {
      printData: [],
      tnxFrom: moment().subtract(30, 'days').toDate(),
      tnxTo: moment().toDate(),
      inputSearch: null,
    }
  },

  computed: {
    tnxFromFormat() {
      if (!this.tnxFrom) return
      return moment(this.tnxFrom).utc().startOf('day').format(Const.DATETIME_PATTERN)
    },
    tnxToFormat() {
      if (!this.tnxTo) return
      return moment(this.tnxTo).utc().endOf('day').format(Const.DATETIME_PATTERN)
    },
    disabled() {
      return isEmpty(this.tnxFromFormat) || isEmpty(this.tnxToFormat)
    }
  },

  methods: {
    async getData() {
      if (this.disabled) return Promise.resolve([])
      return this.getSparesReportByTnx()
    },

    getSparesReportByTnx(params) {
      params = {
        ...params,
        issued_date: {
          start: this.tnxFromFormat,
          end: this.tnxToFormat
        },
        search_key: this.inputSearch,
      }
      return rf.getRequest('SpareRequest').getSparesReportByTnx(params)
    },

    onDataTableFinished() {
      this.data = this.$refs.datatable.rows
      // Do something.
    },

    onClickPrint() {
      const params = {
        issued_date: {
          start: this.tnxFromFormat,
          end: this.tnxToFormat
        },
        no_pagination: true
      }
      rf.getRequest('SpareRequest').getSparesReportByTnx(params)
        .then(res => {
          this.printData = res.data

          this.$nextTick(() => {
            this.$refs.tnxListingPrint.print();
          })
        })
    },

    onClickShowEmailModal(item) {
      const callback = async (emails) => {
        const params = {
          issued_date: {
            start: this.tnxFromFormat,
            end: this.tnxToFormat
          },
          no_pagination: true,
          emails: emails,
        }
        return await rf.getRequest('SpareRequest').sendSparesByTnxReport(params)
      }
      this.$modal.show('send-report-to-email-modal', { callback })
    },

    onClickExportCsv() {
      const params = {
        issued_date: JSON.stringify({
          start: this.tnxFromFormat,
          end: this.tnxToFormat
        }),
        no_pagination: true,
      }
      const qs = '?' + new URLSearchParams(params).toString()
      window.location.href = `/spares-tnx/export` + qs;
    },

    onClickGenerate() {
      if (this.disabled) {
        return
      }
      this.$refs.datatable.refresh()
    }
  }
}
</script>
