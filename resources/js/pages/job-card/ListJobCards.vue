<template>
  <div class="job-card">
    <div class="head">
      <div class="action">
        <button class="btn-primary" @click.stop="onClickAddNew">Add Service/WO</button>
      </div>
      <div class="search-form">
        <input
            type="text"
            placeholder="Service/WO#"
            class="input"
            v-model="inputSearch">
      </div>
    </div>

    <div class="table-content">
      <data-table2 :getData="getJobCards"
                   :limit="10"
                   :column="6"
                   :widthTable="'100%'"
                   @DataTable:finish="onDataTableFinished"
                   ref="datatable">
        <th>S/N</th>
        <th>Service/WO#</th>
<!--        <th>WO#</th>-->
        <th>Veh#</th>
        <th>Veh Type</th>
        <th>Platform</th>
        <th>Action</th>
        <template slot="body" slot-scope="props">
          <tr>
            <td>
              <div class="text">{{ props.realIndex }}</div>
            </td>
            <td>
              <template v-if="props.item.editable">
                <input
                    type="text"
                    class="input_g"
                    :class="{'error': errors.has(`row-${props.index + 1}.card_num`)}"
                    name="card_num"
                    data-vv-as="service/mo"
                    :data-vv-scope="`row-${props.index + 1}`"
                    placeholder="Service/WO"
                    v-model.trim="props.item.formInput.card_num"
                    v-validate="'required|numeric|min_value:1'">
                <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.card_num`)">
                      {{ errors.first(`row-${props.index + 1}.card_num`) }}
                    </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.card_num }}</div>
              </template>
            </td>
<!--            <td>-->
<!--              <template v-if="props.item.editable">-->
<!--                <input-->
<!--                    type="text"-->
<!--                    class="input_g"-->
<!--                    :class="{'error': errors.has(`row-${props.index + 1}.wo`)}"-->
<!--                    name="wo"-->
<!--                    :data-vv-scope="`row-${props.index + 1}`"-->
<!--                    placeholder="WO#"-->
<!--                    v-model.trim="props.item.formInput.wo"-->
<!--                    v-validate="'required|numeric|min_value:1'">-->
<!--                <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.wo`)">-->
<!--                      {{ errors.first(`row-${props.index + 1}.wo`) }}-->
<!--                    </span>-->
<!--              </template>-->
<!--              <template v-else>-->
<!--                <div class="text">{{ props.item.wo }}</div>-->
<!--              </template>-->
<!--            </td>-->
            <td>
              <template v-if="props.item.editable">
                <select
                    :data-vv-scope="`row-${props.index + 1}`"
                    :class="{'error': errors.has(`row-${props.index + 1}.vehicle_id`)}"
                    v-model="props.item.formInput.vehicle_id"
                    name="vehicle_id"
                    data-vv-as="vehicle"
                    v-validate="'required'">
                  <option :value="item.id" v-for="(item, index) in vehicles" :key="index">{{
                      item.vehicle_num
                    }}
                  </option>
                </select>
                <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.vehicle_id`)">
                      {{ errors.first(`row-${props.index + 1}.vehicle_id`) }}
                    </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.vehicle_num }}</div>
              </template>
            </td>
            <td>
              <div class="text">{{ getVehicleType(props.item.formInput, props.index) }}</div>
            </td>
            <td>
              <template v-if="props.item.editable">
                <input
                    type="text"
                    class="input_g"
                    :class="{'error': errors.has(`row-${props.index + 1}.platform`)}"
                    name="platform"
                    :data-vv-scope="`row-${props.index + 1}`"
                    placeholder="Platform"
                    v-model.trim="props.item.formInput.platform"
                    v-validate="'required'">
                <span class="invalid-feedback" v-if="errors.has(`row-${props.index + 1}.platform`)">
                      {{ errors.first(`row-${props.index + 1}.platform`) }}
                    </span>
              </template>
              <template v-else>
                <div class="text">{{ props.item.platform }}</div>
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
<!--                <img src="/images/icons/icon-trash.svg" width="22px"-->
<!--                     @click.stop="onClickDelete(props.item, props.index)">-->
                <img src="/images/icons/icon-return-white.svg" width="22px"
                     @click.stop="onClickClosed(props.item, props.index)">
              </template>
            </td>
          </tr>
        </template>
      </data-table2>

      <InsertVehicleModal @done="onAddVehicleDoneHandler" />
    </div>
  </div>
</template>
<style lang="scss" scoped>
$heightCell: 40px;
$widthCell: 100%;

.job-card {
  //.action {
  //  margin: 20px 0 10px 0;
  //}
  .head {
    display: flex;
    justify-content: space-between;
    margin: 20px 0 10px 0;
    .search-form {
      width: 300px;
    }
  }

  .table-content {
    ::v-deep .box_table {
      th {
        text-align: center;
      }

      td {
        padding: 0px;

        input {
          height: $heightCell;
          width: $widthCell;
        }

        select {
          height: $heightCell;
          min-width: $widthCell;
          width: 100%;
        }

        &.action {
          line-height: 38px;

          img {
            margin: 0 0 0 10px;
            cursor: pointer;

            &:last-child {
              margin-right: 10px;
            }
          }
        }

        .text {
          vertical-align: middle;
          text-align: center;
          line-height: 38px;
        }
      }
    }
  }
}
</style>
<script>
import rf from 'requestfactory'
import {chain, cloneDeep, debounce} from 'lodash'
import {io} from 'socket.io-client'
import InsertVehicleModal from "./InsertVehicleModal";

export default {
  data() {
    return {
      data: [],
      vehicles: [],
      inputSearch: null,
      isShowModalAddVehicle: false,
    }
  },

  components: {
    InsertVehicleModal
  },

  watch: {
    inputSearch: debounce(function () {
      this.$nextTick(() => {
        this.$refs.datatable.refresh()
      })
    }, 300)
  },

  mounted() {
    this.getVehicles()

    if(process.env.MIX_SOCKET_ENDPOINT) {
      this.connectSocket()
    }
  },

  methods: {
    connectSocket() {
      const socket = io(process.env.MIX_SOCKET_ENDPOINT);
      socket.on( 'connect', async function() {
        console.log('connect');
      });
      socket.on('disconnect', function() {
        console.log('disconnect');
      });
      socket.on('connect_failed', function() {
        console.log('connect_failed');
      });
      socket.on('error', function() {
        console.log('error');
      });
      socket.on('scanner-data', (data) => {
        console.log('Receive event scanner-data from barcode with data: ' + data);
        this.processReceiveDataFromBarcode(data)
      })
    },

    processReceiveDataFromBarcode(barcodeValue) {
      rf.getRequest('AdminRequest').scanBarcode({barcode: barcodeValue})
        .then(res => {
          const data = res.data;
          const currentStep = data['currentStep'];
          console.log('currentStep', data, currentStep)
          switch (currentStep) {
            case 1:
              this.processStepOne(data)
              break;
            case 2:
              this.processStepTwo(data)
              break;
            case 3:
              this.processStepThree(data)
              break;
          }
        })
    },

    processStepOne(data) {
      console.log('processStepOne', data)
      this.processBarcodeFillDataForRow(data)
    },

    processStepTwo(data) {
      console.log('processStepTwo', data)
      // Show popup create vehicle
      if(!data['status']) {
        if(!this.isShowModalAddVehicle) {
          this.isShowModalAddVehicle = true;
          this.$modal.show('insert-vehicle-modal', { vehicle_num: data.barcodeValue})
        }
      } else {
        this.processBarcodeFillDataForRow(data)
      }
    },

    processStepThree(data) {
      console.log('processStepThree', data)
      this.processBarcodeFillDataForRow(data)

      setTimeout(() => {
        this.data.forEach((row, index) => {
          if(row.addFromBarcode == true) {
            this.onClickSave(row, index);

            rf.getRequest('AdminRequest').finishedScanBarcode()
              .then(res => {})

            this.$refs.datatable.refresh();

            return false;
          }
        });
      }, 0);
    },

    processBarcodeFillDataForRow(data) {
      console.log('processBarcodeFillDataForRow', data)
      // Check add row from barcode exists
      const rowFromBarcode = chain(this.data)
        .filter(row => {
          return row.addFromBarcode == true
        })
        .first()
        .value()
      if(! rowFromBarcode) {
        this.onClickAddNew(true);
      }

      const barcodeData = data.barcodeData;
      const currentDataBarcode = barcodeData['SCAN_BARCODE_DATA'];
      chain(this.data)
        .each(row => {
          if(row.formInput.addFromBarcode == true) {
            let cloneData = cloneDeep(row);
            cloneData.card_num = currentDataBarcode['SCAN_BARCODE_STEP_ONE'];
            cloneData.wo = currentDataBarcode['SCAN_BARCODE_STEP_ONE'];
            cloneData.vehicle_id = currentDataBarcode['SCAN_BARCODE_STEP_TWO'];
            cloneData.platform = currentDataBarcode['SCAN_BARCODE_STEP_THREE'];

            this.$set(row, 'formInput', cloneDeep(cloneData))
          }
        })
        .value()
    },

    getJobCards(params) {
      params = {
        ...params,
        search_key: this.inputSearch
      }
      return rf.getRequest('AdminRequest').getJobCards(params)
    },

    getVehicles() {
      const params = {
        no_pagination: true
      }
      rf.getRequest('VehicleRequest').getVehicles(params)
          .then(res => {
            this.vehicles = res.data || []
          })
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

    onClickAddNew(addFromBarcode = false) {
      const newItem = {
        editable: true,
        card_num: null,
        wo: null,
        vehicle_id: null,
        vehicle_type: null,
        platform: null,
        addFromBarcode: addFromBarcode
      }
      this.data.push({
        ...newItem,
        formInput: cloneDeep(newItem)
      })
    },

    onClickCancel(record, index) {
      this.$set(record, 'editable', false)
      this.$set(record, 'formInput', cloneDeep(record))

      const scope = `row-${index + 1}.*`
      this.errors.clear(scope)
    },

    onClickDelete(record, index) {
      const refresh = () => {
        this.$refs.datatable.refresh()
      }

      if (!record.id) {
        refresh()
      }

      rf.getRequest('AdminRequest').deleteJobCard({id: record.id})
          .then(res => {
            this.showSuccess('Successfully!');
            refresh()
          })
          .catch(error => {
            this.processErrors(error)
          })
    },

    onClickClosed(record, index) {
      const refresh = () => {
        this.$refs.datatable.refreshCurrentPage()
      }

      if (!record.id) {
        refresh()
      }

      const callback = () => {
        rf.getRequest('AdminRequest').closedJobCard({id: record.id})
          .then(res => {
            this.showSuccess('Successfully!');
            refresh()
          })
          .catch(error => {
            this.processErrors(error)
          })
      }

      this.confirmAction({ callback })
    },

    async onClickSave(record, index) {
      const scope = `row-${index + 1}.*`
      this.errors.clear(scope)

      await this.$validator.validate(scope)

      if (this.errors.any()) {
        return
      }

      this.submitRequest(record.formInput)
          .then(res => {
            this.$set(record, 'editable', false)
            this.$set(record, 'addFromBarcode', false)
            const data = res.data

            this.$set(record, 'card_num', data.card_num)
            this.$set(record, 'wo', data.wo)
            this.$set(record, 'vehicle_id', data.vehicle_id)
            this.$set(record, 'vehicle_num', data.vehicle_num)
            this.$set(record, 'platform', data.platform)

            this.showSuccess('Successfully!');
          })
          .catch(error => {
            this.processErrors(error, `row-${index + 1}`)
          })
    },

    submitRequest(data) {
      if (data.id) {
        return rf.getRequest('AdminRequest').updateJobCard(data)
      }
      return rf.getRequest('AdminRequest').createJobCard(data)
    },

    getVehicleType(record, index) {
      if (!record.vehicle_id) {
        return null
      }

      const vehicle = chain(this.vehicles)
          .find(item => item.id === record.vehicle_id)
          .value()

      return vehicle ? vehicle.vehicle_type_name : null
    },

    onAddVehicleDoneHandler(data) {
      this.getVehicles()

      this.processReceiveDataFromBarcode(data.vehicle_num);
    }
  }
}
</script>
