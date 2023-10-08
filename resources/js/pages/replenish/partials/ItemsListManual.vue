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
          <th>SSN</th>
          <th>Description</th>
          <th>Bin #</th>
          <th>Qty OH</th>
          <template v-if="replenishForm">
            <th>Qty RL</th>
            <th>Action</th>
          </template>
        </thead>
        <tbody>
          <template v-if="noData">
            <tr>
              <td colspan="5">There is no data.</td>
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
              <td><div>{{ item.material_no }}</div></td>
              <td><div>{{ item.part_no }}</div></td>
              <td><div>{{ item.name }}</div></td>
              <td><div>{{ item.cluster_name }} - {{ item.shelf_name }} - {{ item.row }} - {{ item.bin }}</div></td>
              <td><div>{{ item.quantity_oh || 0 }}</div></td>
              <template v-if="replenishForm">
                <td><div>{{ item.inputForm ? (item.inputForm.quantity || 0) : 0 }}</div></td>
                <td>
                  <div><img src="/images/icons/icon-edit.svg" width="20" @click.stop="onClickFillReplenish(item)" /></div>
                </td>
              </template>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="text-right">
      <template v-if="replenishForm">
        <button class="btn btn-second"
          @click.stop="cancelReplenishForm">
          Cancel
        </button>

        <button class="btn btn-primary"
          @click.stop="onCheckout"
          :disabled="noSelectedData">
          Checkout
        </button>

      </template>
      <template v-else>
        <button class="btn btn-primary"
            @click.stop="nextReplenishForm"
            :disabled="noSelectedData" >
            Go to Cart
        </button>
      </template>
    </div>

    <replenish-form-modal @done="handleReplenishFormFinished"/>
    <replenish-manual-print-modal :name="replenishPrintModal"/>
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
  }
}
</style>
<script>
import BaseItemsManual from './BaseItemsManual'

export default {
  extends: BaseItemsManual
}
</script>
