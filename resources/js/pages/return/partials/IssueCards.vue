<template>
  <div class="spares">
    <div class="view">
      <div class="option" :class="{active: selectedMode === MODE.LIST}" @click.stop="selectedMode = MODE.LIST">
        <img src="/images/icons/icon-list.svg" width="20">
        <span>List</span>
      </div>
      <div class="option" :class="{active: selectedMode === MODE.GRID}" @click.stop="selectedMode = MODE.GRID">
        <img src="/images/icons/icon-grid.svg" width="20">
        <span>Image</span>
      </div>
    </div>

    <template v-if="selectedMode === MODE.GRID">
      <div class="grid">
        <div class="spare-item"
          v-for="(item, index) in data"
          :key="index"
          @click.prevent="onSelectBox(item)"
          :class="{ active: item.is_checked }"
          v-if="item.visible" >
          <div class="image">
            <img :src="item.url">
          </div>
          <div class="info row">
            <div class="col-sm-7">
              <div>Item Name: {{ item.spare.name }}</div>
              <div>MPN: {{ item.spare.material_no }}</div>
              <div>SSN: {{ item.spare.part_no }}</div>
              <div>Bin#: {{ item.bin.shelf_id }} - {{ item.bin.row }} - {{ item.bin.bin }}</div>
              <div>Qty On Loan: {{ item.quantity_loan || 0 }}</div>
            </div>
            <div class="col-sm-5">
              <div class="state">
                <div class="btn btn-primary"
                    v-for="state in SPARE_STATE"
                    :class="{selected: item.state === state}"
                    @click.stop="item.state = state" >
                  {{ state | upperFirst }}
                </div>
              </div>
            </div>
          </div>

          <div class="form-input">
            <span class="circle" @click.prevent.stop="onClickDecrease(item)">-</span>
            <span class="number">{{ item.newQuantity }}</span>
            <span class="circle" @click.prevent.stop="onClickIncrease(item)">+</span>
          </div>
          <div class="form-input">
            <span class="btn-mo" @click.stop="onClickLinkMO(item)">WO/Svc#</span>
          </div>
        </div>
      </div>
      <div  v-if="noData" class="no-data">There is no data.</div>
    </template>

    <template v-if="selectedMode === MODE.LIST">
      <div class="table-scroller mt-3">
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
            <th>Qty On Loan</th>
            <th>Veh Area</th>
            <th>Torq</th>
            <th>Qty Return</th>
            <th>Item State</th>
            <th style="width: 125px;">WO/Svc#</th>
          </thead>
          <tbody>
            <template v-if="noData">
              <tr>
                <td colspan="11">There is no data.</td>
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
                <td style="white-space: nowrap;"><div>{{ item.spare.material_no }}</div></td>
                <td style="white-space: nowrap;"><div>{{ item.spare.part_no }}</div></td>
                <td style="white-space: nowrap;"><div>{{ item.spare.name }}</div></td>
                <td style="white-space: nowrap;">
                  <div v-if="item.bin && item.bin.cluster && item.bin && item.bin.shelf && item.bin && item.bin.bin">
                    {{ item.bin.cluster.name }} - {{ item.bin.shelf.name }} - {{ item.bin.row }} - {{ item.bin.bin }}
                  </div>
                  <div v-else>N/A</div>
                </td>
                <td style="white-space: nowrap;"><div>{{ item.quantity_loan || 0 }}</div></td>
                <td style="white-space: nowrap;">
                  <span v-if="item.torque_wrench_area && item.torque_wrench_area.area">{{ item.torque_wrench_area.area }}</span>
                  <span v-else>N/A</span>
                </td>
                <td style="white-space: nowrap;">
                  <span v-if="item.torque_wrench_area && item.torque_wrench_area.torque_value">{{ item.torque_wrench_area.torque_value }}</span>
                  <span v-else>N/A</span>
                </td>
                <td style="white-space: nowrap;">
                  <div class="form-input">
                    <span class="circle" @click.prevent.stop="onClickDecrease(item)">-</span>
                    <input
                      name="Quantitys"
                      style="width: 50px; text-align: center;"
                      type="text"
                      v-model="item.newQuantity"
                      v-validate="`required|numeric|min_value:0|max_value:${item.quantity_loan || 0}`"
                      :data-vv-scope="`${item.scope}`"
                    >
                    <span class="circle" @click.prevent.stop="onClickIncrease(item)">+</span>
                  </div>
                  <!-- <span class="invalid-feedback" v-if="errors.has('Quantitys')">
                    {{ errors.first("Quantitys") }}
                  </span> -->
                  <span class="invalid-feedback" v-if="errors.has(`${item.scope}.Quantitys`)">
                    {{ errors.first(`${item.scope}.Quantitys`) }}
                  </span>
                </td>

                <td style="min-width: 300px;">
                  <div class="state">
                    <div class="btn btn-primary"
                        v-for="state in SPARE_STATE"
                        :class="{selected: item.state === state}"
                        @click.stop="item.state = state" >
                      {{ state | upperFirst }}
                    </div>
                  </div>
                </td>
                <td class="link-mo">
                  <span v-if="item.spare.type == Const.ITEM_TYPE.TORQUE_WRENCH.value" class="btn btn-primary" @click.stop="onClickLinkMO(item)">WO/Svc#</span>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </template>

    <div class="action" v-if="!noData">
      <button class="btn btn-primary"
        :disabled="!selectedSpares.length"
        @click.stop="onClickCheckout">Checkout</button>
    </div>

    <checkout-return-modal />
    <link-service-return-modal />
  </div>
</template>
<style lang="scss" scoped>
.spares {
  .view {
    right: 0;
    .option {
      cursor: pointer;
      display: inline-block;
      padding: 10px;
      border: 1px solid #c7cbce;
      border-radius: 5px;
      margin: 0 5px;
      span {
        margin-left: 10px;
      }
      &:hover {
        border-color: #3490dc;
      }
      &.active {
        background-color: #3490dc;
        color: #fff;
      }
    }
  }
  .grid {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    max-height: 500px;
    overflow-x: auto;
    margin-top: 20px;
    .spare-item {
      width: 300px;
      border: 2px solid #c7cbce;
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
      }
      &.active {
        color: #fff;
        border-color: #3490dc;
        background-color: #3490dc;
      }
    }
  }
//   td {
//   white-space: nowrap !important;
// }
  .action {
    margin-top: 20px;
    text-align: right;
  }
  .no-data {
    text-align: center;
    padding: 10px;
    border: 1px solid #363A47;
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
    .number, .btn-mo {
      border: 1px solid #c7cbce;
      height: 35px;
      width: 50px;
      line-height: 35px;
      margin: 10px;
      text-align: center;
    }
    .btn-mo {
      background-color: #212430;
      width: 100%;
    }
  }
  .state {
    .btn-primary {
      background: none;
      border: 1px solid #a29999;
      color: #FFF;
      margin-left: 5px;
      margin-bottom: 5px;
      outline: none;
      padding: 13px 15px;
      min-width: 85px;
      &::focus {
        outline: none;
      }
      &.selected {
        background-color: #3490dc;
        color: #fff;
      }
    }
  }
  td.link-mo {
    .btn-primary {
      background: none;
      border: 1px solid #a29999;
      color: #FFF;
      margin-left: 5px;
      margin-bottom: 5px;
      outline: none;
      padding: 13px 15px;
      min-width: 85px;
    }
  }
}
</style>
<script>
import rf from 'requestfactory'
import { chain, includes, each, isEmpty, head, sumBy } from 'lodash'
import CheckoutReturnModal from './CheckoutReturnModal'
import LinkServiceReturnModal from './LinkServiceReturnModal'
import SelectBoxesMixin from 'common/SelectBoxesMixin'
import Const from 'common/Const';
import Utils from "common/Utils";
import RemoveErrorsMixin from "common/RemoveErrorsMixin";

const MODE = {
  LIST: 'list',
  GRID: 'grid'
}

const SPARE_STATE = {
  INCOMPLETE: 'incomplete',
  WORKING: 'working',
  Damage: 'damage',
  FINISH: 'finished',
  EXPIRED: 'expired',
  INCOMPLETE: 'incomplete',
}

export default {
  components: {
    CheckoutReturnModal,
    LinkServiceReturnModal,
  },

  mixins: [SelectBoxesMixin, RemoveErrorsMixin],

  data () {
    return {
      MODE,
      selectedMode: MODE.LIST,
      SPARE_STATE,
      Const
    }
  },

  computed: {
    selectedSpares () {
      return chain(this.data)
        .filter(item => (item.is_checked || includes(this.selectedItems, item.id)) && !!item.newQuantity)
        .value()
    },

    noData () {
      return isEmpty(this.data)
    }
  },

  // created () {
  //   this.getSparesReturn()
  // },

  methods: {
    setUserId(userId) {
      this.userId = userId
    },

    getSparesReturn (params = null) {
      rf.getRequest('SpareRequest').getSparesReturn(params)
        .then(res => {
          this.data = chain(res.data || [])
            .map((item, index) => {
              const quantity_loan = item.quantity - (item.returned_quantity || 0)

              return {
                ...item,
                visible: true,
                url: item.url || '/images/icons/no-image.png',
                newQuantity: quantity_loan,
                // quantity_loan: item.quantity - (item.returned_quantity || 0),
                quantity_loan: quantity_loan,
                state: SPARE_STATE.WORKING,
                scope: `row-${index + 1}`,
              }
            })
            .value()
        })
    },

    async onClickCheckout() {
      this.resetError()
      await Utils.asyncForEach(this.data, async (item, index) => {
        const scope = `${item.scope}.*`
        await this.$validator.validate(scope)
      })

      if (this.errors.any()) {
        return this.showError("Invalid field!");
      }
      
      if (isEmpty(this.selectedSpares)) {
        return
      }
      this.$modal.show('checkout-return', { spares: this.selectedSpares })
    },

    onClickLinkMO (item) {
      if (!item.is_checked) {
        return
      }
      if(!item.newQuantity) {
        return
      }

      this.$modal.show('link-service-return', { spare: item, userId: this.userId })
    },

    onClickIncrease(item) {
      this.resetError()
      const number = item.newQuantity + 1
      this.$set(item, 'newQuantity', number <= item.quantity_loan ? number : item.quantity_loan)
    },

    onClickDecrease(item) {
      this.resetError()
      const number = item.newQuantity - 1
      this.$set(item, 'newQuantity', number < 1 ? 0 : number)
    },

    filter (inputText) {
      each(this.data, item => {
        // no search
        if (isEmpty(inputText)) {
          this.$set(item, 'visible', true)
          return
        }

        const visible = includes(item.material_no, inputText) || includes(item.part_no, inputText)
        this.$set(item, 'visible', visible)
      })
    },

    // async validateData() {
    //   await Utils.asyncForEach(this.data, async (item, index) => {
    //     this.errors.clear(item.scope);
    //     await this.$validator.validate(`${item.scope}.*`);
    //   });

    //   if (this.errors.any()) {
    //     return false;
    //   }

    //   return true;
    // },
  }
}
</script>
