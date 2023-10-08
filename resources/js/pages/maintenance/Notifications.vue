<template>
  <div class="page notifications">
    <div class="title-page">
      <div class="text-center">Notifications</div>
    </div>
    <nav class="nav">
      <a class="nav-link"
         :class="{'active': selected === TABS.INVENTORY}"
         href="javascript:void(0)"
         @click.stop="selected = TABS.INVENTORY">Inventory
      </a>
      <a class="nav-link"
         :class="{'active': selected === TABS.ALERT}"
         href="javascript:void(0)"
         @click.stop="selected = TABS.ALERT">Alerts
      </a>
    </nav>
    <div class="block content">
      <template v-if="selected === TABS.INVENTORY">
        <div class="schedule-container">
          <schedule ref="schedule" :mail-type="mailType" :sendReport="sendReport">
            <template slot="beforeSender">
              <div class="col-4 item head">Range to send</div>
              <div class="col-8 item form-input">
                <div class="row">
                  <div class="col-3">
                    <input type="radio" id="range_week" name="range_schedule" value="week" class="radio-range"  v-model="rangeReportTnx">
                    <label for="range_week">Week</label>
                  </div>

                  <div class="col-3">
                    <input type="radio" id="range_month" name="range_schedule" value="month" class="radio-range" v-model="rangeReportTnx">
                    <label for="range_month">Month</label>
                  </div>
                </div>

              </div>
            </template>

          </schedule>
        </div>

        <div class="text-right mt-2">
          <button class="btn btn-primary" @click.stop="onClickSave">Save and Exist</button>
        </div>
      </template>

      <template v-if="selected === TABS.ALERT">
        <div class="schedule">
          <schedule ref="scheduleAlert" :mail-type="mailTypeAlert" hide-sender />
        </div>
      </template>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.content {
  width: 100%;
  .schedule-container {
    border: 3px dotted #363A47;
    border-radius: 10px;
    padding: 5px;
  }
  .radio-range {
    width: auto !important;
    margin-top: 13px;
    margin-left: 10px;
  }
}
</style>
<script>
import rf from 'requestfactory'
import moment from 'moment'
import Const from 'common/Const'
import Schedule from 'pages/physical/partials/Schedule'
import {debounce} from "lodash";

const SCHEDULE_TYPE = {
  WEEKLY: 'weekly',
  MONTHLY: 'monthly'
}

const TABS = {
  INVENTORY: 'inventory',
  ALERT: 'alert',
}

export default {
  components: {
    Schedule
  },

  data () {
    return {
      TABS,
      selected: TABS.INVENTORY,
      rangeReportTnx: 'week'
    }
  },

  watch: {
    selected: debounce(function (newVal) {
      if(newVal == TABS.INVENTORY) {
        this.$refs.schedule.getScheduleSettings();
      }
      if(newVal == TABS.ALERT) {
        this.$refs.scheduleAlert.getScheduleSettings();
      }
    }, 0),

    rangeReportTnx: debounce(function(newVal, oldVal) {
      this.saveRangeReportTnx(newVal);
    }, 0),
  },

  computed: {
    mailType () {
      return Const.RECEIVER_EMAIL_TYPE.MAINTENANCE
    },
    mailTypeAlert() {
      return Const.RECEIVER_EMAIL_TYPE.ALERT_WEIGHING_SYSTEM
    }
  },

  created() {
    this.getRangeReportTnx()
  },

  methods: {
    onClickSave () {
      this.$refs.schedule.saveSenderEmail()
      this.$refs.schedule.saveReceiverEmail()
      this.$refs.schedule.saveSchedule()

      this.$router.push('/inventory')
    },

    sendReport() {
      rf.getRequest('SpareRequest').sendTnxReportNotification().then(res => {
        this.showSuccess('Send report successfully!');
      });
    },

    saveRangeReportTnx(val) {
      const params = {
        'key': Const.KEY_RANGE_REPORT_TNX,
        'value': val
      }
      rf.getRequest('SettingRequest').saveByKey(params).then(res => {
        this.showSuccess('Save successfully');
      })
    },

    getRangeReportTnx() {
      const params = {
        'key': Const.KEY_RANGE_REPORT_TNX,
      }
      rf.getRequest('SettingRequest').getByKey(params).then(res => {
        const data = res.data
        if(data) {
          this.rangeReportTnx = data.value
        }
      })
    }
  }
}
</script>
