<template>
  <div class="spares">
    <div class="grid">
      <div class="spare-item"
        v-for="(item, index) in data"
        :key="index"
        @click.prevent="onSelectBox(item)"
        :class="{ active: item.is_checked }"
        :style="{width: replenishForm ? '300px' : '200px'}"
         v-if="item.visible" >
        <div class="image">
          <img :src="item.url">
        </div>
        <div class="info">
          <div>Item Name: {{ item.name }}</div>
          <div>MPN: {{ item.material_no }}</div>
          <div>SSN: {{ item.part_no }}</div>
          <div>Qty OH: {{ item.quantity_oh || 0 }}</div>
          <div>Bin#: {{ item.cluster_name }} - {{ item.shelf_name }} - {{ item.row }} - {{ item.bin }}</div>
          <template v-if="replenishForm">
            <div>Quantity RL: {{ item.inputForm ? (item.inputForm.quantity || 0) : 0 }}</div>
            <div class="text-center mt-3">
              <button class="btn btn-primary" @click.prevent.stop="onClickFillReplenish(item)">Fill Qty RL</button>
            </div>
          </template>
        </div>
      </div>
    </div>

    <div v-if="noData" class="no-data">There is no data.</div>

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

      .row-input {
        height: 50px;
        width: 100%;
        .input {
          width: 60%;
          float: right;
          padding: 7px 14px;
          border-radius: 4px;
          float: right;
          background-color: #fff;
        }
        ::v-deep .vdp-datepicker {
          width: 60%;
          float: right;
          header {
            color: #000;
          }
          .cell {
            color: #000;
            cursor: pointer;
          }
          .disabled {
            color: #ddd;
            cursor: default;
          }
        }
      }

      .form-input {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 10px;
        .circle {
          border: 1px solid #c7cbce;
          border-radius: 50%;
          padding: 6px 15px;
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
}
</style>
<script>
import BaseItemsManual from './BaseItemsManual'

export default {
  extends: BaseItemsManual
}
</script>
