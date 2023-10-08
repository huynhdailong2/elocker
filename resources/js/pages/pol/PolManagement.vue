<template>
  <div>
    <div class="pol mb-3">
      <div class="mb-2 cpx-2">
        <button class="btn btn-primary" @click.stop="onClickAddNew">Add New POL</button>
        <div class="note">
          <button class="btn is-less-1month box">Expired or less than 1 month</button>
          <button class="btn in-or-less-60-days box">2 months</button>
          <button class="btn in-or-more-60-days box">2 months or more</button>
        </div>
      </div>
      <div class="table-scroller mt-3 mb-3">
        <data-table2 :getData="getPolManagements"
            :limit="10"
            :column="12"
            :widthTable="'100%'"
            @DataTable:finish="onDataTableFinished"
            ref="datatable">
            <th class="text-center">Card Number</th>
            <th class="text-center">Material Number</th>
            <th class="text-center">Description</th>
            <th class="text-center">Purpose of Use</th>
            <th class="text-center">Type</th>
            <th class="text-center">OH Qty</th>
            <!-- <th class="text-center">Demand Date</th>
            <th class="text-center">Demand Qty</th> -->
            <th class="text-center">Received Date</th>
            <th class="text-center">Received Qty (Ltr)</th>
            <th class="text-center">Issued Date</th>
            <th class="text-center">Issued Qty (Ltr)</th>
            <th class="text-center">Expiry Date</th>
            <th class="text-center">Last Edited</th>
            <th class="text-center mw_140px">Action</th>

          <template slot="body" slot-scope="props">
            <tr :style="{ 'background-color': colorSpare(props.item) }">
              <td :title="props.item.card_number" >
                <div class="text ellipsis">{{ props.item.card_number }}</div>
              </td>
              <td :title="props.item.material_number" >
                <div class="text ellipsis">{{ props.item.material_number }}</div>
              </td>
              <td :title="props.item.description" >
                <div class="text ellipsis">{{ props.item.description }}</div>
              </td>
              <td :title="props.item.purpose_use" >
                <div class="text ellipsis">{{ props.item.purpose_use }}</div>
              </td>
              <td :title="props.item.type | upperFirst" >
                <div class="text ellipsis">{{ props.item.type | upperFirst }}</div>
              </td>
              <td :title="props.item.quantity_oh || 0" >
                <div class="text ellipsis">{{ props.item.quantity_oh || 0 }}</div>
              </td><!-- 
              <td :title="props.item.request_date | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY')" >
                <div class="text ellipsis">{{ props.item.request_date | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY HH:mm') }}</div>
              </td>
              <td :title="props.item.request_quantity || 0" >
                <div class="text ellipsis">{{ props.item.request_quantity || 0 }}</div>
              </td> -->
              <td :title="props.item.received_date | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY')" >
                <div class="text ellipsis">{{ props.item.received_date | dateFormatter(Const.DATETIME_PATTERN, Const.DATE_PATTERN) }}</div>
              </td>
              <td :title="props.item.received_quantity || 0" >
                <div class="text ellipsis">{{ props.item.received_quantity || 0 }}</div>
              </td>
              <td :title="props.item.issued_date | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY')" >
                <div class="text ellipsis">{{ props.item.issued_date | dateFormatter(Const.DATETIME_PATTERN, Const.DATE_PATTERN) }}</div>
              </td>
              <td :title="props.item.issued_quantity || 0" >
                <div class="text ellipsis">{{ props.item.issued_quantity || 0 }}</div>
              </td>
              <td :title="props.item.expiry_date | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY')" >
                <div class="text ellipsis">{{ props.item.expiry_date | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY') }}</div>
              </td>
              <td :title="props.item.updated_at | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY HH:mm')" >
                <div class="text ellipsis">{{ props.item.updated_at | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY HH:mm') }}</div>
              </td>
              <td class="action">
                <img src="/images/icons/icon-edit.svg" width="22px" @click.stop="onClickEditPol(props.item, props.index)">
                <img src="/images/icons/icon-trash.svg" width="22px" @click.stop="onClickDelete(props.item, props.index)">
              </td>
            </tr>
          </template>
        </data-table2>
      </div>

      <pol-form-modal :name="polFormModal" />
    </div>
  </div>
</template>
<style lang="scss" scoped>
::v-deep.pol {
  .cpx-2 {
    padding-left: 2px;
    padding-right: 2px;
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
    td {
      background-color: initial;
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
.note {
  display: inline;
  float: right;
  margin-top: 5px;
  .box {
    margin-left: -5px;
    border-radius: 0;
    color: #fff;
    & + .box {
      border-left: none;
    }
    &.active {
      box-shadow: 0 0 0 0.2rem rgb(52 144 220 / 25%);
    }
  }
}
.is-less-1month {
  background: #f21501;
}
.in-or-less-60-days {
  background: #f7bf03;
}
.in-or-more-60-days {
  background: #70ad47;
}
.action {
  padding-top: 30px;
  justify-content: space-between;
  button {
    min-width: 100px;
  }
}
</style>
<script>
import moment from 'moment'
import rf from 'requestfactory'
import Const from 'common/Const'
import { chain } from 'lodash'
import PolFormModal from './partial/PolFormModal'

export default {
  components : {
    PolFormModal
  },

  data () {
    return {
      data: [],
      polFormModal: 'pol-form-modal',
      Const
    }
  },

  methods: {
    getPolManagements(params) {
      return rf.getRequest('AdminRequest').getPolManagements(params)
    },

    onDataTableFinished () {
      this.data = this.$refs.datatable.rows
    },

    onClickAddNew () {
      const callback = () => {
        if (this.$refs.datatable) {
          this.$refs.datatable.refresh()
        }
      }

      this.$modal.show(this.polFormModal, { callback })
    },

    onClickEditPol (record, index) {
      const callback = () => {
        if (this.$refs.datatable) {
          this.$refs.datatable.refresh()
        }
      }

      this.$modal.show(this.polFormModal, { data: record, callback })
    },

    onClickDelete (record, index) {
      const callback = () => {
        rf.getRequest('AdminRequest').deletePolManagements({ ids: [record.id] }).then(() => {
            this.showSuccess();
            this.$refs.datatable.refresh();
          })
          .catch(error => {
            this.processErrors(error);
          })
      }

      this.confirmAction({ callback })
    },

    colorSpare (record) {
      if (!record.expiry_date) {
        return '#70ad47'
      }

      const today = moment()
      const expiryDate = moment.utc(record.expiry_date, Const.DATE_PATTERN).local()
      const diffMonths = expiryDate.diff(today, 'months', true)

      if (expiryDate < today || diffMonths < 1) {
        return '#f21501'
      }

      if (diffMonths >= 1 && diffMonths < 2) {
        return '#f7bf03'
      }

      return '#70ad47'
    }
  }
}
</script>
