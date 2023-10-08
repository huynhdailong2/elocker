<template>
  <div class="pol mb-3">
    <div class="table-scroller mt-3 mb-3">
      <data-table2 :getData="getPolHistories"
          :limit="10"
          :column="14"
          :widthTable="'100%'"
          @DataTable:finish="onDataTableFinished"
          ref="datatable">
          <th class="text-center">Card Number</th>
          <th class="text-center">Material Number</th>
          <th class="text-center">Description</th>
          <th class="text-center">Purpose of Use</th>
          <th class="text-center">Type</th>
          <th class="text-center">OH Quantity</th>
          <th class="text-center">Quantity</th>
          <th class="text-center">Trans</th>
          <th class="text-center">Handover By</th>
          <th class="text-center">Receiver By</th>
          <th class="text-center">Issue By</th>
          <th class="text-center">Issue To</th>
          <th class="text-center">Replenished At</th>
          <th class="text-center">Issued At</th>

        <template slot="body" slot-scope="props">
          <tr>
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
            <td :title="props.item.item_type | upperFirst" >
              <div class="text ellipsis">{{ props.item.item_type | upperFirst }}</div>
            </td>
            <td :title="props.item.quantity_oh || 0" >
              <div class="text ellipsis">{{ props.item.quantity_oh || 0 }}</div>
            </td>
            <td :title="props.item.quantity || 0" >
              <div class="text ellipsis">{{ props.item.quantity || 0 }}</div>
            </td>
            <td :title="props.item.requester_name ? 'R' : 'I'" >
              <div class="text ellipsis">{{ props.item.requester_name ? 'R' : 'I' }}</div>
            </td>
            <td :title="props.item.requester_name" >
              <div class="text ellipsis">{{ props.item.requester_name }}</div>
            </td>
            <td :title="props.item.receiver_requested_name" >
              <div class="text ellipsis">{{ props.item.receiver_requested_name }}</div>
            </td>
            <td :title="props.item.issuer_name" >
              <div class="text ellipsis">{{ props.item.issuer_name }}</div>
            </td>
            <td :title="props.item.receiver_name" >
              <div class="text ellipsis">{{ props.item.receiver_name }}</div>
            </td>
            <td :title="props.item.updated_at | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY HH:mm')" >
              <div class="text ellipsis">{{ props.item.updated_at | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY HH:mm') }}</div>
            </td>
            <td :title="props.item.updated_at | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY HH:mm')" >
              <div class="text ellipsis">{{ props.item.updated_at | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY HH:mm') }}</div>
            </td>
          </tr>
        </template>
      </data-table2>
    </div>

    <pol-form-modal :name="polFormModal" />
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
    getPolHistories(params) {
      return rf.getRequest('AdminRequest').getPolHistories(params)
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
    }
  }
}
</script>
