<template>
  <div id="dashboard" class="page">
    <div style="overflow: auto;">
        <div class="title-page">Weighing System</div>

        <div class="captions">
          <div class="sts-good">Good</div>
          <div class="sts-low">Low</div>
          <div class="sts-expired">Critical/Expired</div>
          <div class="sts-unassigned">Unassigned</div>
        </div>
    </div>

    <div class="list-shelves">
      <span>Site Test > Room 1 > </span>
      <select class="input" v-model="shelf_id" style="width: auto">
        <option value="">-- Choose --</option>
        <option :value="SELECTED_ALL">All</option>
        <option :value="item.id" v-for="(item, index) in shelves" :key="index">{{ item.name }}</option>
      </select>
    </div>

    <div class="bin-dashboard">
      <div class="bin-list">
        <fieldset v-for="(shelf) in dataShelves" :key="shelf.id">
          <legend>{{ getShelfName(shelf.id) }}</legend>
          <table>
            <tbody>
            <tr v-for="chunkBin in getChunks(shelf.data)">
              <td v-for="bin in chunkBin">
                <div class="bin" :class="[getClassBinStatus(bin)]" @click="onClickShowUpdateModal(bin)" style="cursor:pointer;">
                  <div class="bin-id" v-if="bin.id">
                    <span><strong>ID:</strong> {{ bin.deviceId }}</span>
                    <span><strong>Part No.:</strong> {{ bin.deviceDescription ? bin.deviceDescription.partNumber: '' }}</span>
                    <span><strong>Name:</strong> {{ bin.deviceDescription ? bin.deviceDescription.name: '' }}</span>
                  </div>
                  <div class="bin-number">{{ bin.quantity }}</div>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </fieldset>
      </div>
    </div>

    <update-bin-modal />
  </div>
</template>

<script>
import rf from 'requestfactory';
import Const from 'common/Const';
import { chain, size, chunk } from 'lodash'
import UpdateBinModal from './UpdateBinModal'
import mqtt from 'mqtt'
const SELECTED_ALL = 'ALL'

export default {
  computed: {

  },

  components: {
    UpdateBinModal
  },

  data() {
    return {
      SELECTED_ALL,
      dataShelves: [],
      column: 5,
      limit: 35,
      maxRealRow: 0,
      rows: [],

      interval: null,

      shelf_id: '',
      shelves: [],

      connection: {
        host: process.env.MIX_MQTT_HOST,
        port: process.env.MIX_MQTT_PORT,
        endpoint: process.env.MIX_MQTT_ENDPOINT,
        clean: true, // Reserved session
        connectTimeout: 4000, // Timeout
        reconnectPeriod: 4000, // Reconnection interval
        // Certification Information
        clientId: '',
        username: '',
        password: '',
      },
      subscriptions: [
        'device/computed/#',
        'device/update/#',
        'device/remove/#',
      ],
      client: {
        connected: false,
      },
    }
  },

  watch: {
    shelf_id(value, oldValue) {
      if(value) {
        this.doUnSubscribe()
        this.doSubscribe()

        this.getBinsRequests();
      } else {
        this.dataShelves = []
      }
    }
  },

  methods: {
    getChunks(data){
      return chunk(data, 5);
    },

    getShelfName(shelfId) {
      let shelf = chain(this.shelves)
        .filter(item => {
          return item.id == shelfId
        })
        .value()

      return shelf[0]['name']
    },

    createConnectionMqtt() {
      const { host, port, endpoint, ...options } = this.connection
      const connectUrl = `ws://${host}:${port}${endpoint}`

      try {
        this.client = mqtt.connect(connectUrl, options)
      } catch (error) {
        console.log('mqtt.connect error', error)
      }
      this.client.on('connect', () => {
        console.log('Connection succeeded!')
      })
      this.client.on('error', error => {
        console.log('Connection failed', error)
      })
      this.client.on('message', (topic, payload) => {
        const regexDeviceComputed = `device/computed/`
        if(topic.includes(regexDeviceComputed)) {
          const payloadJson = JSON.parse(payload.toString())
          console.log('Received message regexDeviceComputed', topic, payloadJson)

          const shelfId = topic.split(regexDeviceComputed).pop()
          this.processTopicDeviceComputed(shelfId, payloadJson)
        }

        const regexDeviceUpdate = `device/update/`;
        if(topic.includes(regexDeviceUpdate)) {
          const payloadJson = JSON.parse(payload.toString())
          console.log('regexDeviceUpdate', topic, payloadJson)

          const shelfId = topic.split(regexDeviceUpdate).pop()
          this.processTopicDeviceUpdate(shelfId, payloadJson)
        }

        const regexDeviceRemove = `device/remove/`;
        if(topic.includes(regexDeviceRemove)) {
          const payloadJson = JSON.parse(payload.toString())
          console.log('regexDeviceRemove', topic, payloadJson)

          const shelfId = topic.split(regexDeviceRemove).pop()
          this.processTopicDeviceDelete(shelfId, payloadJson)
        }
      })
    },

    doSubscribe() {
      const subscribe = this.getTopicsSubscribe()
      this.client.subscribe(subscribe, {qos:0}, (error, res) => {
        if (error) {
          console.log('Subscribe to topics error', error)
          return
        }
        console.log('Subscribe to topics res', res)
      });
    },

    doUnSubscribe() {
      const subscribe = this.getTopicsSubscribe()
      this.client.unsubscribe(subscribe, error => {
        if (error) {
          console.log('Unsubscribe error', error)
        }

        console.log('Unsubscribe to topics res')
      })
    },

    processTopicDeviceComputed(shelfId, data) {
      const deviceList = this.convertKeyByDeviceId(data)

      let dataUpdate = [];
      this.dataShelves.forEach((shelf) => {
        if(shelf.id == shelfId) {
          let result = chain(shelf.data)
              .map(item => {
                const binData = deviceList[item.deviceId]
                if(binData) {
                  item.weight = binData.weight
                  item.quantity = binData.quantity
                }

                return item
              })
              .value()

          dataUpdate.push({
            id: shelfId,
            data: result,
          })
        } else {
          dataUpdate.push(shelf)
        }
      })

      this.dataShelves = dataUpdate;
    },

    processTopicDeviceUpdate(shelfId, data) {
      const deviceList = this.convertKeyByDeviceId(data['deviceList']);

      let dataUpdate = [];
      this.dataShelves.forEach((shelf) => {
        if(shelf.id == shelfId) {
          let result = chain(shelf.data)
              .map(item => {
                const binData = deviceList[item.deviceId]
                if(binData) {
                  item = binData
                }

                return item
              })
              .value()

          dataUpdate.push({
            id: shelfId,
            data: result,
          })
        } else {
          dataUpdate.push(shelf)
        }
      })

      this.dataShelves = dataUpdate;
    },

    processTopicDeviceDelete(shelfId, data) {
      let dataUpdate = [];
      this.dataShelves.forEach((shelf) => {
        if(shelf.id == shelfId) {
          let result = chain(shelf.data)
            .filter(item => {
              return item.deviceId != data.deviceId
            })
            .value()

          dataUpdate.push({
            id: shelfId,
            data: result,
          })
        } else {
          dataUpdate.push(shelf)
        }
      })

      this.dataShelves = dataUpdate;
    },

    convertKeyByDeviceId(data) {
      const dataConvert = {};
      data.forEach(item => {
        dataConvert[item.deviceId] = item;
      })

      return dataConvert;
    },

    getTopicsSubscribe() {
      return this.subscriptions.map(item => {
        if(this.shelf_id != SELECTED_ALL) {
          return item.replace('#', this.shelf_id)
        }

        return item;
      })
    },

    getBinsRequests(params) {
      if(this.shelf_id == SELECTED_ALL) {
        this.dataShelves = []

        this.shelves.forEach(item => {
          rf.getRequest('WeightRequest').getBinsOfShelf(item.id).then(result => {
            this.dataShelves.push({
              id: item.id,
              data: result.data,
            })
          });
        })
      }
      else {
        rf.getRequest('WeightRequest').getBinsOfShelf(this.shelf_id).then(result => {
          this.dataShelves = []
          this.dataShelves.push({
            id: this.shelf_id,
            data: result.data,
          })
        });
      }
    },

    getClassBinStatus(bin) {
      const currentQuantity = bin.quantity
      const low = bin.quantityMinThreshold
      const crit = bin.quantityCritThreshold

      if(currentQuantity <= low && currentQuantity > crit) {
        return 'sts-low'
      }

      if(currentQuantity <= crit) {
        return 'sts-expired'
      }

      if(currentQuantity > low) {
        return 'sts-good'
      }

      return 'sts-unassigned';
    },

    getSites() {
      rf.getRequest('WeightRequest').getSites()
        .then(res => {
          const site = res.data.sites[0]
          const room = site['rooms'][0]
          let shelves = [];
          chain(room.shelves)
            .each(shelf => {
              shelves.push({
                id: shelf.id,
                name: shelf.name,
              })
            })
            .value()

          this.shelves = shelves
        })
    },

    onClickShowUpdateModal(bin) {
      if(bin.id) {
        this.$modal.show('update-bin-modal', { bin: bin })
      }
    }
  },
  mounted() {
    this.createConnectionMqtt();

    this.getSites();
  },
  beforeDestroy() {
    this.doUnSubscribe();
  }
}
</script>
<style lang="scss" scoped>

@mixin overdue-aws () {
  background: linear-gradient(104.28deg, #7A3D00 1.4%, #C58749 100%);
}

@mixin pending-fulfillment () {
  background: linear-gradient(104.04deg, #927A00 0%, #E8CD43 100%);
}

@mixin spares-fulfilled () {
  background: linear-gradient(291.63deg, #42FA80 0%, #0FA95F 100%);
}

@mixin pending-yt3 () {
  background: linear-gradient(104.04deg, #950062 0%, #E262B7 100%);
}

@mixin pending-yt5 () {
  background: linear-gradient(104.04deg, #006D7B 0%, #36BFD1 100%);
}

@mixin ttc-item () {
  background: linear-gradient(291.63deg, #ED1D24 0%, #8A0021 100%);
}

@mixin unassign () {
  background: linear-gradient(104.04deg, #392470 0%, #A893E1 100%);
}

@mixin overdue-awl () {
  background: linear-gradient(343.2deg, #EA9960 0%, #DA5900 100%);
}

@mixin issued () {
  background: linear-gradient(343.2deg, #6B6B6B 0%, #000000 100%);
}

.page {
  .title-page {
    float: left;
  }
  .captions {
    float: right;
    padding: 30px 0;
    display: flex;
    justify-content: flex-end;
    margin-bottom: 10px;
    div {
      padding: 10px 20px;
    }
  }
  .sts-good {
    background: linear-gradient(270deg, #669827 0%, #385810 100%);
  }
  .sts-low {
    background: linear-gradient(270deg, #E1A713 0%, #9B7513 100%);
  }
  .sts-expired {
    background: linear-gradient(270deg, #FF3131 0%, #861313 100%);
  }
  .sts-unassigned {
    background: #212430;
  }
}

#dashboard {
  padding: 40px 45px 30px 45px;
  overflow: auto;

  .list-shelves {
    margin-bottom: 10px;
  }

  .bin-dashboard {
    margin-left: -8px;
    margin-right: -8px;

    .bin-list {

      fieldset {
        border-bottom: 1px solid #E3342F;
        margin-bottom: 20px;
        padding-bottom: 20px;

        &:last-child{
          border-bottom: none;
          margin-bottom: 0;
          padding-bottom: 0;
        }

        legend {
          font-size: 18px;
        }
      }

      .bin {
        display: flex;
        width: calc(100% - 16px);
        min-width: 200px;
        max-width: 260px;
        height: 80px;
        margin: 0px 8px 16px 8px;
        padding: 12px 12px;
        color: #FFFFFF;
        background: #b0b0b0;
        box-sizing: border-box;
        border-radius: 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 600;
        .bin-id {
          color: #FFFFFF;
          font-size: 16px;
          line-height: 20px;

          span {
            display: block;
          }
        }
        &.overdue-aws {
          @include overdue-aws();
        }
        &.pending-fulfillment {
          @include pending-fulfillment();
        }
        &.spares-fulfilled {
          @include spares-fulfilled();
        }
        &.ttc-item {
          @include ttc-item();
        }
        &.issued {
          @include issued();
        }
        &.unassign {
          @include unassign();
        }
        &.overdue-awl {
          @include overdue-awl();
        }
        &.pending-yt3 {
          @include pending-yt3();
        }
        &.pending-yt5 {
          @include pending-yt5();
        }
        &.empty {
          font-weight: bold;
          font-size: 16px;
          line-height: 20px;
        }

        &.sts-good {
          background: linear-gradient(270deg, #669827 0%, #385810 100%);
        }

        &.sts-low {
          background: linear-gradient(270deg, #E1A713 0%, #9B7513 100%);
        }

        &.sts-expired {
          background: linear-gradient(270deg, #FF3131 0%, #861313 100%);
        }

        &.sts-unassigned {
          background: #212430;
        }

        .bin-number {
          margin-left: auto;
          max-width: 35px;
          font-size: 22px;
          color: #FFFFFF;
        }

        .percent {
          width: 56px;
          height: 56px;
          float: right;
          background: #FFFFFF;
          font-weight: 600;
          font-size: 14px;
          line-height: 20px;
          text-align: center;
          color: #20222B;
          border-radius: 50%;
          padding: 19px 0px;
          position: relative;

          span {
            position: relative;
            z-index: 3;
          }

          .md-progress-spinner {
            position: absolute;
            z-index: 2;
            top: 2px;
            left: 2px;

            ::v-deep .md-progress-spinner-draw {
              width: 52px !important;
              height: 52px !important;

              .md-progress-spinner-circle {
                stroke-width: 5px !important;
              }
            }

            &.md-theme-default {

              ::v-deep .md-progress-spinner-draw {

                .md-progress-spinner-circle {
                  stroke: #ED1D24 !important;
                }
              }

              &.max_percent {

                ::v-deep .md-progress-spinner-draw {

                  .md-progress-spinner-circle {
                    stroke: #21C462 !important;
                  }
                }
              }
            }
          }

          &:after {
            content: "";
            display: block;
            width: calc(100% - 4px);
            height: calc(100% - 4px);
            position: absolute;
            top: 2px;
            left: 2px;
            background-color: #FFFFFF;
            border-radius: 50%;
            border: 4px solid #ECECEC;
          }
        }
      }
    }
    .bin-note {
      display: flex;
      flex-wrap: wrap;
      width: 100%;

      .note {
        width: calc(100% / 3);
        padding-right: 10px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        font-size: 16px;
        line-height: 20px;
        color: #20222B;

        .block-color {
          height: 24px;
          width: 24px;
          display: inline-block;
          margin-right: 8px;
          border-radius: 50%;

          &.overdue-aws {
            @include overdue-aws();
          }
          &.pending-fulfillment {
            @include pending-fulfillment();
          }
          &.spares-fulfilled {
            @include spares-fulfilled();
          }
          &.ttc-item {
            @include ttc-item();
          }
          &.issued {
            @include issued();
          }
          &.unassign {
            @include unassign();
            color: #fff;
          }
          &.overdue-awl {
            @include overdue-awl();
          }
          &.pending-yt3 {
            @include pending-yt3();
          }
          &.pending-yt5 {
            @include pending-yt5();
          }
        }
      }
    }
  }
}
</style>
<style lang="scss">
#dashboard {
  td {
    border: none;
    padding: 0;
  }
}

</style>
