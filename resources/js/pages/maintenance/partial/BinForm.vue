<template>
  <div class="input-form">
    <div class="content-form">
      <div class="row">
        <div class="col-4">
          <label>Item Name</label>
          <select
            class="input"
            v-model="inputForm.spare_id"
            name="spare_id"
            :disabled="!!inputForm.is_drawer"
            data-vv-as="spare"
          >
            <option
              :value="item.id"
              v-for="(item, index) in spares"
              :key="index"
              :class="{
                bluetext: data.spares.map((i) => i.id).includes(item.id),
              }"
            >
              <p>
                {{ item.name }}
              </p>
            </option>
          </select>
          <span class="invalid-feedback" v-if="errors.has('spare_id')">
            {{ errors.first("spare_id") }}
          </span>
        </div>

        <div class="col-4">
          <label>Quantity</label>
          <input
            type="text"
            class="input"
            name="quantity"
            placeholder="Quantity"
            :disabled="disableQuantity"
            v-model="inputForm.quantity"
            v-validate="
              `required|numeric|min_value:${inputForm.min || 0}|max_value:${
                inputForm.max || 1000
              }`
            "
          />
          <span class="invalid-feedback" v-if="errors.has('quantity')">
            {{ errors.first("quantity") }}
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-4">
          <label>Critical</label>
          <input
            type="text"
            class="input"
            name="critical"
            placeholder="Critical"
            v-model.trim="inputForm.critical"
            v-validate="'numeric|min_value:0'"
          />
          <span class="invalid-feedback" v-if="errors.has('critical')">
            {{ errors.first("critical") }}
          </span>
        </div>

        <div class="col-4">
          <label>Minimum Quantity</label>
          <input
            type="text"
            class="input"
            name="min"
            data-vv-as="minimum quantity"
            placeholder="Minimum Quantity"
            v-model.trim="inputForm.min"
            v-validate="'required|numeric|min_value:0'"
          />
          <span class="invalid-feedback" v-if="errors.has('min')">
            {{ errors.first("min") }}
          </span>
        </div>

        <div class="col-4">
          <label>Maximum Quantity</label>
          <input
            type="text"
            class="input"
            name="max"
            data-vv-as="maximum quantity"
            placeholder="Maximum Quantity"
            v-model.trim="inputForm.max"
            v-validate="`required|numeric|min_value:${inputForm.min}`"
          />
          <span class="invalid-feedback" v-if="errors.has('max')">
            {{ errors.first("max") }}
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <label class="name">Description</label>
          <textarea
            class="textarea"
            placeholder="Description"
            name="description"
            v-model.trim="inputForm.description"
            v-validate="'max:190'"
          />
          <span class="invalid-feedback" v-if="errors.has('description')">
            {{ errors.first("description") }}
          </span>
        </div>
      </div>
    </div>

    <bin-configures
      :spare="inputForm"
      :data="inputForm.configures"
      ref="binConfigures"
      v-if="inputForm.spare_id"
    />

    <div class="actions">
      <button @click="onAddItem" class="btn-primary" style="padding: 8px 27px">
        Add
      </button>
      <button
        @click="onClearList"
        class="btn-primary"
        style="
          background: none;
          border-radius: 5px;
          border: 2px solid white;
          padding: 8px 27px;
        "
      >
        Clear
      </button>
    </div>

    <template>
      <div class="table-scroller">
        <table>
          <thead>
            <th>SN</th>
            <th>Item Name</th>
            <th>Item Type</th>
            <th>Qty</th>
            <th>Critical</th>
            <th>Min</th>
            <th>Max</th>
            <th>Desc</th>
            <th>Batch</th>
            <th>Serial</th>
            <th>Charge Time</th>
            <th>Inspection</th>
            <th>Hyd Test Due</th>
            <!-- <th>Create At</th> -->
            <th>Expiry</th>
            <th>Actions</th>
          </thead>
          <tbody>
            <tr v-for="(item, index) in items" :key="index">
              <td>{{ index + 1 }}</td>
              <td>
                <template v-if="editingItem === item">
                  <select
                    class="p-10px"
                    v-model="item.spare_id"
                    name="spare_id"
                    @change="handleSelectChange(item)"
                  >
                    <option
                      v-for="(item, spareIndex) in spares"
                      :value="item.id"
                      :key="spareIndex"
                      :class="{
                        bluetext: data.spares
                          .map((i) => i.id)
                          .includes(item.id),
                      }"
                    >
                      {{ item.name }}
                    </option>
                  </select>
                </template>

                <template v-else>
                  <span>{{ item.name }}</span>
                </template>
              </td>
              <template>
                <td>{{ types[item.type.toUpperCase()].name }}</td>
                <td>
                  <span v-if="editingItem !== item">{{ item.quantity }}</span>
                  <input
                    name="quantity_edit"
                    v-validate="
                      `required|numeric|min_value:${
                        item.min || 0
                      }|max_value:${item.max || 1000}`
                    "
                    class="input_edit"
                    v-else
                    v-model="item.quantity"
                  />
                  <template  v-if="editingItem == item">
                    <span class="invalid-feedback" v-if="errors.has('quantity_edit')">
                      {{ errors.first("quantity_edit") }}
                    </span>
                  </template>
                </td>
                <td>
                  <span v-if="editingItem !== item">{{ item.critical }}</span>
                  <input class="input_edit" v-else v-model="item.critical" />
                </td>
                <td>
                  <span v-if="editingItem !== item">{{ item.min }}</span>
                  <input class="input_edit" v-else v-model="item.min" />
                </td>
                <td>
                  <span v-if="editingItem !== item">{{ item.max }}</span>
                  <input class="input_edit" v-else v-model="item.max" />
                </td>
                <td>
                  <span v-if="editingItem !== item">{{
                    item.configures[0].description
                  }}</span>
                  <input class="input_edit" v-else v-model="item.configures[0].description" />
                </td>
                <td>
                  <template v-if="item.batch_no !== null">
                    <span v-if="editingItem !== item">{{ item.batch_no }}</span>
                    <input class="input_edit" v-else v-model="item.batch_no" />
                  </template>
                </td>
                <td>
                  <template v-if="item.serial_no !== null">
                    <span v-if="editingItem !== item">{{
                      item.serial_no
                    }}</span>
                    <input class="input_edit" v-else v-model="item.serial_no" />
                  </template>
                </td>
              </template>

              <template v-if="item.configures.length > 0">
                <td>
                  <template v-if="item.charge_time !== null">
                    <vue-timepicker
                      :name="'charge_time-' + index"
                      v-validate="'required'"
                      v-model="item.charge_time"
                      format="HH:mm"
                      v-if="!!item.configures[0].has_charge_time"
                      drop-direction="auto"
                    />
                  </template>
                </td>
                <td>
                  <datepicker
                    format="dd/MM/yyyy"
                    input-class="form-control date-selector"
                    v-model="item.configures[0].calibration_due"
                    :disabled-dates="{ to: yesterday }"
                    name="calibration_due"
                    v-validate="'required'"
                    data-vv-as="calibration due"
                    popperContainer="{CalendarContainer}"
                    v-if="item.configures[0].has_calibration_due"
                    style="min-width: 100px"
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
                    data-vv-as="hydrostatic due"
                    v-if="item.configures[0].has_load_hydrostatic_test_due"
                    style="min-width: 100px"
                  />
                </td>
                <!-- <td>
                  {{item.configures[0].created_at}} 
                </td> -->
                <td>
                  <datepicker
                    format="dd/MM/yyyy"
                    input-class="form-control date-selector"
                    v-model="item.configures[0].expiry_date"
                    :disabled-dates="{ to: yesterday }"
                    name="expiry_date"
                    v-validate="'required'"
                    data-vv-as="calibration due"
                    v-if="item.configures[0].has_expiry_date"
                    style="min-width: 100px"
                  />
                </td>
              </template>
              <template v-else>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </template>

              <td class="text-center">
                <div class="flex-center">
                  <template v-if="editingItem !== item">
                    <img
                      src="/images/icons/icon-edit.svg"
                      width="22px"
                      @click="onClickEdit(item)"
                    />
                  </template>
                  <template v-else>
                    <img
                      src="/images/icons/icon-save.svg"
                      width="22px"
                      @click.stop="onClickCancel()"
                    />
                  </template>
                  <img
                    src="/images/icons/icon-trash.svg"
                    width="22px"
                    @click.stop="showDeleteModal(item)"
                  />
                </div>
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
import { chain, cloneDeep, isEmpty, size, debounce } from "lodash";
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
        max: 1000,
        critical: null,
        description: null,
        configures: [],
      },
      spares: [],
      items: [],
      types: Const.ITEM_TYPE,
      editingItem: null,
    };
  },

  computed: {
    yesterday() {
      return moment().subtract(1, "days").toDate();
    },

    showConfigures() {
      return !isEmpty(this.inputForm.configures);
    },

    disableQuantity() {
      return false;
    },

    // visibleBinConfigure() {
    //   return (
    //     this.inputForm.has_batch_no ||
    //     this.inputForm.has_serial_no ||
    //     this.inputForm.has_charge_time ||
    //     this.inputForm.has_calibration_due ||
    //     this.inputForm.has_expiry_date ||
    //     this.inputForm.has_load_hydrostatic_test_due
    //   );
    // },
  },

  watch: {
    "inputForm.spare_id"(newValue) {
      if (newValue) {
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
          description: item.description
        };
      }
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
      critical: 1,
      min: 1,
      max: 1000,
      quantity: 1,
      configures: [],
    };

    if (this.data.spares.length) {
      this.items = this.data.spares.map((i) => {
        return {
          ...i,
          id: this.data.id,
          spare_id: i.id,
          quantity: i.pivot.quantity_oh,
          critical: i.pivot.critical,
          min: i.pivot.min,
          max: i.pivot.max,
          charge_time:
            this.data?.configures.find((ele) => ele.spare_id == i.id)
              ?.charge_time ?? null,
          batch_no:
            this.data?.configures.find((ele) => ele.spare_id == i.id)
              ?.batch_no ?? null,
          serial_no:
            this.data?.configures.find((ele) => ele.spare_id == i.id)
              ?.serial_no ?? null,
          configures: [
            this.data?.configures.find((ele) => ele.spare_id == i.id),
          ].filter(Boolean),
        };
      });
    }

    rf.getRequest("AdminRequest").getBinId(this.data.id);
    this.initConfigures();
    this.getSpares();
  },

  methods: {
    handleSelectChange(item) {
      console.log(item);
    },

    showDeleteModal(item) {
      const _handler = () => {
        this.items = this.items.filter((i) => i.spare_id !== item.spare_id);
      };
      this.confirmAction({
        callback: _handler,
        message: "Are you sure you want to delete?",
      });
    },

    onClickEdit(item) {
      this.editingItem = item;
    },

    onClickCancel() {
      this.editingItem = null;
    },

    async onClickSave() {
      if (this.items.length === 0) {
        this.showError("Must add at least 1 item!");
        return false;
      }
      this.resetError();
      await this.$validator.validateAll();

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
          expiry_date:
            this.inputForm.configures.length > 0
              ? this.inputForm.configures[0].expiry_date
              : null,
          load_hydrostatic_test_due:
            this.inputForm.configures.length > 0
              ? this.inputForm.configures[0].load_hydrostatic_test_due
              : null,
          calibration_due:
            this.inputForm.configures.length > 0
              ? this.inputForm.configures[0].calibration_due
              : null,
          charge_time:
            this.inputForm.configures.length > 0
              ? `${this.inputForm.configures[0].input_charge_time?.HH}:${this.inputForm.configures[0].input_charge_time?.mm}`
              : "",
        });
        this.resetForm();
        this.editingItem = null;
      }
    },

    onClickSave() {
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
        // ...this.inputForm,
        formInput: this.items.map((i) => ({ ...i })),
      };

      if (this.items.length === 0) {
        this.showError("The list cannot be empty!");
      } else {
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
      }
      // console.log(transData)
      this.editingItem = null;
    },

    submitRequest(data) {
      const params = cloneDeep(data);
      return rf.getRequest("AdminRequest").updateBin(data);
    },

    onAddItem() {
      if (this.inputForm.spare_id) {
        const spare = this.spares.find((i) => i.id == this.inputForm.spare_id);
        const existingItem = this.items.find(
          (item) => item.spare_id === spare.id
        );
        if (existingItem) {
          this.showError("Duplicated! This item was added!");
        } else {
          this.items.push({
            ...this.inputForm,
            id: this.data.id,
            spare_id: spare.id,
            name: spare.name,
            type: spare.type,
            charge_time:
              this.inputForm.configures.length > 0
                ? `${this.inputForm.configures[0].input_charge_time?.HH}:${this.inputForm.configures[0].input_charge_time?.mm}`
                : "",
          });
          // this.updateListQuantitiesMinMax();
          this.resetForm();
          this.initConfigures();
        }
      } else {
        this.showError("Have to choose an item!");
      }
    },

    onClearList() {
      this.items = [];
    },

    // updateListQuantitiesMinMax() {
    //   if (this.items.length > 0) {
    //     const totalQuantity = this.items.reduce(
    //       (acc, item) => item.quantity,
    //       0
    //     );
    //     const minValues = this.items.map((item) => item.min);
    //     const maxValues = this.items.map((item) => item.max);
    //     const min = Math.min(...minValues);
    //     const max = Math.max(...maxValues);

    //     this.items.forEach((item) => {
    //       item.quantity = totalQuantity;
    //       item.min = min;
    //       item.max = max;
    //     });
    //   }
    // },

    resetForm() {
      this.inputForm = {
        // spare_id: null,
        min: 1,
        max: 1000,
        critical: 1,
        description: null,
        quantity: 1,
        // configures: [],
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
        description: null
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
            description: item.description
          };
        })
        .value();
    },

    getSpares() {
      const params = { no_pagination: true };
      rf.getRequest("AdminRequest")
        .getSpares(params)
        .then((res) => {
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
        })
        .catch((error) => {
          this.processErrors(error);
        });
    },

    async onClickSave() {
      if (this.items.length === 0) {
        this.showError('Must add at least 1 item!')
        return false;
      }
      this.resetError();
      await this.$validator.validateAll();

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
          expiry_date: this.inputForm.configures.length > 0 ? this.inputForm.configures[0].expiry_date : null,
          load_hydrostatic_test_due: this.inputForm.configures.length > 0 ? this.inputForm.configures[0].load_hydrostatic_test_due : null,
          calibration_due: this.inputForm.configures.length > 0 ? this.inputForm.configures[0].calibration_due : null,
          charge_time: this.inputForm.configures.length > 0 ? `${this.inputForm.configures[0].input_charge_time?.HH}:${this.inputForm.configures[0].input_charge_time?.HH}` : ''
        });
        this.resetForm();
      }
    },

    onClickSave() {
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
        // ...this.inputForm,
        formInput: this.items.map(i => ({ ...i })),
      }

      if (this.items.length === 0) {
        this.showError("The list cannot be empty!");
      } else {
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
      }
      // console.log(transData)

    },

    submitRequest(data) {
      const params = cloneDeep(data);
      return rf.getRequest("AdminRequest").updateBin(data);
    },

    onAddItem() {
      if (this.inputForm.spare_id) {

        const spare = this.spares.find((i) => i.id == this.inputForm.spare_id);
        const existingItem = this.items.find(
          (item) => item.spare_id === spare.id
        );
        if (existingItem) {
          this.showError("Duplicated! This item was added!");
        } else {

          this.inputForm.configures[0].description = this.inputForm.description;
          this.items.push({
            ...this.inputForm,
            id: this.data.id,
            spare_id: spare.id,
            name: spare.name,
            type: spare.type,
            charge_time: this.inputForm.configures.length > 0 ? `${this.inputForm.configures[0].input_charge_time?.HH}:${this.inputForm.configures[0].input_charge_time?.mm}` : ''
          });
          // this.updateListQuantitiesMinMax();
          this.resetForm()
          this.initConfigures()

        }

      } else {
        this.showError('Have to choose an item!')
      }

    },

    // updateListQuantitiesMinMax() {
    //   if (this.items.length > 0) {
    //     const totalQuantity = this.items.reduce(
    //       (acc, item) => item.quantity,
    //       0
    //     );
    //     const minValues = this.items.map((item) => item.min);
    //     const maxValues = this.items.map((item) => item.max);
    //     const min = Math.min(...minValues);
    //     const max = Math.max(...maxValues);

    //     this.items.forEach((item) => {
    //       item.quantity = totalQuantity;
    //       item.min = min;
    //       item.max = max;
    //     });
    //   }
    // },

    onClearList() {
      this.items = [];
    },
  },
};
</script>

<style lang="scss" scoped>
.input-form {
  width: 100%;
  .p-10px {
    padding: 10px;
  }
  .input_edit {
    width: 100%;
    padding: 10px;
    min-width: 100px;
  }
  .flex-center {
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .table-scroller {
    // overflow: unset;
    // max-height: unset;
    table tr td {
      word-break: unset;
      vertical-align: middle;
    }
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
