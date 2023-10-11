<template>
  <div class="bin-configures">
    <div class="table-scroller mt-3 mb-3" v-if="!!spare">
      <table>
        <thead>
          <th class="no">S/N</th>
          <th v-if="spare.has_batch_no">Batch Number</th>
          <th v-if="spare.has_serial_no">Serial Number</th>
          <th v-if="spare.has_charge_time">Charge Time</th>
          <th v-if="spare.has_calibration_due">Calibration Due/Inspection</th>
          <th v-if="spare.has_load_hydrostatic_test_due">
            Load/Hydrostatic Test Due
          </th>
          <th v-if="spare.has_expiry_date">Expiry Date</th>
        </thead>
        <tbody>
          <tr v-for="(item, index) in data">
            <td>{{ index + 1 }}</td>
            <td v-if="spare.has_batch_no">
              <input
                type="text"
                class="input"
                :class="{ error: errors.has(`${item.scope}.batch_no`) }"
                name="batch_no"
                data-vv-as="batch no"
                placeholder="Batch Number"
                :data-vv-scope="`${item.scope}`"
                v-model.trim="item.batch_no"
                v-validate="`${batchNoRule}`"
              />
              <span
                class="invalid-feedback"
                v-if="errors.has(`${item.scope}.batch_no`)"
              >
                {{ errors.first(`${item.scope}.batch_no`) }}
              </span>
            </td>
            <td v-if="spare.has_serial_no">
              <input
                type="text"
                class="input"
                :class="{ error: errors.has(`${item.scope}.serial_no`) }"
                name="serial_no"
                data-vv-as="serial no"
                placeholder="Serial Number"
                :data-vv-scope="`${item.scope}`"
                v-model.trim="item.serial_no"
                v-validate="`${serialNoRule}`"
              />
              <span
                class="invalid-feedback"
                v-if="errors.has(`${item.scope}.serial_no`)"
              >
                {{ errors.first(`${item.scope}.serial_no`) }}
              </span>
            </td>
            <td v-if="spare.has_charge_time">
              <div class="choose-time">
                <label class="checkbox-container">
                  <input type="checkbox" v-model="item.has_charge_time" />
                  <span class="checkmark"></span>
                </label>

                <vue-timepicker
                  v-model="item.input_charge_time"
                  name="charge_time"
                  v-validate="'required'"
                  :data-vv-scope="`${item.scope}`"
                  :class="{ error: errors.has(`${item.scope}.charge_time`) }"
                  data-vv-as="charge time"
                  v-if="item.has_charge_time"
                />

                <!-- <datepicker
                  format="dd/MM/yyyy"
                  input-class="form-control date-selector"
                  v-model="item.charge_time"
                  :disabled-dates="{to: yesterday}"
                  name="charge_time"
                  v-validate="'required'"
                  :data-vv-scope="`${item.scope}`"
                  :class="{'error': errors.has(`${item.scope}.charge_time`)}"
                  data-vv-as="charge time"
                  v-if="item.has_charge_time" /> -->
              </div>
              <span
                class="invalid-feedback"
                v-if="errors.has(`${item.scope}.charge_time`)"
              >
                {{ errors.first(`${item.scope}.charge_time`) }}
              </span>
            </td>
            <td v-if="spare.has_calibration_due">
              <div class="choose-date">
                <label class="checkbox-container">
                  <input type="checkbox" v-model="item.has_calibration_due" />
                  <span class="checkmark"></span>
                </label>
                <datepicker
                  format="dd/MM/yyyy"
                  input-class="form-control date-selector"
                  v-model="item.calibration_due"
                  :disabled-dates="{ to: yesterday }"
                  name="calibration_due"
                  v-validate="'required'"
                  :data-vv-scope="`${item.scope}`"
                  :class="{
                    error: errors.has(`${item.scope}.calibration_due`),
                  }"
                  data-vv-as="calibration due"
                  v-if="item.has_calibration_due"
                />
              </div>
              <span
                class="invalid-feedback"
                v-if="errors.has(`${item.scope}.calibration_due`)"
              >
                {{ errors.first(`${item.scope}.calibration_due`) }}
              </span>
            </td>
            <td v-if="spare.has_load_hydrostatic_test_due">
              <div class="choose-date">
                <label class="checkbox-container">
                  <input
                    type="checkbox"
                    v-model="item.has_load_hydrostatic_test_due"
                  />
                  <span class="checkmark"></span>
                </label>
                <datepicker
                  format="dd/MM/yyyy"
                  input-class="form-control date-selector"
                  v-model="item.load_hydrostatic_test_due"
                  :disabled-dates="{ to: yesterday }"
                  name="load_hydrostatic_test_due"
                  v-validate="'required'"
                  :data-vv-scope="`${item.scope}`"
                  :class="{
                    error: errors.has(
                      `${item.scope}.load_hydrostatic_test_due`
                    ),
                  }"
                  data-vv-as="calibration due"
                  v-if="item.has_load_hydrostatic_test_due"
                />
              </div>
              <span
                class="invalid-feedback"
                v-if="errors.has(`${item.scope}.load_hydrostatic_test_due`)"
              >
                {{ errors.first(`${item.scope}.load_hydrostatic_test_due`) }}
              </span>
            </td>
            <td v-if="spare.has_expiry_date">
              <div class="choose-date expiry">
                <label class="checkbox-container">
                  <input type="checkbox" v-model="item.has_expiry_date" />
                  <span class="checkmark"></span>
                </label>
                <datepicker
                  format="dd/MM/yyyy"
                  input-class="form-control date-selector"
                  v-model="item.expiry_date"
                  :disabled-dates="{ to: yesterday }"
                  name="expiry_date"
                  v-validate="'required'"
                  :data-vv-scope="`${item.scope}`"
                  :class="{ error: errors.has(`${item.scope}.expiry_date`) }"
                  data-vv-as="expiry date"
                  v-if="item.has_expiry_date"
                />
              </div>
              <span
                class="invalid-feedback"
                v-if="errors.has(`${item.scope}.expiry_date`)"
              >
                {{ errors.first(`${item.scope}.expiry_date`) }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.bin-configures {
  .table-scroller {
    // min-height: 250px;
    table {
      // margin-bottom: 80px;
      tr {
        td {
          min-width: 135px;
          vertical-align: middle;
          .choose-date {
            display: flex;
            align-items: center;
            justify-content: center;
            ::v-deep .vdp-datepicker {
              input {
                min-width: 100px;
                max-width: 120px;
              }
            }
            &.expiry {
              ::v-deep .vdp-datepicker {
                &__calendar {
                  right: 0;
                }
              }
            }
          }
          ::v-deep .time-picker {
            max-width: 80px;
            input {
              width: 100%;
            }
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
              top: -145px;
            }
          }
        }
        &:first-child {
          // ::v-deep .vdp-datepicker {
          //   &__calendar {
          //     top: -290px;
          //   }
          // }
          ::v-deep .time-picker {
            .dropdown {
              top: calc(2.2em + 2px);
            }
          }
        }
      }
    }
  }
}
</style>
<script>
import moment from "moment";
import { time } from "lodash";
import Datepicker from "vuejs-datepicker";
import Utils from "common/Utils";
import Const from "common/Const";
import VueTimepicker from "vue2-timepicker";
import "vue2-timepicker/dist/VueTimepicker.css";

export default {
  components: {
    Datepicker,
    VueTimepicker,
  },

  props: {
    spare: {
      type: Object,
      default: () => {},
    },

    data: {
      type: Array,
      required: true,
    },
  },

  computed: {
    yesterday() {
      return moment()
        .subtract(1, "days")
        .toDate();
    },

    batchNoRule() {
      switch (this.spare?.type) {
        case Const.ITEM_TYPE.CONSUMABLE.value:
          return "";
      }

      return "required";
    },

    serialNoRule() {
      switch (this.spare?.type) {
        case Const.ITEM_TYPE.CONSUMABLE.value:
          return "";
      }

      return "required";
    },
  },

  methods: {
    async validateData() {
      await Utils.asyncForEach(this.data, async (item, index) => {
        this.errors.clear(item.scope);
        await this.$validator.validate(`${item.scope}.*`);
      });

      if (this.errors.any()) {
        return false;
      }

      return true;
    },
  },
};
</script>
