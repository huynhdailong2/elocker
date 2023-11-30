<template>
  <div class="spares mb-3">
    <div class="table-scroller mt-3 mb-3">
      <table>
        <thead>
          <th class="no">
            <label class="checkbox-container">No.
              <input type="checkbox" v-model="checkedAll" @click="onClickCheckedAll">
              <span class="checkmark"></span>
            </label>
          </th>
          <th>MPN</th>
          <th>Part No</th>
          <th>Item Details</th>
          <th>Location</th>
          <th>Qty OH</th>
          <th class="w_105px">Calibration Due/Inspection</th>
          <th class="w_105px">Load/Hydrostatic Test Due</th>
          <th class="w_105px">Expiry Date</th>
          <th v-if="issueFormStep">Qty Issue</th>
          <th v-if="issueFormStep" class="mw_140px">Area</th>
          <th v-if="issueFormStep">Torque(N.M)</th>
        </thead>
        <tbody>
          <template v-if="noData">
            <tr>
              <td colspan="9">There is no data.</td>
            </tr>
          </template>
          <template v-else>
            <tr v-for="(item, index) in data" v-if="item.visible">
              <td class="no" @click.prevent="onSelectBox(item)">
                <label class="checkbox-container">{{ index + 1 }}
                  <input type="checkbox" v-model="item.is_checked">
                  <span class="checkmark"></span>
                </label>
              </td>
              <td>
                <div class="text ellipsis">
                  {{ item.spares.material_no ||"N/A" }}
                </div>
              </td>
              <td>
                <div class="text ellipsis">
                  {{ item.spares.part_no ||"N/A"}}
                </div>
              </td>
              <td>
                <div class="text ellipsis">{{ item.spares.name }}</div>
              </td>
              <td>
                <div class="text ellipsis">
                  {{ item.cluster.name }}-{{ item.shelf.name
              }}-{{
                  item.row }}-{{ item.bin }}
                </div>
              </td>
              <td>
                <div>{{ item.spares.pivot.quantity_oh || 0 }}</div>
              </td>
              <td>
                <div v-for="(row, index) in item.configures" :item="row" :index="index">
                  {{ row.calibration_due | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss',
                'DD-MM-YYYY') || "N/A" }}
                </div>
                <!-- <div>{{ getCalibrationDueDate(item) | dateFormatter('YYYY-MM-DD')|| "N/A" }}</div> -->
              </td>
              <td>
                <div v-for="(row, index) in item.configures" :item="row" :index="index">
                  {{ row.load_hydrostatic_test_due | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss',
                'DD-MM-YYYY') || "N/A" }}
                </div>
                <!-- <div>{{ getLoadHydrostaticDueDate(item) | dateFormatter('YYYY-MM-DD') || "N/A" }}</div> -->
              </td>
              <td>
                <div v-for="(row, index) in item.configures" :item="row" :index="index">
                  {{ row.expiry_date | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss',
                'DD-MM-YYYY') || "N/A" }}
                </div>
              </td>
              <td v-if="issueFormStep">
                <div style="gap: 8px" class="form-input">
                  <span class="circle" @click.stop="onClickDecrease(item)">-</span>
                  <input name="Quantity" style="width: 50px; text-align: center;" type="text" v-model="item.newQuantity"  v-validate="`required|numeric|min_value:0|max_value:${item.spares.pivot.quantity_oh || 0}`">
                  <span class="circle" @click.stop="onClickIncrease(item)">+</span>
                </div>
                <span class="invalid-feedback" v-if="errors.has('Quantity')">
                  {{ errors.first("Quantity") }}
                </span>
              </td>
              <td v-if="issueFormStep">
                <template v-if="visibleTorqueArea(item)">
                  <select class="input" :class="{ 'error': errors.has(`${item.scope}.torque_wrench_area_id`) }"
                    v-model="item.torque_wrench_area_id" name="torque_wrench_area_id" :data-vv-scope="`${item.scope}`"
                    data-vv-as="area" v-validate="''">
                    <option :value="item.id" v-for="(item, idx) in torqueAreas" :key="idx">{{ item.area }}</option>
                  </select>
                  <span class="invalid-feedback" v-if="errors.has(`${item.scope}.torque_wrench_area_id`)">
                    {{ errors.first(`${item.scope}.torque_wrench_area_id`) }}
                  </span>
                </template>
              </td>
              <td v-if="issueFormStep">
                <div>{{ getTorqueValue(item) }}</div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="text-right">
      <template v-if="issueFormStep">
        <button class="btn btn-second" @click.stop="onCancel">
          Cancel
        </button>

        <button class="btn btn-primary" @click.stop="onCheckout" :disabled="noQuantity">
          Checkout
        </button>

      </template>
      <template v-else>
        <button class="btn btn-primary" @click.stop="nextIssueFormStep" :disabled="noSelectedData">
          Go to Cart
        </button>
      </template>
    </div>

    <scan-taker-modal :name="scanTakerModal" />
  </div>
</template>
<style lang="scss" scoped>
.spares {
  .form-input {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 10px;

    .circle {
      border: 1px solid #363A47;
      border-radius: 50%;
      padding: 8px 15px;
      cursor: pointer;
      font-weight: bold;
      background-color: #363A47;
      color: #fff;

      &:hover {
        border-color: #3490dc;
      }
    }

    .number {
      border: 1px solid #363A47;
      height: 35px;
      width: 50px;
      line-height: 35px;
      margin: 10px;
      text-align: center;
    }
  }
}
</style>
<script>
import BaseItems from './BaseItems'

export default {
  extends: BaseItems
}
</script>
