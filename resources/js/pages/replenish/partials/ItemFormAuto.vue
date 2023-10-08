<template>
  <div class="item-list">
    <div>
      <div class="head">
        <div class="d-inline-block pointer mr-2 ic-back" @click.stop="onClickBack">
          <img src="/images/icons/icon-back2.svg" class="mr-2" width="25">
        </div>

        <div class="text">
          <span v-if="replenishment">Replenish #{{ replenishment.uuid }}</span>
          <span v-else>Create New Replenish</span>
        </div>
      </div>

      <div class="form-search">
        <label>Item P/N:</label>
        <div>
          <input
            type="text"
            class="input"
            placeholder="Item P/N"
            v-model="inputText"
            @keypress.enter="onClickSearch"
            ref="inputText" >
        </div>
        <button class="btn btn-primary" @click.stop="onClickSearch">Search</button>
      </div>

      <div class="mb-2 mt-2">
        <div class="d-inline-block">
          <button class="btn btn-primary mr-2" :disabled="noSelectedSpares" @click.stop="onClickSubmit">Submit</button>
          <button class="btn btn-primary mr-1" :disabled="noData" @click.stop="onClickFill2Max">Fill to Max</button>
        </div>
      </div>

      <div class="clearfix"></div>

      <div class="table-scroller mt-3 mb-3">
      <table>
        <thead>
          <th class="no" v-if="!editable">
            <label class="checkbox-container">No.
              <input type="checkbox" v-model="checkedAll" @click="onClickCheckedAll">
              <span class="checkmark"></span>
            </label>
          </th>
          <th>MPN</th>
          <th>SSN</th>
          <th>Description</th>
          <th>Bin</th>
          <th>Qty OH</th>
          <th>Qty RL</th>
          <th v-if="editable">Delete</th>
        </thead>
        <tbody>
          <template v-if="noData">
            <tr>
              <td :colspan="editable ? 7 : 7">There is no data.</td>
            </tr>
          </template>
          <template v-else>
            <tr v-for="(item, index) in data" v-if="item.visible">
              <td class="no" @click.prevent="onSelectBox(item)" v-if="!editable">
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
              <td>
                <div class="form-input">
                  <span class="circle" @click.stop="onClickDecrease(item)">-</span>
                  <span class="number">{{ item.inputForm.quantity }}</span>
                  <span class="circle" @click.stop="onClickIncrease(item)">+</span>
                </div>
              </td>
              <td v-if="editable">
                <div><img src="/images/icons/icon-trash.svg" width="25" @click.stop="onClickDelete(item, index)"></div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    </div>

  </div>
</template>
<style lang="scss" scoped>
.item-list {
  .head {
    position: relative;
    margin-top: 10px;
    margin-bottom: 30px;
    .ic-back {
      position: absolute;
      left: 0;
    }
    .text {
      text-align: center;
      font-size: 16px;
      font-weight: bold;
    }
  }
  .form-search {
    display: flex;
    justify-content: center;
    align-items: baseline;
    margin-bottom: 40px;
    .input {
      width: 350px;
      margin: 0 10px;
    }
    button {
      padding: 15px 40px;
      margin-top: 20px;
    }
  }
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
      border: 1px solid #c7cbce;
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
import rf from 'requestfactory'
import Const from 'common/Const'
import SelectBoxesMixin from 'common/SelectBoxesMixin'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
import { isEmpty, chain, includes, concat, each, size, debounce } from 'lodash'


export default {

  props: {
    replenishment: {
      type: Object,
      default: null
    }
  },

  mixins: [SelectBoxesMixin, RemoveErrorsMixin],

  data () {
    return {
      inputText: null,
      torqueAreas: [],
    }
  },

  computed: {
    noData () {
      return isEmpty(this.data)
    },

    selectedSpares () {
      return chain(this.data)
          .filter(item => includes(this.selectedItems, item.id))
          .value()
      // return chain(this.data)
      //   .filter(item => includes(this.selectedItems, item.id) && !!item.quantity)
      //   .value()
    },

    noSelectedSpares () {
      // const isValid = !isEmpty(this.selectedSpares) && size(this.selectedSpares) === size(this.selectedItems)
      const isValid = size(this.selectedItems)
      return !isValid
    },

    editable () {
      return !isEmpty(this.replenishment)
    }
  },

  watch: {
    inputText: debounce(function () {
      this.onClickSearch()
    }, 400)
  },

  mounted () {
    this.initData()
  },

  methods: {
    async initData () {
      // for creating
      if (!this.replenishment) {
        return this.getSparesAssignedBin({
          can_replenishment: true
        })
      }

      const mapSpares = chain(this.replenishment.replenish_spares)
        .keyBy('spare_id')
        .value()

      const spareIds = chain(mapSpares)
        .keys()
        .value()

      const mapBins = chain(this.replenishment.replenish_spares)
          .keyBy('bin_id')
          .value()

      const binIds = chain(mapBins)
          .keys()
          .value()

      if(binIds.length) {
        await this.getSparesAssignedBin({ binIds })
      }

      this.data = chain(this.data)
        .map(item => {
          return mapSpares[item.spare_id] ? { ...item, ...mapSpares[item.spare_id] } : item
        })
        .value()
    },

    getSparesAssignedBin (data = {}) {
      const params = {
        no_pagination: true,
        ...data
      }
      return rf.getRequest('AdminRequest').getSparesAssignedBin(params)
        .then(res => {
          this.data = chain(res.data || [])
            // .filter(item => !!item.quantity)
            .map(item => {
              return {
                ...item,
                visible: true,
                inputForm: {
                  bin_id: item.id,
                  spare_id: item.spare_id,
                  quantity: 0
                }
              }
            })
            .value()
        })
    },

    async onClickSubmit () {
      if (this.noSelectedSpares) {
        return
      }

      let spares = this.editable ? this.data : this.selectedSpares

      if (isEmpty(spares)) {
        return
      }

      spares = chain(spares)
        .map(item => item.inputForm)
        .value()

      const callback = async () => {
        try {
          await rf.getRequest('SpareRequest').replenishAuto({ spares })

          this.showSuccess('Successfully!')
          this.$emit('done')
        } catch (error) {
          console.error(error)
          this.processErrors(error)
        }
      }

      this.confirmAction({ callback: callback })
    },

    onClickFill2Max () {
      each(this.data, item => {
        let maxQty = item.max - item.quantity_oh
        maxQty = maxQty < 0 ? 0 : maxQty

        // If edit screen
        if(this.editable) {
          this.$set(item.inputForm, 'quantity', maxQty)
        }
        // If add screen
        else {
          if(item.is_checked) {
            this.$set(item.inputForm, 'quantity', maxQty)
          }
        }
      })
    },

    onClickBack () {
      this.$emit('back', true)
    },

    onClickIncrease (item) {
      let maxQty = item.max - item.quantity_oh
      maxQty = maxQty < 0 ? 0 : maxQty

      const number = item.inputForm.quantity + 1
      this.$set(item.inputForm, 'quantity', number <= maxQty ? number : maxQty)
    },

    onClickDecrease (item) {
      let number = item.inputForm.quantity - 1
      number = number < 0 ? 0 : number
      this.$set(item.inputForm, 'quantity', number < item.min ? item.min : number)
    },

    onClickSearch () {
      chain(this.data)
        .each(item => {
          // no search
          if (!this.inputText || isEmpty(this.inputText)) {
            this.$set(item, 'visible', true)
            return
          }

          const visible = includes(item.material_no, this.inputText) || includes(item.part_no, this.inputText)
          this.$set(item, 'visible', visible)
        })
        .value()
    },

    onClickDelete(item, index) {
      const callback = () => {
        rf.getRequest('SpareRequest').removeSpareByBinReplenishAuto({
          replenishment_id: this.replenishment.id,
          bin_id: item.bin_id
        })
          .then(res => {
            this.showSuccess('Successfully!');

            this.data = chain(this.data)
              .filter(spare => {
                return spare.bin_id != item.bin_id
              })
              .value()
          })
      }

      this.confirmAction({ callback })
    }
  }
}
</script>
