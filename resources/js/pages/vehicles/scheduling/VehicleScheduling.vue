<template>
  <div class="page notifications">
    <div class="title-page">
      <div class="text-center">Vehicle Scheduling</div>
    </div>
    <div class="captions">
      <div class="due-1">Due within 1 month</div>
      <div class="due-2">Due in 2 months time</div>
      <div class="due-3">Due in 3 months time</div>
    </div>
    <div class="content block">
      <div class="vehicles">
        <data-table2 :getData="getVehicles"
            :limit="10"
            :column="9"
            :widthTable="'100%'"
            @DataTable:finish="onDataTableFinished"
            ref="datatable">
            <th class="text-center">S/N</th>
            <th class="text-center">Veh no.</th>
            <!-- <th class="text-center">Vehicle Type</th> -->
            <th class="text-center" data-sort-field="vehicle_types.name">Variant</th>
            <th class="text-center" data-sort-field="unit">Unit</th>
            <th class="text-center">Last O Point Servicing</th>
            <th class="text-center mw_110px" data-sort-field="completion_date_6_months">6 mth Plan</th>
            <th class="text-center mw_110px" data-sort-field="completion_date_12_months">12 mth Plan</th>
            <th class="text-center mw_110px" data-sort-field="completion_date_18_months">18 mth Plan</th>
            <th class="text-center mw_110px" data-sort-field="completion_date_24_months">24 mth Plan</th>
          <template slot="body" slot-scope="props">
            <tr>
              <td><div class="text-center">{{ props.realIndex }}</div></td>
              <td><div class="text-center">{{ props.item.vehicle_num }}</div></td>
              <td><div class="text-center">{{ props.item.variant }}</div></td>
              <!-- <td><div class="text-center">{{ props.item.variant | uppercase}}</div></td> -->
              <td><div class="text-center">{{ props.item.unit | uppercase }}</div></td>
              <td><div class="text-center">{{ props.item.last_point_servicing | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div></td>
              <td :class="getBbClass(props.item, 'schedule_6_months', 'completion_date_6_months')">
                <div class="text-center">{{ props.item.schedule_6_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
              </td>
              <td :class="getBbClass(props.item, 'schedule_12_months', 'completion_date_12_months')">
                <div class="text-center">{{ props.item.schedule_12_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
              </td>
              <td :class="getBbClass(props.item, 'schedule_18_months', 'completion_date_18_months')">
                <div class="text-center">{{ props.item.schedule_18_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
              </td>
              <td :class="getBbClass(props.item, 'schedule_24_months', 'completion_date_24_months')">
                <div class="text-center">{{ props.item.schedule_24_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
              </td>
            </tr>
          </template>
        </data-table2>
      </div>

      <div class="action d-flex">
        <button class="btn btn-primary" @click.stop="onClickPrint()" style="margin-right: 16px;">
          Print
        </button>
        <button class="btn btn-primary" @click.stop="onClickDownloadExcel()">
          Excel
        </button>
      </div>

      <vehicle-scheduling-print :data="printData" ref="vehicleSchedulingPrint" />
    </div>
  </div>
</template>
<style lang="scss" scoped>
.page {
  .captions {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 10px;
    div {
      padding: 10px;
    }
  }
  .due-1 {
    background-color: #E3342F;
  }
  .due-2 {
    background-color: #E1A713;
  }
  .due-3 {
    background-color: #669827;
  }
  .green {
    background-color: #669827;
  }
  .content {
    width: 100%;
    .vehicles {
      width: 100%;
      .table-content {
        ::v-deep .box_table {
        }
      }
    }
    .action {
      flex-direction: row;
      padding-top: 30px;
      justify-content: flex-end;
      button {
        min-width: 100px;
      }
    }
  }
}
</style>
<script>
import rf from 'requestfactory'
import Const from 'common/Const'
import { chain } from 'lodash'
import moment from 'moment'
import VehicleSchedulingPrint from "./VehicleSchedulingPrint";

export default {
  components: {VehicleSchedulingPrint},
  data () {
    return {
      data: [],
      printData: [],
      Const
    }
  },

  methods: {
    getVehicles(params) {
      params = {
        ...params,
        is_opened: true
      }
      return rf.getRequest('VehicleRequest').getVehicles(params)
    },

    onDataTableFinished () {
      this.data = this.$refs.datatable.rows
    },

    getBbClass (record, attrPlan, attrCompletion) {
      const planDate = record[attrPlan]
      if (!planDate) {
        return
      }

      const completionDate = record[attrCompletion]
      if (completionDate) {
        return 'green'
      }

      const diff = moment(planDate).diff(moment(), 'months', true)
      return `due-${Math.ceil(diff)}`
    },

    onClickPrint () {
      const params = {
        ...params,
        is_opened: true,
        no_pagination: true
      }

      this.getVehicles(params).then(res => {
        this.printData = res.data

        this.$nextTick(() => {
          this.$refs.vehicleSchedulingPrint.print()
        })
      })
    },

    onClickDownloadExcel() {
      const params = {
        no_pagination: true
      }
      return rf.getRequest('VehicleRequest').getDownloadExcelVehicles(params)
        .then((response) => {
          console.log(response);
          var fileURL = window.URL.createObjectURL(new Blob([response.data]));
          var fileLink = document.createElement('a');

          fileLink.href = fileURL;
          fileLink.setAttribute('download', 'vehicle-scheduling-' + moment().format('YYYY-MM-DD')  + '.xlsx');
          document.body.appendChild(fileLink);

          fileLink.click();
        });
    }
  }
}
</script>
