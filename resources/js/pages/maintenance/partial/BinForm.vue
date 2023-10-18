<template>
  <div class="input-form">
    <div class="content-form">
      <div class="row">
        <div class="col-4">
          <label>Item Name</label>
          <select class="input" v-model="inputForm.spare_id" name="spare_id" :disabled="!!inputForm.is_drawer"
            data-vv-as="spare">
            <option :value="item.id" v-for="(item, index) in spares" :key="index" :class="{
              bluetext: data.spares.map((i) => i.id).includes(item.id),
            }">
              <p>
                {{ item.name }}
              </p>
            </option>
          </select>

          <!-- <v-select
            class="input style-chooser"
            name="spare_id"
            v-model="inputForm.spare_id"
            :disabled="!!inputForm.is_drawer"
            :reduce="(spare) => spare.code"
            :options="spares"
          ></v-select> -->
          <span class="invalid-feedback" v-if="errors.has('spare_id')">
            {{ errors.first("spare_id") }}
          </span>
        </div>
        <div class="col-4">
          <label>Quantity</label>
          <input type="text" class="input" name="quantity" placeholder="Quantity" :disabled="disableQuantity"
            v-model="inputForm.quantity" v-validate="`required|numeric|min_value:${inputForm.min || 0}|max_value:${inputForm.max || 1000
              }`
              " />
          <span class="invalid-feedback" v-if="errors.has('quantity')">
            {{ errors.first("quantity") }}
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-4">
          <label>Critical</label>
          <input type="text" class="input" name="critical" placeholder="Critical" v-model.trim="inputForm.critical"
            v-validate="'numeric|min_value:0'" />
          <span class="invalid-feedback" v-if="errors.has('critical')">
            {{ errors.first("critical") }}
          </span>
        </div>
        <div class="col-4">
          <label>Minimum Quantity</label>
          <input type="text" class="input" name="min" data-vv-as="minimum quantity" placeholder="Minimum Quantity"
            v-model.trim="inputForm.min" v-validate="'required|numeric|min_value:0'" />
          <span class="invalid-feedback" v-if="errors.has('min')">
            {{ errors.first("min") }}
          </span>
        </div>
        <div class="col-4">
          <label>Maximum Quantity</label>
          <input type="text" class="input" name="max" data-vv-as="maximum quantity" placeholder="Maximum Quantity"
            v-model.trim="inputForm.max" v-validate="`required|numeric|min_value:${inputForm.min}`" />
          <span class="invalid-feedback" v-if="errors.has('max')">
            {{ errors.first("max") }}
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <label class="name">Description</label>
          <textarea class="textarea" placeholder="Description" name="description" v-model.trim="inputForm.description"
v-validate="'max:190'" />
          <span class="invalid-feedback" v-if="errors.has('description')">
            {{ errors.first("description") }}
          </span>
        </div>
      </div>
    </div>

    <bin-configures :spare="inputForm" :data="inputForm.configures" ref="binConfigures" v-if="inputForm.spare_id" />

    <div class="actions">
      <button @click="onAddItem" class="btn-primary" style="padding: 8px 27px;">Add</button>
      <button @click="onClearList" class="btn-primary"
        style="background: none; border-radius: 5px; border: 2px solid white;padding: 8px 27px;">
        Clear
      </button>
    </div>

    <template>
      <div class="table-scroller">
        <table>
          <thead>
            <th>SN</th>
            <th>Item Name</th>
            <th>Qty</th>
            <th>Critical</th>
            <th>Min</th>
            <th>Max</th>
            <th>Desc</th>
            <th>Batch</th>
            <th>Serial</th>
            <th>Charge Time</th>
            <th>Load/Hydrostatic Test Due</th>
            <th>Create At</th>
            <th>Expiry</th>
            <th>Actions</th>
          </thead>
          <tbody>
            <tr v-for="(item, index) in items" :key="index">
              <td>{{ index + 1 }}</td>
              <td>{{ item.name }}</td>
              <td>{{ item.quantity }}</td>
              <td>{{ item.critical }}</td>
              <td>{{ item.min }}</td>
              <td>{{ item.max }}</td>
              <td>{{ item.description }}</td>
              <template v-if="item.configures.length > 0">
                <td>{{ item.configures[0].batch_no}}</td>
                <td>{{ item.configures[0].serial_no}}</td>
                <td>
                  <vue-timepicker
                    :name="'charge_time-' + index"
                    v-validate="'required'"
                    v-model="item.charge_time"
                    format="HH:mm"
                  />
                </td>  
                <td>     
                  <datepicker
                    format="dd/MM/yyyy"
                    input-class="form-control date-selector"
                    v-model="item.configures[0].load_hydrostatic_test_due"
                    :disabled-dates="{ to: yesterday }"
                    name="load_hydrostatic_test_due"
                    v-validate="'required'"
                    data-vv-as="calibration due"
                    v-if="item.configures[0].has_load_hydrostatic_test_due"
                  />
                </td>
                <td>
                  {{item.configures[0].created_at}} 
                </td>
                <td>
                  {{item.configures[0].expiry_date}} 
                </td>
              </template>
              <template v-else>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </template> 
              <td>
<img src="/images/icons/icon-cancel.svg" width="22px" @click="showDeleteModal(item)" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>

    <div style="justify-content: end" class="actions mt-3">
      <button class="btn-primary" @click.stop="onClickSave">Save</button>
    </div>
  </div>
</template>

<script>
import rf from "requestfactory";
import moment from "moment";
import RemoveErrorsMixin from "common/RemoveErrorsMixin";
import Const from "common/Const";
import Utils from "common/Utils";
import { chain, cloneDeep, isEmpty, times, size, debounce } from "lodash";
import BinConfigures from "./BinConfigures";
import Datepicker from "vuejs-datepicker";
import VueTimepicker from "vue2-timepicker";
import "vue2-timepicker/dist/VueTimepicker.css";

export default {
  components: {
    BinConfigures,
    VueTimepicker,
    Datepicker,
  },

  props: {
    data: {
      type: Object,
      default: null,
    },
  },

  mixins: [RemoveErrorsMixin],

  data() {
    return {
      inputForm: {
        spare_id: null,
        min: null,
        max: null,
        critical: null,
        description: null,
        configures: [],
      },
      spares: [],
      items: [],
      listSpares: [], 
    };
  },

  computed: {
    yesterday() {
      return moment()
        .subtract(1, "days")
        .toDate();
    },
    showConfigures() {
      return !isEmpty(this.inputForm.configures);
    },

    disableQuantity() {
      return false;
    },

    visibleBinConfigure() {
      return (
        this.inputForm.has_batch_no ||
        this.inputForm.has_serial_no ||
        this.inputForm.has_charge_time ||
        this.inputForm.has_calibration_due ||
        this.inputForm.has_expiry_date ||
        this.inputForm.has_load_hydrostatic_test_due
      );
    },
  },

  watch: {
    "inputForm.spare_id"(newValue) {
      const item = chain(this.spares)
        .filter((record) => record.id === newValue)
        .head()
        .value();

      if (!item) {
        return;
      }

      this.inputForm = {
        ...this.inputForm,
        has_batch_no: item.has_batch_no,
        has_serial_no: item.has_serial_no,
        has_charge_time: item.has_charge_time,
        has_calibration_due: item.has_calibration_due,
        has_expiry_date: item.has_expiry_date,
        has_load_hydrostatic_test_due: item.has_load_hydrostatic_test_due,
      };
    },

    "inputForm.quantity": debounce(function () {
      this.initConfigures();

      const limit = parseInt(this.inputForm.quantity) || 0;
      if (!limit) {
        return;
      }
      this.inputForm.configures = chain(this.inputForm.configures)
        .take(limit)
        .value();
    }, 300),
  },

  mounted() {
    this.inputForm = {
      ...this.inputForm,
      ...this.data,
      spare_id: this.data.spares.length > 0 ? this.data?.spares[0]?.id : null,
      critical: this.data?.critical || 1,
description: this.data?.spares[0]?.description || null,
      min: this.data?.min || 1,
      max: this.data?.max || 1,
      quantity: this.data?.quantity || 1,
    };

    
    this.items = this.data.spares.map((i) => ({
      ...i,
      spare_id: i.id,
      quantity: this.data?.quantity,
      critical: this.data?.critical,
      min: this.data?.min,
      max: this.data?.max,
      configures: this.data?.configures
    }));
    
    rf.getRequest("AdminRequest").getBinId(this.data.id);

    this.initConfigures();
    this.getSpares();
  },

  methods: {
    showDeleteModal(item) {
      const _handler = () => {
        this.items = this.items.filter((i) => i.spare_id !== item.id);
      };
      this.confirmAction({ callback: _handler, message: "Do you want to delete?" });
    },

    resetForm() {
      this.inputForm = {
        spare_id: null,
        min: 1,
        max: 1,
        critical: 1,
        description: null,
        quantity: 1,
        configures: [],
      };
    },

    initConfigures() {
      // const limit = parseInt(this.inputForm.quantity) || 0
      const limit = 1;
      const currentSize = size(this.inputForm.configures);
      const needMore = limit - currentSize < 0 ? 0 : limit - currentSize;

      const newData = Array(needMore).fill({
        batch_no: null,
        serial_no: null,
        has_charge_time: null,
        charge_time: null,
        has_calibration_due: null,
        calibration_due: null,
        has_expiry_date: null,
        expiry_date: null,
        has_load_hydrostatic_test_due: null,
        load_hydrostatic_test_due: null,
      });

      this.inputForm.configures = chain(this.inputForm.configures || [])
        .concat(newData)
        .map((item, index) => {
          return {
            ...item,
            scope: `row-${index + 1}`,
            input_charge_time: Utils.stringTime2Object(item.charge_time),
            calibration_due: Utils.utcToClient(
              item.calibration_due,
              Const.DATE_PATTERN
            ),
            expiry_date: Utils.utcToClient(
              item.expiry_date,
              Const.DATE_PATTERN
            ),
            load_hydrostatic_test_due: Utils.utcToClient(
              item.load_hydrostatic_test_due,
              Const.DATE_PATTERN
            ),
          };
        })
        .value();
    },

    getSpares() {
      const params = { no_pagination: true };
      rf.getRequest("AdminRequest")
        .getSpares(params)
        .then((res) => {
          this.listSpares = res.data
          this.spares = chain(res.data || [])
            // .filter(item => item.type !== Const.ITEM_TYPE.EUC.value)
            .map((item) => {
              return {
                ...item,
                label: item.name,
                code: item.id,
              };
            })
            .value();
        }).catch((error) => {
          this.processErrors(error);
        });
    },

    async onClickSave() {
this.resetError();

      await this.$validator.validateAll();

      if (this.$refs.binConfigures) {
        await this.$refs.binConfigures.validateData();
      }

      if (this.errors.any()) {
        return;
      }

      const toUTc = (date) => {
        return date ? new moment(date).utc().format(Const.DATE_PATTERN) : null;
      };

      chain(this.inputForm.configures)
        .each((item) => {
          (item.charge_time = Utils.objTime2String(item.input_charge_time)),
            (item.calibration_due = toUTc(item.calibration_due));
          item.expiry_date = toUTc(item.expiry_date);
          item.load_hydrostatic_test_due = toUTc(
            item.load_hydrostatic_test_due
          );
        })
        .value();

      const transData = {
        ...this.inputForm,
        formInput: this.items.map(i => ({
          ...i,
        })),
      }

      this.submitRequest(transData)
        .then((res) => {
          this.showSuccess("Successfully!");
          this.resetError();
          this.resetForm();
          this.$emit("item:saved", res.data);
        })
        .catch((error) => {
          this.processErrors(error);
        });
    },

    submitRequest(data) {
      const params = cloneDeep(data);
      return rf.getRequest("AdminRequest").updateBin(data);
    },

    onAddItem() {
      const spare = this.spares.find((i) => i.id == this.inputForm.spare_id);
      const existingItem = this.items.find(
        (item) => item.spare_id === spare.id
      );
      if (existingItem) {
        this.showError("Duplicated! This item was added!");
      } else {
        this.items.push({
          ...this.inputForm,
          name: spare.name,
          charge_time: this.inputForm.configures.length > 0 ? `${this.inputForm.configures[0].input_charge_time?.HH}:${this.inputForm.configures[0].input_charge_time?.HH}`:''
        });
        this.updateListQuantitiesMinMax();
        this.resetForm()
      }
    },

    updateListQuantitiesMinMax() {
      if (this.items.length > 0) {
        const totalQuantity = this.items.reduce(
          (acc, item) => item.quantity,
          0
        );
        const minValues = this.items.map((item) => item.min);
        const maxValues = this.items.map((item) => item.max);
        const min = Math.min(...minValues);
        const max = Math.max(...maxValues);

        this.items.forEach((item) => {
          item.quantity = totalQuantity;
          item.min = min;
          item.max = max;
        });
      }
    },

    onClearList() {
      this.items = [];
    },

    // onSaveData() {
    //   const data = {
    //     ...this.items[0],
    //     ...this.inputForm,
    //     spare_id: this.items.map((i) => i.spare_id),
    //     configures: this.inputForm.configures.map((i) => ({
    //       ...i,
    //       charge_time: i.has_charge_time
    //         ? `${i.input_charge_time.HH}:${i.input_charge_time.mm}`
    //         : null,
    //     })),
    //   };

    //   return rf
//     .getRequest("AdminRequest")
    //     .updateBin(data)
    //     .then((res) => {
    //       this.showSuccess("Successfully!");
    //       this.$emit("item:saved", res.data);
    //     })
    //     .catch(() => this.processErrors("Fail"));
    // },
  },
};
</script>

<style lang="scss" scoped>
.input-form {
  width: 100%;
  .table-scroller{
    overflow: unset;
    max-height: unset;
  }
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
    }
  }

  .btn-primary {
    padding: 15px 50px;
    font-size: 16px;
  }
}

.actions {
  display: flex;
  gap: 24px;
  margin-bottom: 24px;
}
</style>

<style>
.style-chooser input.vs__search {
  border: none !important;
}

.bluetext {
  background-color: #6cb2eb;
  border-bottom: 1px solid #ccc;
}

.style-chooser .vs__selected,
.style-chooser .vs__search::placeholder,
.style-chooser .vs__dropdown-toggle,
.style-chooser .vs__dropdown-menu {
  background: #11131d;
  border: 1px solid #363a47;
  color: #fff;
  border-radius: 0;
}

.style-chooser .vs__selected {
  border: none;
}

.style-chooser .vs__clear,
.style-chooser .vs__open-indicator {
  fill: #fff;
}

.style-chooser.vs--disabled .vs__clear,
.style-chooser.vs--disabled .vs__open-indicator {
  fill: rgba(60, 60, 60, 0.5);
}

.style-chooser.vs--disabled .vs__selected,
.style-chooser.vs--disabled .vs__search,
.style-chooser.vs--disabled .vs__dropdown-toggle,
.style-chooser.vs--disabled .vs__dropdown-menu,
.style-chooser.vs--disabled .vs__open-indicator {
  background: #212430;
}

.v-select.style-chooser #vs1__listbox li {
  color: #ffffff;
}
</style>