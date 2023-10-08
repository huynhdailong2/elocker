<template>
  <div class="spares mb-3">
    <div class="d-flex">
      <div class="form-search">
        <input
          type="text"
          class="input"
          placeholder="Search Part No ..."
          v-model="inputSearch" />
      </div>

      <div class="note">
        <button class="btn is-refresh box">Refresh</button>
        <button class="btn is-expired box">Expired / Less than 2 weeks</button>
        <button class="btn is-in-30-days box">2 – 4 weeks to expired</button>
        <button class="btn is-in-60-days box">More than 4 weeks to 90 days expired</button>
      </div>
    </div>
    <div class="table-scroller mt-3 mb-3">
      <data-table2 :getData="getExpiringItems"
          :limit="10"
          :column="8"
          :widthTable="'100%'"
          @DataTable:finish="onDataTableFinished"
          ref="datatable">
          <th class="text-center" data-sort-field="load_hydrostatic_test_due">Load/Hydrostatic Test Due</th>
          <th class="text-center" data-sort-field="calibration_due">Inspection / Calibration Due</th>
          <th class="text-center" data-sort-field="expiry_date">Expiring Date</th>
          <th class="text-center mw_110px maw_145x">Item Details</th>
          <th class="text-center" data-sort-field="item_type">Item Type</th>
          <th class="text-center">Part No</th>
          <th class="text-center">Quantity</th>
          <th class="text-center">Item Location</th>
        <template slot="body" slot-scope="props">
          <tr :style="{ 'background-color': colorSpare(props.item) }">
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
            <td class="mw_110px maw_145x" :title="props.item.name" >
              <div class="text ellipsis">{{ props.item.name }}</div>
            </td>
            <td class="mw_110px maw_145x" :title="props.item.item_type | upperFirst" >
              <div class="text ellipsis">{{ getLabelByType(props.item.item_type) }}</div>
            </td>
            <td class="mw_110px maw_145x" :title="props.item.part_no" >
              <div class="text ellipsis">{{ props.item.part_no }}</div>
            </td>
            <td class="mw_110px maw_145x" :title="props.item.quantity_oh || 0" >
              <div class="text ellipsis">{{ props.item.quantity_oh || 0 }}</div>
            </td>
            <td class="mw_110px maw_145x" :title="props.item.location" >
              <div class="text ellipsis">{{ props.item.location }}</div>
            </td>
          </tr>
        </template>
      </data-table2>
    </div>
    <div>Note: if the return state of the item is not WORKING, the expiring date will be 1 day before the return date</div>
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

    <ExpiringItemsPrint
      :data="printData"
      ref="expiringItemsPrinter" />

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
        background-color: initial;
      }
    }
  }
  .form-search {
    .input {
      width: 300px;
    }
  }
  .note {
    display: flex;
    width: 100%;
    justify-content: flex-end;
    .box {
      // border: 1px solid;
      border-radius: 0;
      padding: 4px 10px;
      color: #fff;
      & + .box {
        border-left: none;
      }
      &.active {
        box-shadow: 0 0 0 0.2rem rgb(52 144 220 / 25%);
      }
    }
  }
  .is-refresh {
    background: #11131D;
  }
  .is-expired {
    background: #f21501;
  }
  .is-in-30-days {
    background: #f7bf03;
  }
  .is-in-60-days {
    background: #70ad47;
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
import { chain, debounce } from 'lodash'
import ExpiringItemsPrint from './ExpiringItemsPrint'
import SendReportToEmailModal from '../SendReportToEmailModal'
import Const from 'common/Const'

const EXPIRED_ITEM = 1

const STATE = {
  1: { point: EXPIRED_ITEM, name: 'Expired', class: 'is-expired', color: '#f21501' },
  2: { point: 2, name: 'In 30 days', class: 'is-in-30-days', color: '#f7bf03' },
  3: { point: 3, name: 'In 60 Days', class: 'is-in-60-days', color: '#70ad47' },
  4: { point: 4, name: 'Refresh', class: 'is-refresh', color: '#11131D' }
}

export default {
  components : {
    ExpiringItemsPrint,
    SendReportToEmailModal
  },

  data () {
    return {
      inputSearch: null,
      printData: [],
      EXPIRED_ITEM,
      Const
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
    getExpiringItems(params) {
      params = {
        ...params,
        search_key: this.inputSearch,
      }
      return rf.getRequest('SpareRequest').getSparesExpiring(params)
    },

    onDataTableFinished () {
      this.data = this.$refs.datatable.rows

      chain(this.data)
        .each(row => {
          const usersAccessing = chain(row.user_accessing_spares)
            .map(item => item.role)
            .value()
          this.$set(row, 'user_access', usersAccessing)
        })
        .value()
    },

    onClickPrint () {
      const params = {
        no_pagination: true
      }
      rf.getRequest('SpareRequest').getSparesExpiring(params)
        .then(res => {
          this.printData = res.data

          this.$nextTick(() => {
            this.$refs.expiringItemsPrinter.print();
          })
        })
    },

    onClickShowEmailModal (item) {
      const callback = async (emails) => {
        return await rf.getRequest('SpareRequest').sendSparesExpiringReport({ emails })
          .catch(error => {
            this.showError(error.response.data.message)
          })
      }
      this.$modal.show('send-report-to-email-modal', { callback })
    },

    onClickExportCsv() {
      const params = {
        no_pagination: true,
      }
      const qs = '?' + new URLSearchParams(params).toString()
      window.location.href = `/spares-expiring/export` + qs;
    },

    colorSpare (item) {
      // return STATE[item.point].color
      return STATE[item.point_all_date].color
    },

    getLabelByType(type) {
      let matchType = chain(Const.ITEM_TYPE)
          .filter((record) => {
            return record.value == type
          })
          .head()
          .value()

      return matchType ? matchType.name : type;
    }
  }
}
</script>
