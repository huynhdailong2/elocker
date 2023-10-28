<template>
  <div class="spares">
    <div class="grid">
      <div class="spare-item"
        v-for="(item, index) in data"
        :key="index"
        @click.prevent="onSelectBox(item)"
        :class="{ active: item.is_checked }"
        :style="{width: issueFormStep ? '300px' : '200px'}"
         v-if="item.visible" >
        <div class="image">
          <img :src="item.spares.url">
        </div>
        <div class="info">
          <div>Item Name: {{ item.spares.name }}</div>
          <div>MPN: {{ item.spares.material_no ||"N/A" }}</div>
          <div>SSN: {{ item.spares.part_no ||"N/A"}}</div>
          <div>Qty OH: {{ item.spares.pivot.quantity_oh || 0 }}</div>
          <div  v-for="(row, index) in item.configures" :item="row" :index="index">Calibration Due/Inspection: {{ row.calibration_due | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss',
                'DD-MM-YYYY') || "N/A" }}</div>
          <div v-for="(row, index) in item.configures" :item="row" :index="index">Load/Hydrostatic Test Due: {{ row.load_hydrostatic_test_due | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss',
                'DD-MM-YYYY') || "N/A" }}</div>
          <div v-for="(row, index) in item.configures" :item="row" :index="index">Expiry Date:  {{ row.expiry_date | dateTimeFormatterLocal('YYYY-MM-DD HH:mm:ss',
                'DD-MM-YYYY') || "N/A" }}</div>
          <div>Location:  {{ item.cluster.name }}-{{ item.shelf.name
              }}-{{
                  item.row }}-{{ item.bin }}</div>
          <div v-if="issueFormStep">
            <template v-if="visibleTorqueArea(item)">
              <span>Area</span>
              <select
                  class="input"
                  @click.prevent.stop="() => {}"
                  :class="{'error': errors.has(`${item.scope}.torque_wrench_area_id`)}"
                  v-model="item.torque_wrench_area_id"
                  name="torque_wrench_area_id"
                  :data-vv-scope="`${item.scope}`"
                  data-vv-as="area"
                  v-validate="''" >
                <option :value="item.id" v-for="(item, idx) in torqueAreas" :key="idx">{{ item.area }}</option>
              </select>
              <div class="mt-1">Torque(N.M): {{ getTorqueValue(item) }}</div>
            </template>
          </div>
          <div class="form-input" v-if="issueFormStep">
            <span class="circle" @click.prevent.stop="onClickDecrease(item)">-</span>
            <span class="number">{{ item.quantity }}</span>
            <span class="circle" @click.prevent.stop="onClickIncrease(item)">+</span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="noData" class="no-data">There is no data.</div>

    <div class="text-right">
      <template v-if="issueFormStep">
        <button class="btn btn-second"
            @click.stop="onCancel">
            Cancel
        </button>

        <button class="btn btn-primary"
            @click.stop="onCheckout"
            :disabled="noQuantity">
            Checkout
        </button>

      </template>
      <template v-else>
        <button class="btn btn-primary"
            @click.stop="nextIssueFormStep"
            :disabled="noSelectedData" >
            Go to Cart
        </button>
      </template>
    </div>

    <scan-taker-modal :name="scanTakerModal"/>
  </div>
</template>
<style lang="scss" scoped>
.spares {
  .grid {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    max-height: 500px;
    overflow-x: auto;
    margin-top: 20px;
    margin-bottom: 20px;
    .spare-item {
      width: 200px;
      border: 1px solid #363A47;
      margin-right: 5px;
      margin-bottom: 5px;
      cursor: pointer;
      .image {
        width: 100%;
        height: 130px;
        img {
          width: 100%;
          height: 100%;
        }
      }
      .info {
        padding: 10px;
        select {
          width: 60%;
        }
      }
      &.active {
        color: #fff;
        border-color: #3490dc;
        background-color: #3490dc;
      }

      .form-input {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 10px;
        .circle {
          border: 1px solid #363A47;
          border-radius: 50%;
          padding: 6px 15px;
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
  }
}
</style>
<script>
import BaseItems from './BaseItems'

export default {
  extends: BaseItems
}
</script>
