<template>
  <div class="spares mb-3">
    <div class="d-flex ju-center">
      <div class="col-6">
        <div class="form-group">
          <label for="wo-from" class="w-100 input-title text-white">From (WO#)</label>
          <input type="number" class="form-control" id="wo-from" v-model="woFrom">
        </div>
      </div>
      <div class="col-6">
        <div class="form-group">
          <label for="wo-to" class="w-100 input-title text-white">To (WO#)</label>
          <input type="number" class="form-control" id="wo-to" v-model="woTo">
        </div>
      </div>
    </div>
    <div class="text-center mb-2 cpx-2">
      <button class="btn btn-primary" :disabled="disabled" @click.stop="onClickGenerate">Generate</button>
    </div>
    <div class="table-scroller mt-3 mb-3">
      <data-table2 :getData="getYetToReturnSpares"
          :limit="10"
          :column="12"
          :widthTable="'100%'"
          @DataTable:finish="onDataTableFinished"
          ref="datatable">
          <th class="text-center">WO#</th>
          <th class="text-center">Trans Date</th>
          <th class="text-center">Vehicle#</th>
          <th class="text-center">Platform</th>
          <th class="text-center">Item Details</th>
          <th class="text-center">Part No</th>
          <th class="text-center">Quantity</th>
          <th class="text-center">Area Use</th>
          <th class="text-center">Issue By</th>
          <th class="text-center">Issue To</th>
          <th class="text-center">Trans</th>
          <th class="text-center">Expiry</th>
        <template slot="body" slot-scope="props">
          <tr>
            <td :title="props.item.wo" >
              <div class="text ellipsis">{{ props.item.wo }}</div>
            </td>
            <td :title="props.item.issued_date | dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY')" >
              <div class="text ellipsis">{{ props.item.issued_date | dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY') }}</div>
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
            <td :title="props.item.quantity" >
              <div class="text ellipsis">{{ props.item.quantity }}</div>
            </td>
            <td :title="props.item.torque_area" >
              <div class="text ellipsis">{{ props.item.torque_area }}</div>
            </td>
            <td :title="props.item.issued_by" >
              <div class="text ellipsis">{{ props.item.issued_by }}</div>
            </td>
            <td :title="props.item.issued_to" >
              <div class="text ellipsis">{{ props.item.issued_to }}</div>
            </td>
            <td :title="props.item.tnx" >
              <div class="text ellipsis">{{ props.item.tnx }}</div>
            </td>
            <td title="N/A" >
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
      <button class="btn btn-primary" @click.stop="onClickShowEmailModal">
        Email
      </button>
      <button class="btn btn-primary" @click.stop="onClickPrint()">
        Print
      </button>
    </div>

    <WOListingPrint
      :data="printData"
      ref="wOListingPrint" />

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
import WOListingPrint from './WOListingPrint'
import SendReportToEmailModal from '../SendReportToEmailModal'

export default {
  components : {
    WOListingPrint,
    SendReportToEmailModal
  },

  data () {
    return {
      printData: [],
      woFrom: '',
      woTo: '',
      noPagination: false
    }
  },

  computed: {
    disabled () {
      return isEmpty(this.woFrom) || isEmpty(this.woTo)
    }
  },

  methods: {
    getYetToReturnSpares(params) {
      params = {
        ...params,
        wo: {
          start: this.woFrom,
          end: this.woTo
        }
      }
      return rf.getRequest('SpareRequest').getSparesReportByWo(params)
    },

    onDataTableFinished () {
      this.data = this.$refs.datatable.rows
      // Do something.
    },

    onClickPrint () {
      this.noPagination = true
      const params = {
        no_pagination: this.noPagination,
        wo: {
          start: this.woFrom,
          end: this.woTo
        }
      }
      rf.getRequest('SpareRequest').getSparesReportByWo(params)
        .then(res => {
          this.printData = res.data

          this.$nextTick(() => {
            this.$refs.wOListingPrint.print();
          })
        })
    },

    onClickShowEmailModal (item) {
      const callback = async (emails) => {
        return await rf.getRequest('SpareRequest').sendSparesReportByWo({ emails })
          .catch(error => {
            this.showError(error.response.data.message)
          })
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
