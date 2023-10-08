<template>
  <div class="input-form">
    <div class="content-form">
      <div class="row">
        <div class="col-6">
          <label class="name">Material Number</label>
          <input
            type="text"
            class="input"
            name="material_number"
            data-vv-as="material number"
            placeholder="Material Number"
            v-model.trim="formInput.material_number"
            v-validate="'required|max:190'" >
          <span class="invalid-feedback" v-if="errors.has('material_number')">
            {{ errors.first('material_number') }}
          </span>
        </div>
        <div class="col-6">
          <label class="name">Card Number</label>
          <input
            type="text"
            class="input"
            name="card_number"
            data-vv-as="card number"
            placeholder="Card Number"
            v-model.trim="formInput.card_number"
            v-validate="'required|max:190'" >
          <span class="invalid-feedback" v-if="errors.has('card_number')">
            {{ errors.first('card_number') }}
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-6">
          <label class="label">Type</label>
          <select
            class="select_option"
            name="type"
            v-validate="'required'"
            v-model="formInput.type">
            <option :value="item.value" v-for="(item, index) in types" :key="index">{{ item.name }}</option>
          </select>
          <span class="invalid-feedback" v-if="errors.has('type')">
            {{ errors.first('type') }}
          </span>
        </div>
        <!-- <div class="col-4">
          <label class="name">Received Quantity</label>
          <input
            type="text"
            class="input"
            name="received_quantity"
            data-vv-as="received quantity"
            placeholder="Received Quantity"
            v-model.trim="formInput.received_quantity"
            v-validate="'required|numeric|min_value:1'" >
          <span class="invalid-feedback" v-if="errors.has('received_quantity')">
            {{ errors.first('received_quantity') }}
          </span>
        </div> -->
        <div class="col-6 expiry">
          <label class="name">Expiry Date</label>
          <datepicker
            format="dd/MM/yyyy"
            input-class="form-control date-selector"
            v-model="formInput.expiry_date"
            :disabled-dates="{to: yesterday}"
            name="expiry_date"
            placeholder="Expiry Date"
            v-validate="''"
            :class="{'error': errors.has('expiry_date')}"
            data-vv-as="expiry date" />
          <span class="invalid-feedback" v-if="errors.has('expiry_date')">
            {{ errors.first('expiry_date') }}
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-6">
          <label class="name">Purpose of use</label>
          <textarea
            class="input"
            placeholder="Purpose of use"
            name="purpose_use"
            data-vv-as="purpose"
            v-model.trim="formInput.purpose_use"
            v-validate="'max:500'" />
          <span class="invalid-feedback" v-if="errors.has('purpose_use')">
            {{ errors.first('purpose_use') }}
          </span>
        </div>
        <div class="col-6">
          <label class="name">Description</label>
          <textarea
            class="input"
            placeholder="Description"
            name="description"
            v-model.trim="formInput.description"
            v-validate="'max:500'" />
          <span class="invalid-feedback" v-if="errors.has('description')">
            {{ errors.first('description') }}
          </span>
        </div>
      </div>
    </div>
    <div class="action">
      <button class="btn-primary" @click.stop="onClickSave">Save</button>
    </div>
  </div>
</template>
<style lang="scss" scoped>
  .input-form {
    width: 100%;
    .title {
      margin: 0 0 20px 0;
    }
    .content-form {
      width: 100%;
      .row {
        margin-bottom: 20px;
        input {
          padding: 5px;
        }
        select {
          width: 100%;
          height: 35px;
          padding: 2px 10px;
          cursor: pointer;
        }
        .label {
          margin-right: 100px;
        }
        .textarea {
          width: 100%;
          height: 90px;
          resize: none;
        }
        .input-image {
          .preview {
            height: 200px;
            width: 300px;
            margin-top: 20px;
            position: relative;
            img  {
              height: 100%;
              width: 100%;
            }
            .trash {
              position: absolute;
              top: 10px;
              right: 10px;
              background-color: #fff;
              box-shadow: 0px 4px 8px rgb(0 0 0 / 25%);
              cursor: pointer;
              width: 25px;
              padding: 1px 2px;
              img {
                width: 20px;
                height: 20px;
              }
            }
          }
        }
        .expiry {
          ::v-deep .vdp-datepicker {
            input {
              height: 40px;
            }
            .vdp-datepicker__calendar {
              right: 0;
            }
          }
        }
      }
    }
    .btn-primary {
      padding: 15px 50px;
      font-size: 16px;
    }
  }
</style>
<script>
import rf from 'requestfactory'
import Const from 'common/Const'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
import { chain } from 'lodash'
import moment from 'moment'
import Datepicker from 'vuejs-datepicker'

export default {
  components: {
    Datepicker
  },

  props: {
    data: {
      type: Object,
      default: null
    }
  },

  mixins: [RemoveErrorsMixin],

  data () {
    return {
      formInput: {
        material_number: null,
        card_number: null,
        type: null,
        request_quantity: null,
        received_quantity: null,
        issued_quantity: null,
        expiry_date: null,
        purpose_use: null,
        description: null
      },
    }
  },

  computed: {
    editable () {
      return this.data && this.data.id
    },

    types () {
      return Object.values(Const.POL_TYPE)
    },

    yesterday () {
      return moment().subtract(1, 'days').toDate()
    },
  },

  mounted () {
    this.formInput = { ...this.formInput, ...this.data }
  },

  methods: {
    async onClickSave () {
      this.resetError();

      await this.$validator.validateAll();

      if (this.errors.any()) {
        return;
      }

      this.submitRequest().then(() => {
          this.showSuccess()
          this.$emit('item:saved')
        })
        .catch(error => {
          this.processErrors(error)
        })
    },

    submitRequest() {
      const toUtc = (date) => {
        return date ? new moment(date).format(Const.DATE_PATTERN) : null
      }

      this.formInput.expiry_date = toUtc(this.formInput.expiry_date)

      if (this.editable) {
        return rf.getRequest('AdminRequest').updatePolManagement(this.formInput)
      }

      this.formInput.issued_quantity = 0
      return rf.getRequest('AdminRequest').createdPolManagement(this.formInput)
    }
  }
}
</script>
