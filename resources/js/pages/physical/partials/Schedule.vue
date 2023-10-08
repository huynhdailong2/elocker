<template>
  <div id="schedule">
    <div class="headingline">
      <span class="title">Schedule</span>
      <!-- <div class="btn btn-primary">With OH Qty</div>
      <div class="btn btn-second">Without OH Qty</div> -->
      <button class="btn btn-primary" v-if="visibleSendReport" @click.stop="onClickSendReportNow">Send Report Now</button>
    </div>

    <div class="content">
      <div class="weekly">
        <div>
          <input type="radio" id="weekly" name="schedule" :value="SCHEDULE_TYPE.WEEKLY" v-model="scheduleType">
          <label for="weekly">{{ SCHEDULE_TYPE.WEEKLY }}</label>
        </div>
        <div class="options" v-if="isWeekly">
          <label class="item" v-for="item in weekly">
            <p class="name">{{ item.name }}</p>
            <input type="checkbox" name="" class="checkbox" v-model="item.checked">
          </label>

          <div class="item time">
            <p class="name">Time</p>
            <vue-timepicker v-model="inputTimeForWeekly" />
          </div>
        </div>
      </div>

      <div class="clearfix"></div>

      <div class="monthly">
        <div>
          <input type="radio" id="monthly" name="schedule" :value="SCHEDULE_TYPE.MONTHLY" v-model="scheduleType">
          <label for="monthly">{{ SCHEDULE_TYPE.MONTHLY }}</label>
        </div>
        <template v-if="isMonthly">
          <div>
            <div class="options">
              <div class="text">Day of the Month</div>
              <div>
                <label class="item" v-for="(item, index) in monthly">
                  <div >{{ item.day }}</div>
                  <input type="checkbox" class="checkbox" v-model="item.checked">
                </label>
              </div>
            </div>
            <div class="time">
              <p class="name">Time</p>
              <vue-timepicker v-model="inputTimeForMonthly" />
            </div>
          </div>
        </template>
      </div>
      <div class="clearfix"></div>
      <div class="invalid-feedback">{{ errors.first('schedule') }}</div>

      <div class="settings">
        <div class="table">
          <div class="row">
            <slot name="beforeSender" />
          </div>
          <template v-if="!hideSender">
            <div class="row">
              <div class="col-4 item head">Sender's Email</div>
              <div class="col-8 item form-input">
                <input
                  type="text"
                  class="input_g"
                  :class="{'error': errors.has('settings.sender_email')}"
                  name="sender_email"
                  data-vv-as="sender's email"
                  data-vv-scope="settings"
                  placeholder="Sender's Email"
                  v-model.trim="settings.sender_email"
                  v-validate="'required|email'" >
                <span class="invalid-feedback" v-if="errors.has(`settings.sender_email`)">
                  {{ errors.first(`row-${props.index + 1}.sender_email`) }}
                </span>
              </div>
            </div>
            <div class="row">
              <div class="col-4 item head">Sender's Password</div>
              <div class="col-8 item form-input">
                <input
                  type="password"
                  class="input_g"
                  :class="{'error': errors.has('settings.sender_password')}"
                  name="sender_password"
                  data-vv-as="sender's password"
                  data-vv-scope="settings"
                  placeholder="Sender's Password"
                  v-model.trim="settings.sender_password"
                  v-validate="'required'" >
                <span class="invalid-feedback" v-if="errors.has(`settings.sender_password`)">
                  {{ errors.first(`row-${props.index + 1}.sender_password`) }}
                </span>
              </div>
            </div>
          </template>

          <div class="row">
            <div class="col-4 item head">Receiver's Email</div>
            <div class="col-8 item form-input">
              <input
                type="text"
                class="input_g"
                :class="{'error': errors.has('settings.receiver_email')}"
                name="receiver_email"
                data-vv-as="sender email"
                data-vv-scope="settings"
                placeholder="Ex: receiver1@gmail.com,receiver2@gmail.com,..."
                v-model.trim="settings.receiver_email"
                v-validate="'required|email'" >
              <span class="invalid-feedback" v-if="errors.has(`settings.receiver_email`)">
                {{ errors.first(`row-${props.index + 1}.receiver_email`) }}
              </span>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>
<script>
  import Const from 'common/Const';
  import moment from 'moment';
  import rf from 'requestfactory'
  import VueTimepicker from 'vue2-timepicker';
  import 'vue2-timepicker/dist/VueTimepicker.css';
  import { debounce, isEmpty, chain, times, padStart, head, isEqual, includes } from 'lodash'

  const SCHEDULE_TYPE = {
    WEEKLY: 'weekly',
    MONTHLY: 'monthly'
  }

  export default {
    props: {
      mailType: {
        type: String,
        default: Const.RECEIVER_EMAIL_TYPE.CYCLE_COUNT
      },

      hideSender: {
        type: Boolean,
        default: false
      },

      sendReport: null,
    },

    components: {
      VueTimepicker
    },

    data() {
      return {
        settings: {},
        isLoadedPage: false,
        scheduleType: SCHEDULE_TYPE.WEEKLY,
        weekly: Const.WEEKLY,
        monthly: [],
        inputTimeForWeekly: {
          HH: '00',
          mm: '00'
        },
        inputTimeForMonthly: {
          HH: '00',
          mm: '00'
        },
        SCHEDULE_TYPE
      }
    },

    watch: {
      'settings.sender_email': debounce(function (newVal, oldValue) {
        if (isEqual(newVal, oldValue)) {
          return
        }
        this.saveSenderEmail()
      }, 400),

      'settings.sender_password': debounce(function (newVal, oldValue) {
        if (isEqual(newVal, oldValue)) {
          return
        }
        this.saveSenderEmail()
      }, 400),

      'settings.receiver_email': debounce(function (newVal, oldValue) {
        if (isEqual(newVal, oldValue)) {
          return
        }
        this.saveReceiverEmail()
      }, 400),

      'inputTimeForWeekly': debounce(function (newVal, oldValue) {
        if (isEqual(newVal, oldValue)) {
          return
        }
        this.saveSchedule()
      }, 400),

      'inputTimeForMonthly': debounce(function (newVal, oldValue) {
        if (isEqual(newVal, oldValue)) {
          return
        }
        this.saveSchedule()
      }, 400),

      weekly: {
        deep: true,
        handler: debounce(function (newVal) {
          this.saveSchedule()
        }, 400)
      },

      monthly: {
        deep: true,
        handler: debounce(function (newVal) {
          this.saveSchedule()
        }, 400)
      }
    },

    computed: {
      isWeekly() {
        return this.scheduleType === SCHEDULE_TYPE.WEEKLY;
      },

      isMonthly() {
        return this.scheduleType === SCHEDULE_TYPE.MONTHLY;
      },

      isCycleCount () {
        return this.mailType === Const.RECEIVER_EMAIL_TYPE.CYCLE_COUNT
      },

      visibleSendReport () {
        const types = [Const.RECEIVER_EMAIL_TYPE.MAINTENANCE]
        return includes(types, this.mailType)
      }
    },

    created() {
      this.getScheduleSettings()
      this.initMonthLy()
    },

    methods: {
      initMonthLy() {
        const monthDate = moment().startOf('month');

        times(monthDate.daysInMonth(), value => {
          this.monthly.push({day: value + 1, checked: false })
        })
      },

      getScheduleSettings () {
        rf.getRequest('SettingRequest').getScheduleSettings({ type: this.mailType }).then(res => {
          const data = res.data

          this.settings = {
            sender_email: data.sender_email,
            sender_password: data.sender_password,
            receiver_email: data.receiver_email
          }

          switch (this.mailType) {
            case Const.RECEIVER_EMAIL_TYPE.CYCLE_COUNT:
              this.scheduleType = data.schedule_cycle_count;
              break;
            case Const.RECEIVER_EMAIL_TYPE.INVENTORY_COUNT:
              this.scheduleType = data.schedule_inventory_count;
              break;
            case Const.RECEIVER_EMAIL_TYPE.ALERT_WEIGHING_SYSTEM:
              this.scheduleType = data.schedule_alert_weighing_system;
              break;
            default:
              this.scheduleType = SCHEDULE_TYPE.WEEKLY;
              break;
          }
          // this.scheduleType = (this.isCycleCount ? data.schedule_cycle_count : data.schedule_inventory_count) || SCHEDULE_TYPE.WEEKLY
          this.buildSchedule(data)

          this.isLoadedPage = true
        })
      },

      buildSchedule (data) {
        const updateValues = (schedule, value, property) => {
          const mapValue = chain(value)
            .mapKeys(item => item[property])
            .value()

          chain(schedule)
            .each(item => {
              this.$set(item, 'checked', !!mapValue[item[property]])
            })
            .value()
        }

        const formatTime = (time) => {
          const date = new moment.utc(`10-10-2020 ${time}`, 'DD-MM-YYYY HH:mm');

          const hour    = padStart(date.hour(), 2, '0');
          const minute  = padStart(date.minute(), 2, '0');

          return {HH: `${hour}`, mm: `${minute}`};
        }

        const firstWeekly = head(data.weekly)
        this.inputTimeForWeekly = firstWeekly ? formatTime(firstWeekly.time): { HH: '00', mm: '00' }

        const firstMonthly = head(data.monthly)
        this.inputTimeForMonthly = firstMonthly ? formatTime(firstMonthly.time): { HH: '00', mm: '00' }

        updateValues(this.weekly, data.weekly, 'value')
        updateValues(this.monthly, data.monthly, 'day')
      },

      saveSenderEmail () {
        if (!this.isLoadedPage || this.hideSender || isEmpty(this.settings.sender_email) || isEmpty(this.settings.sender_password)) {
          return
        }

        rf.getRequest('SettingRequest').saveSenderEmail(this.settings)
      },

      saveReceiverEmail () {
        if (!this.isLoadedPage || isEmpty(this.settings.receiver_email)) {
          return
        }

        rf.getRequest('SettingRequest').saveReceiverEmail({ value: this.settings.receiver_email, type: this.mailType })
      },

      saveSchedule() {
        const toTime = (obj) => {
          return `${obj.HH}:${obj.mm}`
        }

        const getScheduling = (data, time) => {
          return chain(data)
            .filter(item => !!item.checked)
            .map(item => {
              return {
                ...item,
                type: this.mailType,
                time: toTime(time),
                offset: (new Date()).getTimezoneOffset() }
            })
            .value()
        }

        const schedule = this.isWeekly
            ? getScheduling(this.weekly, this.inputTimeForWeekly)
            : getScheduling(this.monthly, this.inputTimeForMonthly)

        if (!this.isLoadedPage || isEmpty(schedule)) {
          return
        }

        const params = {
          report_type: this.scheduleType,
          schedule
        }

        switch (this.mailType) {
          case Const.RECEIVER_EMAIL_TYPE.CYCLE_COUNT:
            return rf.getRequest('SettingRequest').saveCycleCountSchedule(params)
            break;
          case Const.RECEIVER_EMAIL_TYPE.ALERT_WEIGHING_SYSTEM:
            return rf.getRequest('SettingRequest').saveAlertWeighingSystemSchedule(params)
            break;
          default:
            return rf.getRequest('SettingRequest').saveInventoryCountSchedule(params)
            break;
        }
        // if (this.mailType === Const.RECEIVER_EMAIL_TYPE.CYCLE_COUNT) {
        //   return rf.getRequest('SettingRequest').saveCycleCountSchedule(params)
        // }
        // return rf.getRequest('SettingRequest').saveInventoryCountSchedule(params)
      },

      onClickSendReportNow () {
        if(typeof this.sendReport == 'function') {
          this.sendReport()
        }
      }
    }
  }
</script>
<style lang="scss" scoped>

  #schedule {
    margin-bottom: 30px;
    overflow: auto;

    .clearfix {
      content: "";
      clear: both;
      display: table;
    }

    .headingline {
      .title {
        text-transform: uppercase;
        font-size: 16px;
        margin-right: 20px;
      }
      .btn-report {
        border: none;
        outline: 0;
        font-weight: 700;
        font-size: 14px;
        line-height: 16px;
        padding: 10px;
        border-radius: 8px;
        background-color: #f90;
        margin-left: 20px;
      }
    }
    .content {
      margin-left: 10px;
      min-width: 800px;
      min-height: 280px;
      .weekly {
        margin-top: 10px;
        font-size: 15px;
        label {
          cursor: pointer;
        }
        text-transform: uppercase;
        .options {
          margin-left: 20px;
          .item {
            float: left;
            text-align: center;
            border: 1px solid #989090;
            border-left: none;
            // margin-left: 2px;
            &:first-child {
              border-left: 1px solid #989090;
            }
            .name {
              border-bottom: 1px solid #989090;
              text-transform: capitalize;
              padding: 10px;
              background-color: #363A47;
              color: #fff;
            }
            .checkbox {
              height: 15px;
              width: 15px;
              margin-bottom: 17px;
            }
          }
          .time {
            // margin-left: 5px;
            ::v-deep .time-picker {
              width: auto;
              .display-time {
                margin-top: -5px;
                margin-bottom: 4px;
                margin-left: 5px;
                margin-right: 5px;
                width: 100px;
                height: 34.5px;
              }
              .clear-btn {
                padding-right: 15px;
                margin-top: -12px;
              }
            }
          }
        }
      }
      .monthly {
        margin-top: 10px;
        margin-top: 10px;
        font-size: 15px;
        label {
          cursor: pointer;
        }
        text-transform: uppercase;
        .options {
          float: left;
          width: 600px;
          margin-left: 20px;
          .text {
            background-color: #363A47;
            color: #fff;
            padding: 8px;
            width: 556.4px;
            margin-left: 2px;
          }
          .item {
            float: left;
            padding: 10px;
            margin-bottom: 2px;
            margin-left: 2px;
            height: 60px;
            width: 60px;
            position: relative;
            // background-color: #11131D;
            border: 1px solid #989090;

            div {
              font-size: 18px;
              margin-top: 15px;
            }
            input {
              position: absolute;
              right: 6px;
              top: 6px;
              height: 15px;
              width: 15px;
              cursor: pointer;
            }
          }
        }
        .time {
          float: left;
          background-color: #363A47;
          color: #fff;
          width: 120px;
          .name {
            margin: 0;
            padding: 8px;
          }
          ::v-deep .time-picker {
            width: 120px;
            .display-time {
              width: 120px;
            }
            .clear-btn {
              padding-right: 15px;
            }
          }
        }
      }
      .settings {
        margin-top: 30px;
        .table {
          margin: auto;
          width: 70%;
          .row {
            .item {
              border: 1px solid #363A47;
              background-color: #212430;
              color: #fff;
              font-size: 15px;
              &.head {
                line-height: 40px;
              }
              &.form-input {
                padding: 0;
              }
              input {
                width: 100%;
                padding: 10px;
                border: none;
              }
            }
          }
        }
      }
    }
  }
</style>
