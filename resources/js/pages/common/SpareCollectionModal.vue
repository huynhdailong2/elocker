<template>
  <modal
    :name="name"
    :width="'70%'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    @opened="opened"
    class="custom-modal">
    <div class="header">
      <div class="title">Items List</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content">
      <div class="form-search">
        <label>Item P/N:</label>
        <div>
          <input
            type="text"
            class="input"
            name="inputText"
            placeholder="P/N"
            v-model.trim="inputText"
            ref="inputText" >
        </div>
        <button class="btn btn-primary" @click.stop="onClickSearch">Search</button>
      </div>

      <div class="mb-2" v-if="!noData">*Please select an item.</div>
      <div class="table-scroller">
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
            <th>Qty OH</th>
            <th>RL Qty</th>
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
                <td><div>{{ item.quantity_oh || 0 }}</div></td>
                <input type="number"
                   class="input"
                   placeholder="Replenish OH"
                   name="quantity_replenish"
                   v-model="item.quantity_replenish"
                   data-vv-as="quantity_replenish"
                   :data-vv-scope="`${item.scope}`"
                   data-vv-validate-on="none"
                   v-validate="'required|numeric|min_value:0'">
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
    <div class="footer">
      <button class="btn btn-primary"@click.stop="onClickSubmit">Submit</button>
    </div>
  </modal>
</template>
<style lang="scss" scoped>
.content {
  // width: 90%;
  .form-search {
    display: flex;
    justify-content: center;
    align-items: baseline;
    margin-bottom: 40px;
    .input {
      width: 300px;
      margin: 0 10px;
    }
    button {
      padding: 15px 40px;
      margin-top: 20px;
    }
  }
}
.footer {
  .btn-primary {
    padding-left: 30px;
    padding-right: 30px;
  }
}
</style>
<script>
import rf from 'requestfactory'
import { chain, includes, isEmpty, each, debounce } from 'lodash'
import SelectBoxesMixin from 'common/SelectBoxesMixin'
import Const from 'common/Const'

export default {
  props: {
    name: {
      type: String,
      default: 'SpareCollectionModal'
    },

    state: {
      type: String,
      default: Const.ITEM_STATE.ASSIGNED_BIN
    },

    types: {
      type: Array,
      default: () => []
    }
  },

  mixins: [SelectBoxesMixin],

  data () {
    return {
      inputText: null,
      originalData: [],
      selected: []
    }
  },

  computed: {
    noData () {
      return isEmpty(this.data)
    }
  },

  watch: {
    inputText: debounce(function (newVal) {
      this.onClickSearch()
    }, 300)
  },

  created () {
    this.getSpares ()
  },

  methods: {
    beforeOpen (event) {
      const { params } = event
      this.selected = params?.selected || []
    },

    opened () {
      this.data = chain(this.originalData)
        .filter(item => !includes(this.selected, item.id))
        .map(item => {
          this.$set(item, 'visible', true)
          item.quantity_replenish = 0;
          return item
        })
        .value()
    },

    close () {
      this.$modal.hide(this.name)
      this.resetCheckedbox()
      this.selected = []
    },

    getSpares () {
      const params = {
        no_pagination: true
      }

      const getData = (params) => {
        switch (this.state) {
          case Const.ITEM_STATE.ALL_SPARES:
            return rf.getRequest('AdminRequest').getSpares(params)
          case Const.ITEM_STATE.UNASSIGNED_BIN:
            return rf.getRequest('AdminRequest').getSparesUnassigned(params)
          case Const.ITEM_STATE.ASSIGNED_BIN:
            return rf.getRequest('AdminRequest').getSparesAssignedBin(params)
        }
      }

      getData(params).then(res => {
        this.originalData = chain(res.data || [])
          .filter(item => {
            if (isEmpty(this.types)) {
              return true
            }
            return includes(this.types, item.type)
          })
          .map(item => {
            return { ...item }
          })
          .value()
      })
    },

    onClickSearch () {
      each(this.data, item => {
        // no search
        if (!this.inputText || isEmpty(this.inputText)) {
          this.$set(item, 'visible', true)
          return
        }

        const visible = includes(item.material_no, this.inputText) || includes(item.part_no, this.inputText)
        this.$set(item, 'visible', visible)
      })
    },

    onClickSubmit () {
      const selected = chain(this.data)
        .filter(item => includes(this.selectedItems, item.id))
        .value()

      this.$emit('done', selected)
      this.close()
    }
  }
}
</script>
