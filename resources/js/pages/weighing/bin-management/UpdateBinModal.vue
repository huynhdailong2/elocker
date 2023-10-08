<template>
  <modal
    :name="name"
    :width="'70%'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    class="custom-modal">
    <div class="header">
      <div class="title">Bin Detail</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content" v-if="bin">
      <div class="row">
        <div class="col-sm-3">
          <label>Item Name</label>
          <input
            type="text"
            class="input"
            v-model="bin.deviceDescription.name">
        </div>
        <div class="col-sm-3">
          <label>Device ID</label>
          <input
            type="text"
            class="input"
            v-model="bin.deviceId">
        </div>
        <div class="col-sm-3">
          <label>Part Number</label>
          <input
            type="text"
            class="input"
            v-model="bin.deviceDescription.partNumber">
        </div>
        <div class="col-sm-3">
          <label>Material Number</label>
          <input
            type="text"
            class="input"
            v-model="bin.deviceDescription.materialNo">
        </div>
      </div>

      <hr />

      <div class="row">
        <div class="col-sm-12">
          <label><strong>Threshold level</strong></label>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-3">
          <label>Low</label>
          <input
            type="text"
            class="input"
            v-model="bin.quantityMinThreshold">
        </div>
        <div class="col-sm-3">
          <label>Crit</label>
          <input
              type="text"
              class="input"
              v-model="bin.quantityCritThreshold">
        </div>
      </div>

      <hr />

      <div class="row">
        <div class="col-sm-4">
          <label>Calibration</label>
        </div>
        <div class="col-sm-4">
          <label>Raw Value</label>
        </div>
        <div class="col-sm-4">
          <label>Quantity</label>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-4 m-auto">
          <label>Current</label>
        </div>
        <div class="col-sm-4">
          <input
              type="text"
              class="input"
              v-model="bin.weight">
        </div>
        <div class="col-sm-4">
          <input
              type="text"
              class="input"
              v-model="bin.quantity">
        </div>
      </div>

      <div class="row">
        <div class="col-sm-4 m-auto">
          <label>Tare Weight</label>
        </div>
        <div class="col-sm-4">
          <span class="input-icon">
            <input
                type="text"
                class="input"
                disabled
                v-model="bin.zeroWeight">
            <img src="/images/icons/icon-copy.svg" @click.stop="copyCurrentValue('zeroWeight')">
          </span>
        </div>
        <div class="col-sm-4">
          <input
              type="text"
              class="input"
              disabled
              v-model="bin.unitWeight">
        </div>
      </div>

      <div class="row">
        <div class="col-sm-4 m-auto">
          <label>Cal. Weight</label>
        </div>
        <div class="col-sm-4">
          <span class="input-icon">
            <input
                type="text"
                class="input"
                disabled
                v-model="bin.calcWeight">
            <img src="/images/icons/icon-copy.svg" @click.stop="copyCurrentValue('calcWeight')">
          </span>
        </div>
        <div class="col-sm-4">
          <input
              type="text"
              class="input"
              v-model="bin.calcQuantity">
        </div>
      </div>

      <div class="row">
        <div class="col-sm-12 text-center">
          <button class="btn btn-second" @click.stop="close">Discard Changes</button>
          <button class="btn btn-primary" @click.stop="onClickSubmit">Save Bin Settings</button>
        </div>
      </div>
    </div>

  </modal>
</template>
<style lang="scss" scoped>
.content {
  width: 100%;
  .row {
    margin-bottom: 20px;
    label {
      color: #fff;
    }
  }
  hr {
    border-color: #7f7f7f;
  }
  .input-icon {
    position: relative;
    display: block;

    img {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      right: 10px;
      width: 20px;
      cursor: pointer;
    }
  }
  .m-auto {
    margin: auto;
  }
}
</style>

<script>
import rf from 'requestfactory';
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'

export default {
  props: {
    name: {
      type: String,
      default: 'update-bin-modal'
    }
  },

  mixins: [RemoveErrorsMixin],

  data () {
    return {
      bin: null,
    }
  },

  computed: {

  },

  methods: {
    beforeOpen (event) {
      const { params } = event
      this.bin = params?.bin
      if(! this.bin.deviceDescription) {
        this.bin.deviceDescription = {
          name: '',
          partNumber: '',
          materialNo: '',
        }
      }
    },

    close () {
      this.spare = null;
      this.$modal.hide(this.name)
    },

    async onClickSubmit () {
      const dataUpdate = {
        id: parseInt(this.bin.id),
        quantity: parseInt(this.bin.quantity),
        quantityCritThreshold: this.bin.quantityCritThreshold ?? '',
        quantityMinThreshold: this.bin.quantityMinThreshold ?? '',
        weight: parseFloat(this.bin.weight),
        zeroWeight: this.bin.zeroWeight ?? '',
        deviceId: parseInt(this.bin.deviceId),
        calcQuantity: this.bin.calcQuantity ?? '',
        calcWeight: this.bin.calcWeight ?? '',
        deviceDescription: {
          batchNoBag: this.bin.deviceDescription.bagNoBatch ?? '',
          batchNoBag2: this.bin.deviceDescription.bagNoBatch2 ?? '',
          batchNoBag3: this.bin.deviceDescription.bagNoBatch3 ?? '',
          criCode: this.bin.deviceDescription.criCode ?? '',
          expiryBag: this.bin.deviceDescription.expiryBag ?? '',
          expiryBag2: this.bin.deviceDescription.expiryBag2 ?? '',
          expiryBag3: this.bin.deviceDescription.expiryBag3 ?? '',
          field1: this.bin.deviceDescription.field1 ?? '',
          itemAcct: this.bin.deviceDescription.itemAcct ?? '',
          jom: this.bin.deviceDescription.jom ?? '',
          materialNo: this.bin.deviceDescription.materialNo ?? '',
          matlGrp: this.bin.deviceDescription.matlGrp ?? '',
          name: this.bin.deviceDescription.name ?? '',
          partNumber: this.bin.deviceDescription.partNumber ?? '',
          quantityBag: this.bin.deviceDescription.quantityBag ?? '',
          quantityBag2: this.bin.deviceDescription.quantityBag2 ?? '',
          quantityBag3: this.bin.deviceDescription.quantityBag3 ?? '',
          supplierEmail: this.bin.deviceDescription.supplierEmail ?? ''
        }
      }

      rf.getRequest('WeightRequest').updateBinInformation(dataUpdate)
          .then(res => {
            this.showSuccess('Successful!')
            this.close()
          })
          .catch(error => {
            this.processAndToastFirstError(error)
          })

      this.$emit('done', this.bin)
      this.close()
    },

    copyCurrentValue(column) {
      this.bin[column] = this.bin.weight
    }
  }
}
</script>
