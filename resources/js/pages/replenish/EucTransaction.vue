<template>
  <div class="page">
    <div class="title-page">
      <div class="text-center">EUC Transaction</div>
    </div>

    <div class="content block">
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
        <div class="table-scroller mt-3 mb-3">
          <data-table2 :getData="getEucItems"
              :limit="11"
              :column="11"
              :widthTable="'100%'"
              @DataTable:finish="onDataTableFinished"
              ref="datatable">
              <th class="text-center">MPN</th>
              <th class="text-center">SSN</th>
              <th class="text-center">Description</th>
              <th class="text-center">EUC Box #</th>
              <th class="text-center">Quantity</th>
              <th class="text-center">Stock by</th>
              <!-- <th class="text-center">Receive by</th> -->
              <!-- <th class="text-center">Receive Date</th> -->
              <!-- <th class="text-center">Issue by</th> -->
              <th class="text-center">Received by</th>
              <th class="text-center">Received Date</th>
<!--              <th class="text-center">Trans</th>-->
<!--              <th class="text-center">Date</th>-->
            <template slot="body" slot-scope="props">
              <tr>
                <td :title="props.item.material_no" >
                  <div class="text ellipsis">{{ props.item.material_no }}</div>
                </td>
                <td :title="props.item.part_no" >
                  <div class="text ellipsis">{{ props.item.part_no }}</div>
                </td>
                <td :title="props.item.spare_name" >
                  <div class="text ellipsis">{{ props.item.spare_name }}</div>
                </td>
                <td :title="props.item.euc_box_order" >
                  <div class="text ellipsis">EUC Box #{{ props.item.euc_box_order }}</div>
                </td>
                <td :title="props.item.issue_item ? props.item.issue_item.quantity : props.item.quantity || 0" >
                  <div class="text ellipsis">{{ props.item.issue_item ? props.item.issue_item.quantity : props.item.quantity || 0 }}</div>
                </td>
                <td :title="props.item.requester_name" >
                  <div class="text ellipsis">{{ props.item.requester_name }}</div>
                </td>
                <!-- <td :title="props.item.receiver_name" >
                  <div class="text ellipsis">{{ props.item.receiver_name }}</div>
                </td> -->
                <!-- <td :title="props.item.replenish_created_at | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY HH:mm')" >
                  <div class="text ellipsis">{{ props.item.replenish_created_at | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY HH:mm') }}</div>
                </td> -->
                <!-- <td :title="props.item.issue_item ? props.item.issue_item.issued_by : props.item.issued_by" >
                  <div class="text ellipsis">{{ props.item.issue_item ? props.item.issue_item.issued_by : props.item.issued_by }}</div>
                </td> -->
                <td :title="props.item.issue_item ? props.item.issue_item.issued_to : props.item.issued_to" >
                  <div class="text ellipsis">{{ props.item.issue_item ? props.item.issue_item.issued_to : props.item.issued_to }}</div>
                </td>
                <td :title="props.item.issue_item ? props.item.issue_item.issued_date : props.item.issued_date | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY HH:mm')" >
                  <div class="text ellipsis">{{ props.item.issue_item ? props.item.issue_item.issued_date : props.item.issued_date | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY HH:mm') }}</div>
                </td>
<!--                <td :title="props.item.tnx" >-->
<!--                  <div class="text ellipsis">{{ props.item.tnx }}</div>-->
<!--                </td>-->
<!--                <td :title="props.item.created_at | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY')" >-->
<!--                  <div class="text ellipsis">{{ props.item.created_at | dateFormatter(Const.DATETIME_PATTERN, 'DD-MM-YYYY HH:mm') }}</div>-->
<!--                </td>-->
              </tr>
            </template>
          </data-table2>
        </div>
      </div>
    </div>
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
import moment from 'moment'
import rf from 'requestfactory'
import Const from 'common/Const'
import { chain, cloneDeep, remove, isEmpty } from 'lodash'
import Datepicker from 'vuejs-datepicker'

export default {
  components : {
    Datepicker
  },

  data () {
    return {
      printData: [],
      dateFrom: moment().subtract(30, 'days').toDate(),
      dateTo: moment().toDate(),
      Const
    }
  },

  computed: {
    dateFromFormat () {
      if(!this.dateFrom) return
      return moment(this.dateFrom).startOf('day').startOf('day').utc().format(Const.DATETIME_PATTERN)
    },

    dateToFormat () {
      if(!this.dateTo) return
      return moment(this.dateTo).endOf('day').endOf('day').utc().format(Const.DATETIME_PATTERN)
    },

    disabled () {
      return isEmpty(this.dateFromFormat) || isEmpty(this.dateToFormat)
    }
  },

  methods: {
    getEucItems(params) {
      params = {
        ...params,
        dates: {
          start: this.dateFromFormat,
          end: this.dateToFormat
        }
      }
      return rf.getRequest('SpareRequest').getEucItems(params)
    },

    onDataTableFinished () {
      this.data = this.$refs.datatable.rows
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
