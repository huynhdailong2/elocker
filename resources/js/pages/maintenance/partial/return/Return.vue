<template>
  <div class="spares mb-3">
    <div class="d-flex ju-center">
      <div class="col-4">
        <div class="form-group">
          <label for="wo-from" class="w-100 input-title text-white">From (Return Date)</label>
          <datepicker
            format="dd/MM/yyyy"
            input-class="form-control date-selector"
            v-model="returnFrom"
            name="calibration_due" />
        </div>
      </div>
      <div class="col-4">
        <div class="form-group">
          <label for="wo-to" class="w-100 input-title text-white">TO (Return Date)</label>
          <datepicker
            format="dd/MM/yyyy"
            input-class="form-control date-selector"
            v-model="returnTo"
            :disabled-dates="{to: returnFrom}"
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
      <button class="btn btn-primary" @click.stop="onClickGenerate">Generate</button>
    </div>
    <div class="table-scroller mt-3 mb-3">
      <data-table2 :getData="getSparesReportForReturns"
          :limit="10"
          :column="14"
          :widthTable="'100%'"
          @DataTable:finish="onDataTableFinished"
          ref="datatable">
          <th class="text-center">Trans ID</th>
          <th class="text-center">Trans Date</th>
          <!-- <th class="text-center">Vehicle #</th> -->
          <!-- <th class="text-center">Platform</th> -->
          <th class="text-center">Item Details</th>
          <th class="text-center">Part #</th>
          <th class="text-center">Quantity</th>
          <th class="text-center">Location</th>
          <th class="text-center">Item type</th>
          <!-- <th class="text-center">Area Use</th> -->
          <th class="text-center">Load/Hydrostatic Test Due</th>
          <th class="text-center">Expiry Date</th>
          <th class="text-center">Calibration Date</th>
          <th class="text-center">Return By</th>
          <th class="text-center">Trans</th>
          <th class="text-center">Status</th>
          <th class="text-center">Action</th>
        <template slot="body" slot-scope="props">
          <tr :style="{ 'background-color': props.item.not_use ? '#f21501' : '' }">
            <td :title="props.item.id" >
              <div class="text ellipsis">{{ props.item.id }}</div>
            </td>
            <td :title="props.item.created_at | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY')" >
              <div class="text ellipsis">{{ props.item.created_at | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY') }}</div>
            </td>
            <!-- <td :title="props.item.vehicle_num" >
              <div class="text ellipsis">{{ props.item.vehicle_num }}</div>
            </td> -->
            <td>
              <div class="text ellipsis" v-for="(row, index) in props.item.locations.spares" :item="row" :index="index">
                {{ row.spare_name }}
                <span v-if="index < props.item.locations.spares.length - 1">,</span>
              </div>
            </td>
            <td>
              <div class="text ellipsis" v-for="(row, index) in props.item.locations.spares" :item="row" :index="index">
                {{ row.part_no }}
                <span v-if="index < props.item.locations.spares.length - 1">,</span>
              </div>
            </td>
            <td :title="props.item.request_qty" >
              <div class="text ellipsis">{{ props.item.request_qty }}</div>
            </td>
            <td :title="props.item.locations">
              <div class="text ellipsis">{{ props.item.locations.bin.cluster_id }}-{{ props.item.locations.cabinet.id
              }}-{{
                  props.item.locations.bin.row }}-{{ props.item.locations.bin.id }}</div>
            </td>
            <td>
              <div class="text ellipsis" v-for="(row, index) in props.item.locations.spares" :item="row" :index="index">
                {{ row.type }}
                <span v-if="index < props.item.locations.length - 1">,</span>
              </div>
            </td>
            <td class="mw_110px maw_145x" >
              <div class="text ellipsis" v-for="(row, index) in props.item.locations.spares" :item="row" :index="index">
                {{ row.load_hydrostatic_test_due | dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY')|| "N/A" }}
                <span v-if="index < props.item.locations.spares.length - 1">,</span>
              </div>
            </td>
            <td class="mw_110px maw_145x" >
              <div class="text ellipsis" v-for="(row, index) in props.item.locations.spares" :item="row" :index="index">
                {{ row.calibration_due | dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY')|| "N/A" }}
                <span v-if="index < props.item.locations.spares.length - 1">,</span>
              </div>
            </td>
            
            <td class="mw_110px maw_145x" >
              <div class="text ellipsis" v-for="(row, index) in props.item.locations.spares" :item="row" :index="index">
                {{ row.expiry_date | dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY') || "N/A"}}
                <span v-if="index < props.item.locations.spares.length - 1">,</span>
              </div>
            </td>
            <td>
              <div class="text ellipsis">{{ props.item.user.name }}</div>
            </td>
            <td class="mw_110px maw_145x" >
              <div class="text ellipsis" v-for="(row, index) in props.item.locations.spares" :item="row" :index="index">
                <div>
                  {{ row.type === 'consumable' ? 'I' : row.type === 'return' ? 'R' : 'L' }}
                  <span v-if="index < props.item.locations.spares.length - 1">,</span>
                </div>
              </div>
            </td>
            <td>
              <div class="text ellipsis">{{ props.item.status }}</div>
            </td>
            <td>
              <button class="btn btn-primary w_95px"
                @click.stop="onClickWriteOff(props.item)">
                Write Off
              </button>
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

    <ReturnPrint
      :data="printData"
      ref="returnPrint" />

    <send-report-to-email-modal />
    <write-off-modal :name="writeOffModal" />

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
    .box_table {
      td {
        background-color: initial;
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
import ReturnPrint from './ReturnPrint'
import SendReportToEmailModal from '../SendReportToEmailModal'
import Datepicker from 'vuejs-datepicker'
import WriteOffModal from './WriteOffModal'

const BUTTON_FILTER = {
  ALL: 'all',
  REFRESH: 'refresh',
  EXPIRED: 'expired',
  ONE_MONTH: 'in 30 days',
  TWO_MONTHS: 'in 60 days'
}

export default {
  components : {
    ReturnPrint,
    SendReportToEmailModal,
    WriteOffModal,
    Datepicker
  },

  data () {
    return {
      printData: [],
      returnFrom: moment().subtract(30, 'days').toDate(),
      returnTo: moment().toDate(),
      writeOffModal: 'write-off-modal',
      inputSearch: null,
    }
  },

  computed: {
    returnFromFormat () {
      if(!this.returnFrom) return
      return moment(this.returnFrom).startOf('day').format(Const.DATETIME_PATTERN)
    },
    returnToFormat () {
      if(!this.returnTo) return
      return moment(this.returnTo).endOf('day').format(Const.DATETIME_PATTERN)
    },
    disabled () {
      return isEmpty(this.returnFromFormat) || isEmpty(this.returnToFormat)
    }
  },

  methods: {
    getSparesReportForReturns(params) {
      params = {
        ...params,
        returned_date: {
          start: this.returnFromFormat,
          end: this.returnToFormat
        },
        search_key: this.inputSearch,
      }
      return rf.getRequest('SpareRequest').getSparesReportForReturns(params)
    },

    onDataTableFinished () {
      this.data = this.$refs.datatable.rows
      // Do something.
    },

    onClickPrint () {
      const params = {
        returned_date: {
          start: this.returnFromFormat,
          end: this.returnToFormat
        },
        no_pagination: true
      }
      rf.getRequest('SpareRequest').getSparesReportForReturns(params)
        .then(res => {
          this.printData = res.data

          this.$nextTick(() => {
            this.$refs.returnPrint.print();
          })
        })
    },

    onClickShowEmailModal (item) {
      const callback = async (emails) => {
        return await rf.getRequest('SpareRequest').sendSparesByReturnsReport({ emails })
      }
      this.$modal.show('send-report-to-email-modal', { callback })
    },

    onClickExportCsv() {
      const params = {
        returned_date: JSON.stringify({
          start: this.returnFromFormat,
          end: this.returnToFormat
        }),
        no_pagination: true,
      }
      const qs = '?' + new URLSearchParams(params).toString()
      window.location.href = `/spares-returns/export` + qs;
    },

    onClickGenerate () {
      if (this.disabled) {
        return
      }
      this.$refs.datatable.refresh()
    },

    onClickWriteOff (item) {
      const callback = () => {
        this.$refs.datatable.refresh()
      }

      this.$modal.show(this.writeOffModal, { data: item, callback })
    }
  }
}
</script>
