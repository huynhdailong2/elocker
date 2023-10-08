<template>
  <div class="item-list">
    <div>

      <div class="action">
        <div class="select">
          <select
              class="input"
              v-model="selectedEuc">
            <option :value="item" v-for="(item, idx) in eucList" :key="idx">EUC Box #{{ item.order }}</option>
          </select>
        </div>
      </div>

      <euc-items :data="dataExists" />

      <div class="text-right">
        <button class="btn btn-primary" @click.stop="onSubmit">Submit</button>
      </div>
    </div>

    <scan-requester-euc-item-modal :name="scanRequesterEucModal" />
  </div>
</template>
<style lang="scss" scoped>
  .item-list {
    .table-scroller {
      min-height: 200px;
      tr {
        td {
          .input {
            min-width: 100px;
          }
          .radio {
            label {
              margin-top: 10px;
              input {
                cursor: pointer;
                width: 50px;
                height: 1.4em
              }
            }
            ::v-deep .vdp-datepicker {
              input {
                min-width: 100px;
              }
            };
          }
        }
        &:last-child {
          // ::v-deep .vdp-datepicker {
          //   &__calendar {
          //     top: -290px;
          //   }
          // }
          ::v-deep .time-picker {
            .dropdown {
              &:last-child {
                top: -145px;
              }
            }
          }
        }
        &:first-child {
          ::v-deep .time-picker {
            .dropdown {
              &:last-child {
                top: calc(2.2em + 2px);
              }
            }
          }
        }
      }
    }
    .action {
      .select {
        display: inline-block;
        width: 200px;
        margin: 20px 20px 20px 0;
      }
      .btn {
        padding: 13px;
      }
    }
  }
</style>
<script>
import rf from 'requestfactory'
import Const from 'common/Const'
import Utils from 'common/Utils'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
import {isEmpty, chain, includes, head, debounce, map} from 'lodash'
import moment from 'moment'
import Datepicker from 'vuejs-datepicker'
import VueTimepicker from 'vue2-timepicker'
import 'vue2-timepicker/dist/VueTimepicker.css'
import ScanRequesterEucItemModal from './ScanRequesterEucItemModal'
import EucItems from "../../physical/partials/EucItems";

export default {

  mixins: [RemoveErrorsMixin],

  components: {
    Datepicker,
    VueTimepicker,
    ScanRequesterEucItemModal,
    EucItems
  },

  data () {
    return {
      data: [],
      dataExists: [],
      eucList: [],
      selectedEuc: {},
      scanRequesterEucModal: 'scan-requester-euc-modal',
      Const,
    }
  },

  watch: {
    data: {
      deep: true,
      immediate: true,
      handler (newValue) {
        const hasEmptyRow = chain(newValue || [])
          .filter(item => this.isEmptyRow(item))
          .size()
          .value()

        if (!hasEmptyRow) {
          this.addNewItem()
        }
      }
    },
    selectedEuc: function (value) {
      this.dataExists = value.spares;
    }
  },

  mounted () {
    this.getEucLists()
  },

  methods: {
    addNewItem () {
      const newItem = {
        editable: true,
        unix: Date.now(),
        mpn: null,
        ssn: null,
        description: null,
        is_confirmed: 0
      }

      this.data.push(newItem)
    },

    isEmptyRow(row) {
      return !row.spare_id && !row.mpn && !row.ssn && !row.description
    },

    getEucLists () {
      const params = {
        no_pagination: true
      }
      rf.getRequest('AdminRequest').getEucLists(params)
        .then(res => {
          this.eucList = res.data || []
          this.selectedEuc = head(this.eucList)
        })
    },

    getSpares () {
      const params = {
        no_pagination: true,
        search_key: isEmpty(this.inputText) ? null : this.inputText
      }
      rf.getRequest('AdminRequest').getSpares(params)
        .then(res => {
          const types = [
            Const.ITEM_TYPE.CONSUMABLE.value,
            Const.ITEM_TYPE.DURABLE.value,
            Const.ITEM_TYPE.PERISHABLE.value,
            Const.ITEM_TYPE.AFES.value,
            Const.ITEM_TYPE.TORQUE_WRENCH.value
          ]

          this.data = chain(res.data || [])
            .filter(item => includes(types, item.type))
            .value()
        })
    },

    onClickAddNew () {
      this.addNewItem()
    },

    onChangeMpn (e) {
      const index = chain(e.target.attributes)
        .filter(item => item.nodeName === 'index')
        .map(item => item.nodeValue)
        .head()
        .value()

      const record = this.data[parseInt(index)]

      const self = this
      const fetchData = debounce (function () {
        rf.getRequest('AdminRequest').getSpareByMpn({ material_no: record.mpn })
          .then(res => {
            const data = res.data
            if (isEmpty(data)) return
            self.$set(record, 'spare_id', data.id)
            self.$set(record, 'ssn', data.part_no)
            self.$set(record, 'description', data.name)
          })
          .finally(() => {
            self.$set(record, 'fetching_mpn', false)
          })
      }, 500)

      if (!record.fetching_mpn) {
        self.$set(record, 'fetching_mpn', true)
        fetchData()
      }
    },

    validateManual () {
      if (isEmpty(this.dataExists)) {
        this.errors.add({field: 'spare_ids', msg: 'The spare field is required'})
      }
    },

    async onSubmit () {
      this.resetError()

      this.validateManual()
      await this.$validator.validateAll()

      if (this.errors.any()) {
        return
      }

      const toUTc = (date) => {
        return date ? new moment(date).utc().format(Const.DATE_PATTERN) : null
      }

      let dataUpdate = map(this.dataExists, item => {
        return {
          spare_id: item.spare_id,
          quantity_oh: item.quantity_oh,
          quantity_replenish: item.quantity_replenish,
          batch_no: item.batch_no,
          calibration_due: toUTc(item.calibration_due),
          charge_time: Utils.objTime2String(item.charge_time),
          expiry_date: toUTc(item.expiry_date),
          hydrostatic_test_due: toUTc(item.hydrostatic_test_due),
          serial_no: item.serial_no
        }
      })

      const _callback = () => {
        this.$modal.show(this.scanRequesterEucModal, {
          data: dataUpdate,
          callback: this.submitRequest
        })
      }

      this.confirmAction({ callback: _callback })
    },

    submitRequest(dataUpdate) {
      rf.getRequest('AdminRequest').updateItemsEuc(this.selectedEuc.id, {'spares': dataUpdate})
        .then(res => {
          this.showSuccess('Successfully!');
          this.$emit('done')

          const params = {
            no_pagination: true
          }
          rf.getRequest('AdminRequest').getEucLists(params)
            .then(res => {
              this.eucList = res.data || []

              for (let i = 0; i < this.eucList.length; i++) {
                if(this.eucList[i].id == this.selectedEuc.id) {
                  this.selectedEuc = this.eucList[i];
                  break;
                }
              }
            })
        })
        .catch(error => {
          if(error.response.status === 422) {
            this.showError('EUC item must have serial number for tracking!')
          }
          this.processErrors(error)
        })
    }
  }
}
</script>
