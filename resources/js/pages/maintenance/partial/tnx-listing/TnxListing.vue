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
          <input type="text" class="input" placeholder="Search Trans ID, Part No ..." v-model="inputSearch" />
        </div>
      </div>

    </div>
    <div class="d-flex ju-center">
      <div class="col-4">
        <div class="form-group">
          <div class="option">
            <label>Cluster:</label>
            <select class="input" v-model="cluster_id" name="cluster_id">
              <option value="">All</option>
              <option v-for="cluster in clusters" :value="cluster.id" :key="cluster.id">
                {{ cluster.name }}
              </option>
            </select>
          </div>
        </div>
      </div>
      <div class="col-4">
        <div class="form-group">
          <div class="option">
            <label>Shelf:</label>
            <select class="input" v-model="shelf_id" name="shelf_id">
              <option value="">All</option>
              <option v-for="shelf in shelfs" :value="shelf.id" :key="shelf.id">
                {{ shelf.name }}
              </option>
            </select>
          </div>
        </div>
      </div>
      <div class="col-4">
        <div class="form-group">
          <div class="option">
            <label>Bin:</label>
            <select class="input" v-model="bin_id" name="bin_id">
              <option value="">All</option>
              <option v-for="bin in bins" :value="bin.id" :key="bin.id">
                {{ bin.bin_name }}
              </option>
            </select>
          </div>
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
            <td >
              <div class="text ellipsis">{{ props.item.id }}</div>
            </td>
            <td>
              <div class="text ellipsis">{{ props.item.created_at | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss',
                'DD-MM-YYYY') }}</div>
            </td>
            <td>
              <div class="text ellipsis" v-for="(row, index) in props.item.locations.spares" :item="row" :index="index">
                <div>
                  {{ row.pivot.listWO == null ? '' : JSON.parse(row.pivot.listWO) }}
                </div>
              </div>
            </td>
            <td >
              <div class="text ellipsis">{{ props.item.vehicle_num || "N/A"}}</div>
            </td>
            <td>
              <div class="text ellipsis">{{ props.item.platform|| "N/A" }}</div>
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
            <td>
              <div class="text ellipsis" v-for="(row, index) in props.item.locations.spares" :item="row" :index="index">
                {{ row.name }}
                <span v-if="index < props.item.locations.spares.length - 1">,</span>
              </div>
            </td>
            <td>
              <div class="text ellipsis" v-for="(row, index) in props.item.locations.spares" :item="row" :index="index">
                {{ row.part_no }}
                <span v-if="index < props.item.locations.spares.length - 1">,</span>
              </div>
            </td>
            <td>
              <div class="text ellipsis">{{ props.item.request_qty }}</div>
            </td>
            <td>
              <div class="text ellipsis">{{ props.item.torque_area || "N/A" }}</div>
            </td>
            <td>
              <div class="text ellipsis">{{ props.item.user.name }}</div>
            </td>
            <td>
              <div class="text ellipsis" v-for="(row, index) in props.item.locations.spares" :item="row" :index="index">
                <div>
                  {{ row.type === 'consumable' ? 'I' : row.type === 'return' ? 'R' : 'L' }}
                  <span v-if="index < props.item.locations.spares.length - 1">,</span>
                </div>
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
  mounted() {
    this.getClusters();
    this.getShelfs();
    this.getBins();
  },
  created() {
    this.getClusters();
    this.getShelfs();
    this.getBins();
  },
  data() {
    return {
      printData: [],
      clusters: [],
      shelfs: [],
      bins: [],
      tnxFrom: moment().subtract(30, 'days').toDate(),
      tnxTo: moment().toDate(),
      inputSearch: null,
      cluster_id: '',
      shelf_id: '',
      bin_id: '',
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
        cluster_id: this.cluster_id,
        shelf_id: this.shelf_id,
        bin_id: this.bin_id,
        issued_date: {
          start: this.tnxFromFormat,
          end: this.tnxToFormat
        },
        search_key: this.inputSearch,
      }
      return rf.getRequest('SpareRequest').getSparesReportByTnx(params)
    },

    getClusters(params) {
      rf.getRequest('AdminRequest').getClusters(params).then(res => this.clusters = res.data.data)
    },
    getShelfs(params) {
      rf.getRequest('AdminRequest').getShelfs(params).then(res => this.shelfs = res.data.data)
    },
    getBins(params) {
      rf.getRequest('AdminRequest').getBins(params).then(res => {
        return this.bins = res.data
      })
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
