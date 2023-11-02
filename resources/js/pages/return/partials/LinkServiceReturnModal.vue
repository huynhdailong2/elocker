<template>
  <modal
      :name="name"
      :width="'700'"
      height="auto"
      :clickToClose="false"
      @before-open="beforeOpen"
      class="custom-modal">
    <div class="header">
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content">
      <template v-if="step === STEP.CHOOSE_ACTION">
        <p class="text-center">WO/Svc#</p>
        <div class="choose-action">
          <div class="table-content">
            <data-table2 :getData="getSparesTorqueWrench"
                         :limit="10"
                         :column="5"
                         :widthTable="'100%'"
                         @DataTable:finish="onDataTableFinished"
                         ref="datatable">
              <th>No.</th>
              <th>WO/Svc#</th>
              <th>Veh Area</th>
              <th>Torq</th>
              <th>Action</th>
              <template slot="body" slot-scope="props">
                <tr>
                  <td>
                    <div class="text">{{ props.realIndex }}</div>
                  </td>
                  <td>
                    <!--                    <template v-if="props.item.editable">-->
                    <!--                      <input-->
                    <!--                          type="text"-->
                    <!--                          class="input_g"-->
                    <!--                          :class="{'error': errors.has(`row-${props.index + 1}.card_num`)}"-->
                    <!--                          name="area"-->
                    <!--                          data-vv-as="service/mo"-->
                    <!--                          :data-vv-scope="`row-${props.index + 1}`"-->
                    <!--                          placeholder="Area"-->
                    <!--                          v-model.trim="props.item.formInput.card_num"-->
                    <!--                          v-validate="'required'" >-->
                    <!--                      <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.card_num`)">-->
                    <!--                      {{ errors.first(`row-${props.index + 1}.card_num`) }}-->
                    <!--                      </span>-->
                    <!--                    </template>-->
                    <!--                    <template v-else>-->
                    <!--                      <div class="text">{{ props.item.card_num }}</div>-->
                    <!--                    </template>-->
                    <template>
                      <div class="text">{{ props.item.job_card.vehicle.vehicle_num }}</div>
                    </template>
                  </td>
                  <td>
                    <template v-if="props.item.editable">
                      <select
                          :data-vv-scope="`row-${props.index + 1}`"
                          :class="{'error': errors.has(`row-${props.index + 1}.torque_area`)}"
                          v-model="props.item.formInput.torque_id"
                          name="torque_id"
                          data-vv-as="torque"
                          v-validate="'required'">
                        <option :value="item.id" v-for="(item, index) in area_used" :key="index">{{
                            item.area
                          }}
                        </option>
                      </select>
                      <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.torque_area`)">
                      {{ errors.first(`row-${props.index + 1}.torque_area`) }}
                    </span>
                    </template>
                    <template v-else>
                      <div class="text">{{ props.item.torque_wrench_area.area }}</div>
                    </template>
                  </td>
                  <td>
                    <template v-if="props.item.editable">
                      <div class="text">{{ getTorqValue(props.item.formInput.torque_id) }}</div>
                    </template>
                    <template v-else>
                      <div class="text">{{ getTorqValue(props.item.torque_wrench_area.id) }}</div>
                    </template>
                  </td>
                  <td class="action">
                    <template v-if="props.item.editable">
                      <img src="/images/icons/icon-cancel.svg" width="22px"
                           @click.stop="onClickCancel(props.item, props.index)">
                      <img src="/images/icons/icon-save.svg" width="22px"
                           @click.stop="onClickSave(props.item, props.index)">
                    </template>
                    <template v-else>
                      <img src="/images/icons/icon-edit.svg" width="22px" @click.stop="props.item.editable = true">
                      <img src="/images/icons/icon-trash.svg" width="22px"
                           @click.stop="onClickDelete(props.item, props.index)">
                    </template>
                  </td>
                </tr>
              </template>
            </data-table2>

            <div class="container-link-mo">
              <p>Add WO/Svc#</p>
              <p>
                <input
                    type="text"
                    class="input"
                    :class="{'error': errors.has('job_card')}"
                    name="job_card"
                    data-vv-as="service/mo"
                    placeholder="Service/WO"
                    v-model.trim="inputMOSvc"
                    v-validate=""
                    @keypress.enter="onClickSearch"
                    ref="inputText">
              </p>
              <p>
                <button class="btn btn-danger" @click.stop="onClickConfirm" :disabled='isDisabledBtnConfirm'>Confirm</button>
              </p>
            </div>
          </div>
        </div>
      </template>

      <template v-if="step === STEP.CHOOSE_CONFIRM">
        <div class="choose-confirm">
          <div class="choose-confirm-title">Sight Glass Test Gauge, KIS-MS-QC</div>
          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Veh P .Area:</label>
            <div class="col-sm-9">
              <select
                :data-vv-scope="`confirmAreaId`"
                class="input"
                name="confirmAreaId"
                data-vv-as="confirmAreaId"
                v-model="confirmAreaId">
                <option :value="item.id" v-for="(item, index) in area_used" :key="index">{{
                    item.area
                  }}
                </option>
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Torq:</label>
            <div class="col-sm-9">
              <div class="text">{{ getTorqValue(confirmAreaId) }}</div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12 group-btn-confirm">
              <button class="btn btn-second btn-confirm-cancel" @click.stop="onClickCancelStepConfirm">
                Cancel
              </button>
              <button class="btn btn-danger" :disabled="!confirmAreaId" @click.stop="onClickConfirmStepConfirm">
                Confirm
              </button>
            </div>
          </div>
        </div>
      </template>

    </div>

  </modal>
</template>
<style lang="scss" scoped>
.content {
  padding: 0px;
  .btn-danger {
    line-height: 16px;
    padding: 15px;
  }
  .choose-confirm {
    margin-bottom: 30px;
    .choose-confirm-title {
      text-align: center;
      margin-bottom: 20px;
      color: white;
      font-size: 120%;
      font-weight: bold;
    }
    .group-btn-confirm {
      display: flex;
      justify-content: flex-end;
      .btn-confirm-cancel {
        margin-right: 20px;
      }
    }
  }
  .choose-action {
    .container-link-mo {
      display: flex;
      align-items: center;
      margin-top: 20px;
      justify-content: center;
      > p {
        margin-right: 10px;
      }
    }

    margin-bottom: 30px;

    .item {
      min-width: 175px;
      margin: 15px;
      padding: 25px;
      background-color: white;
      text-align: center;
      box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.25);
      cursor: pointer;

      img {
        width: 80px;
        margin-bottom: 20px;
      }
    }
  }

  .scan-personnel {
    text-align: center;
    margin-bottom: 40px;

    .input-search {
      margin: 10px 20px;
    }

    .btn {
      margin-top: 10px;
    }

    .info {
      .label {
        width: 100px;
        display: inline-block;
      }

      input {
        width: 50%;
        margin: 10px 20px;
      }
    }
  }
}
</style>
<script>
import rf from 'requestfactory'
import {chain, cloneDeep, debounce, isEmpty} from 'lodash'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'

const STEP = {
  CHOOSE_ACTION: 'choose-action',
  CHOOSE_CONFIRM: 'choose-confirm',
}

export default {
  components: {},

  props: {
    name: {
      type: String,
      default: 'link-service-return'
    }
  },

  mixins: [RemoveErrorsMixin],

  data() {
    return {
      STEP,
      step: STEP.CHOOSE_ACTION,
      action: null,
      user: null,
      spare: {},
      userId: null,
      printModal: 'ReturnItemsPrintModal',
      area_used: [],

      inputMOSvc: null,
      jobCard: null,
      isSearched: false,
      confirmAreaId: null
    }
  },

  mounted() {
    this.getAreaUsed()
  },

  watch: {
    inputMOSvc: debounce(function () {
      this.onClickSearch()
    }, 400)
  },

  computed: {
    isDisabledBtnConfirm() {
      return isEmpty(this.inputMOSvc) || isEmpty(this.jobCard)
    }
  },

  methods: {
    beforeOpen(event) {
      const {params} = event
      this.spare = params.spare
      this.userId = params.userId

      // this.getSparesTorqueWrench()
    },

    close() {
      this.$modal.hide(this.name)
      this.user = null
      this.step = STEP.CHOOSE_ACTION
      this.action = null
      this.spare = {}
      this.jobCard = null
      this.inputMOSvc = null
    },

    getSparesTorqueWrench(params) {
      params = {
        spare_id: this.spare.spare_id,
        issue_card: this.spare.id,
        bin_id: this.spare.bin_id,
        user_id: this.userId,
        returned_type: 'mo',
        tracking_mo: true
      }
      return rf.getRequest('SpareRequest').getSparesTorqueWrench(params)
    },

    onDataTableFinished() {
      this.data = this.$refs.datatable.rows

      chain(this.data)
          .each(row => {
            this.$set(row, 'editable', false)
            this.$set(row, 'formInput', cloneDeep(row))
          })
          .value()
    },

    getAreaUsed(params) {
      params = {
        no_pagination: true,
      }

      rf.getRequest('AdminRequest').getTorqueWrenchAreas(params)
          .then(res => {
            this.area_used = res.data || []
          })
    },

    getTorqValue(torque_id) {
      if (!torque_id) {
        return null
      }

      const area = chain(this.area_used)
          .find(item => item.id === torque_id)
          .value()

      return area ? area.torque_value : null
    },

    getTorqName(torque_id) {
      if (!torque_id) {
        return null
      }

      const area = chain(this.area_used)
          .find(item => item.id === torque_id)
          .value()

      return area ? area.area : null
    },

    onClickCancel(record, index) {
      this.$set(record, 'editable', false)
      this.$set(record, 'formInput', cloneDeep(record))

      const scope = `row-${index + 1}.*`
      this.errors.clear(scope)
    },

    async onClickSave(record, index) {
      const scope = `row-${index + 1}.*`
      this.errors.clear(scope)

      await this.$validator.validate(scope)

      if (this.errors.any()) {
        return
      }

      rf.getRequest('SpareRequest').updateLinkMO({
        'id': record.formInput.issued_id,
        'torque_wrench_area_id': record.formInput.torque_id
      })
          .then(res => {
            this.$set(record, 'editable', false)
            const data = res.data

            this.$set(record, 'torque_area', this.getTorqName(data.torque_wrench_area_id))
            this.$set(record, 'torque_id', data.torque_wrench_area_id)

            this.showSuccess('Successfully!');
          })
          .catch(error => {
            this.processErrors(error, `row-${index + 1}`)
          })
    },

    onClickDelete(record, index) {
      const refresh = () => {
        this.$refs.datatable.refresh()
      }

      if (!record.id) {
        refresh()
      }

      const callback = () => {
        rf.getRequest('SpareRequest').deleteLinkMO({id: record.issued_id})
            .then(res => {
              this.showSuccess('Successfully!');
              refresh()
            })
            .catch(error => {
              this.processErrors(error)
            })
      }

      this.confirmAction({callback})
    },

    async onClickSearch() {
      this.resetError()

      await this.$validator.validate('job_card')
      if (this.errors.any()) {
        return
      }

      try {
        const params = {card_num: this.inputMOSvc}
        const res = await rf.getRequest('AdminRequest').getJobCardByCardNumber(params)

        this.jobCard = res.data
        this.isSearched = true
      } catch (error) {
        this.processErrors(error)
      }
    },

    onClickConfirm() {
      this.step = STEP.CHOOSE_CONFIRM;
    },

    onClickCancelStepConfirm() {
      this.confirmAreaId = null;
      this.inputMOSvc = null;
      this.step = STEP.CHOOSE_ACTION;
    },

    onClickConfirmStepConfirm() {
      if(!this.confirmAreaId || !this.jobCard) {
        return;
      }

      rf.getRequest('SpareRequest').createLinkMO({
        'job_card_id': this.jobCard.id,
        'bin_id': this.spare.bin_id,
        'issue_card_id': this.spare.id,
        'spare_id': this.spare.spare_id,
        'quantity': this.spare.newQuantity,
        'torque_wrench_area_id': this.confirmAreaId,
        'taker_id': this.userId,
      })
        .then(res => {
          this.showSuccess('Successfully!')
          this.onClickCancelStepConfirm()
        })
        .catch(error => {
          this.processErrors(error)
        })
    }
  }
}
</script>
