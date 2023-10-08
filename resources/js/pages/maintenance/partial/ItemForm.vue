<template>
  <div class="input-form">
    <div class="content-form">
      <div class="row">
        <div class="col-4">
          <label>Item Name</label>
          <input
            type="text"
            class="input"
            name="name"
            placeholder="Item Name"
            v-model="inputForm.name"
            v-validate="'required'" >
          <span class="invalid-feedback" v-if="errors.has('name')">
            {{ errors.first('name') }}
          </span>
        </div>
        <div class="col-4">
          <label>Part No</label>
          <input
            type="text"
            class="input"
            name="part_no"
            :disabled="!!data"
            data-vv-as="part no"
            placeholder="Part No"
            v-model="inputForm.part_no"
            v-validate="'required'" >
          <span class="invalid-feedback" v-if="errors.has('part_no')">
            {{ errors.first('part_no') }}
          </span>
        </div>
        <div class="col-4">
          <label>Material No</label>
          <input
            type="text"
            class="input"
            name="material_no"
            data-vv-as="material no"
            placeholder="Material No"
            v-model="inputForm.material_no"
            v-validate="'required'" >
          <span class="invalid-feedback" v-if="errors.has('material_no')">
            {{ errors.first('material_no') }}
          </span>
        </div>
      </div>

      <div class="row">
        <!-- <div class="col-4"> -->
          <!-- <label>Location</label> -->
          <!-- <input
            type="text"
            class="input"
            name="location"
            placeholder="Location"
            v-model="inputForm.location"
            v-validate="''" > -->
          <!-- <span class="invalid-feedback" v-if="errors.has('location')">
            {{ errors.first('location') }}
          </span> -->
        <!-- </div> -->
        <div class="col-4">
          <label>Supplier’s Email</label>
          <input
            type="text"
            class="input"
            name="supplier_email"
            data-vv-as="supplier’s email"
            placeholder="Supplier’s Email"
            v-model="inputForm.supplier_email"
            v-validate="''" >
          <span class="invalid-feedback" v-if="errors.has('supplier_email')">
            {{ errors.first('supplier_email') }}
          </span>
        </div>
        <div class="col-4">
          <label>Item Acct</label>
          <input
            type="text"
            class="input"
            name="mat_grp"
            data-vv-as="item acct"
            placeholder="Item Acct"
            v-model="inputForm.mat_grp"
            v-validate="''" >
          <span class="invalid-feedback" v-if="errors.has('mat_grp')">
            {{ errors.first('mat_grp') }}
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-4">
          <label>CriCode</label>
          <input
            type="text"
            class="input"
            name="cricode"
            placeholder="CriCode"
            v-model="inputForm.cricode"
            v-validate="''" >
          <span class="invalid-feedback" v-if="errors.has('cricode')">
            {{ errors.first('cricode') }}
          </span>
        </div>
        <div class="col-4">
          <label>UOM</label>
          <input
            type="text"
            class="input"
            name="jom"
            data-vv-as="jom"
            placeholder="UOM"
            v-model="inputForm.jom"
            v-validate="''" >
          <span class="invalid-feedback" v-if="errors.has('jom')">
            {{ errors.first('jom') }}
          </span>
        </div>
        <div class="col-4">
          <label>Mat’l Grp</label>
          <input
            type="text"
            class="input"
            name="item_acct"
            data-vv-as="item acct"
            placeholder="Item Acct"
            v-model="inputForm.item_acct"
            v-validate="''" >
          <span class="invalid-feedback" v-if="errors.has('item_acct')">
            {{ errors.first('item_acct') }}
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-4">
          <label>Batch Number</label>
          <div class="d-flex">
            <div>
              <label class="checkbox-container">
                <input
                  type="checkbox"
                  :disabled="!inputForm.type"
                  v-model="inputForm.has_batch_no"
                  name="has_batch_no"
                  data-vv-as="batch no"
                  v-validate="''" >
                <span class="checkmark" :class="{'not-allowed': !inputForm.type}" />
              </label>
            </div>
          </div>
        </div>
        <div class="col-4">
          <label class="label">Serial Number</label>
          <div class="d-flex">
            <div>
              <label class="checkbox-container">
                <input
                  type="checkbox"
                  :disabled="!inputForm.type"
                  v-model="inputForm.has_serial_no"
                  name="has_serial_no"
                  data-vv-as="serial no"
                  v-validate="inputForm.type == Const.ITEM_TYPE.EUC.value ? 'required' : ''" >
                <span class="checkmark" :class="{'not-allowed': !inputForm.type}" />
              </label>
              <span class="invalid-feedback" v-if="errors.has('has_serial_no')">
                {{ errors.firstRule('has_serial_no') == 'required' ? 'EUC item must have serial number for tracking!' : errors.first('has_serial_no') }}
              </span>
            </div>
          </div>
        </div>
        <div class="col-4">
          <label>Item Type</label>
          <select
              class="input"
              v-model="inputForm.type"
              name="type"
              data-vv-as="item type"
              v-validate="'required'" >
            <option :value="item.value" v-for="(item, index) in itemTypes" :key="index">{{ item.name }}</option>
          </select>
          <span class="invalid-feedback" v-if="errors.has('type')">
            {{ errors.first('type') }}
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-4">
          <label>STEs Item Min Charge Time&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
          <div class="d-flex">
            <div>
              <label class="checkbox-container">
                <input
                  type="checkbox"
                  :disabled="!inputForm.type"
                  v-model="inputForm.has_charge_time"
                  name="has_charge_time"
                  data-vv-as="use charge time"
                  v-validate="''" >
                <span class="checkmark" :class="{'not-allowed': !inputForm.type}" />
              </label>
            </div>
          </div>
        </div>
        <div class="col-4">
          <label>STEs dates of calibration due/inspection</label>
          <div class="d-flex">
            <div>
              <label class="checkbox-container">
                <input
                  type="checkbox"
                  :disabled="!inputForm.type"
                  v-model="inputForm.has_calibration_due"
                  name="has_calibration_due"
                  data-vv-as="use calibration due"
                  v-validate="''" >
                <span class="checkmark" :class="{'not-allowed': !inputForm.type}" />
              </label>
            </div>
          </div>
        </div>
        <div class="col-4">
          <label>Perishable Item Expiry Date&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>

          <div class="d-flex expiry">
            <div>
              <label class="checkbox-container">
                <input
                  type="checkbox"
                  :disabled="!inputForm.type"
                  v-model="inputForm.has_expiry_date"
                  name="has_expiry_date"
                  data-vv-as="use expiry date"
                  v-validate="''" >
                <span class="checkmark" :class="{'not-allowed': !inputForm.type}" />
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-4">
          <label>User Access Rights</label>
          <select-box
            :options="roles"
            v-model="inputForm.user_access"
            placeholder="User Access"
            multiple />
          <span class="invalid-feedback" v-if="errors.has('user_access')">
            {{ errors.first('user_access') }}
          </span>
        </div>
        <div class="col-4">
          <!-- <label>Spare Field #1</label>
          <input
            type="text"
            class="input"
            name="field1"
            data-vv-as="spare field #1"
            placeholder="Spare Field #1"
            v-model="inputForm.field1"
            v-validate="''" >
          <span class="invalid-feedback" v-if="errors.has('field1')">
            {{ errors.first('field1') }}
          </span> -->
          <label>Load/Hydrostatic Test Due</label>

          <div class="d-flex expiry">
            <div>
              <label class="checkbox-container">
                <input
                  type="checkbox"
                  :disabled="!inputForm.type"
                  v-model="inputForm.has_load_hydrostatic_test_due"
                  name="has_load_hydrostatic_test_due"
                  data-vv-as="use load hydrostatic test due"
                  v-validate="''" >
                <span class="checkmark" :class="{'not-allowed': !inputForm.type}" />
              </label>
            </div>
          </div>
        </div>
        <div class="col-4">
          <label>Spare Field #2</label>
          <input
            type="text"
            class="input"
            name="field2"
            data-vv-as="spare field #2"
            placeholder="Spare Field #2"
            v-model="inputForm.field2"
            v-validate="''" >
          <span class="invalid-feedback" v-if="errors.has('field2')">
            {{ errors.first('field2') }}
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-6">
          <label class="name">Description</label>
          <textarea
            class="textarea"
            placeholder="Description"
            name="description"
            v-model="inputForm.description"
            v-validate="'max:190'" />
          <span class="invalid-feedback" v-if="errors.has('description')">
            {{ errors.first('description') }}
          </span>
        </div>
        <div class="col-6 input-image">
          <label>Item Image</label>
          <input
            type="file"
            accept="image/*"
            name="url"
            @change="onFileChange"
            ref="image" >
          <span class="invalid-feedback" v-if="errors.has('url')">
            {{ errors.first('url') }}
          </span>
          <div class="preview" v-if="inputForm.imagePreview">
            <img :src="inputForm.imagePreview" />
            <div class="trash" @click.stop="onRemoveImage">
              <img src="/images/icons/icon-cancel.svg">
            </div>
          </div>
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
          height: 35px;
              padding: 2px 10px;
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
        ::v-deep .vdp-datepicker {
          input, select {
            height: 30px
          }
        }
        .expiry {
          ::v-deep .vdp-datepicker {
            &:last-child {
              .vdp-datepicker__calendar {
                right: 0;
              }
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
import moment from 'moment'
import rf from 'requestfactory'
import Const from 'common/Const'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
import { chain, cloneDeep, remove, each, padStart, has } from 'lodash'
import Datepicker from 'vuejs-datepicker';
import VueTimepicker from 'vue2-timepicker';
import 'vue2-timepicker/dist/VueTimepicker.css';

export default {
  components: {
    Datepicker,
    VueTimepicker
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
      inputForm: {
        name: null,
        part_no: null,
        material_no: null,
        location: null,
        supplier_email: null,
        mat_grp: null,
        cricode: null,
        jom: null,
        item_acct: null,
        type: null,
        has_batch_no: null,
        has_serial_no: null,
        has_charge_time: null,
        has_calibration_due: null,
        has_expiry_date: null,
        has_load_hydrostatic_test_due: null,
        user_access: null,
        field1: null,
        field2: null,
        url: null,
        imagePreview: null
      },
      Const
    }
  },

  computed: {
    itemTypes () {
      return Object.values(Const.ITEM_TYPE)
    },

    roles () {
      return Const.USER_ROLES
    }
  },

  watch: {
    data () {
      this.initData()
    }
  },

  mounted () {
    this.initData()
  },

  methods: {
    initData () {
      this.inputForm = {
        ...this.inputForm,
        ...this.data,
        imagePreview: this.data?.url
      }
    },

    async onClickSave () {
      this.resetError()

      await this.$validator.validateAll()

      if (this.errors.any()) {
        return
      }

      this.submitRequest(this.inputForm)
        .then(res => {
          this.showSuccess('Successfully!');
          this.inputForm = {}
          this.resetError()
          this.$emit('item:saved', res.data)
        })
        .catch(error => {
          this.processErrors(error)
        })
    },

    submitRequest (data) {
      const params = cloneDeep(data)

      const newFormData = (data) => {
        const keys = Object.keys(data)

        const formData = new FormData()
        each(keys, key => {
          if (!has(data, key)) {
            return
          }

          let value = data[key]
          if (Array.isArray(data[key])) {
            value = JSON.stringify(value)
          }

          formData.append(key, value)
        })

        return formData
      }

      if (data.id) {
        return rf.getRequest('AdminRequest').updateSpare(newFormData(data))
      }
      return rf.getRequest('AdminRequest').createSpare(newFormData(data))
    },

    onFileChange (e) {
      const file = e.target.files[0];
      this.inputForm.url = file
      this.inputForm.imagePreview = URL.createObjectURL(file)
    },

    onRemoveImage () {
      this.$refs.image.value = null
      this.inputForm.url = null
      this.inputForm.imagePreview = null
    }
  }
}
</script>
