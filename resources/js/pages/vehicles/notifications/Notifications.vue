<template>
  <div class="page notifications">
    <div class="title-page">
      <div class="text-center">Notifications</div>
    </div>
    <div class="block content">
      <div class="schedule-container">
        <schedule ref="schedule" :mail-type="mailType" />
      </div>

      <div class="text-right mt-2">
        <button class="btn btn-primary" @click.stop="onClickSave">Save and Exist</button>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.content {
  width: 100%;
  .schedule-container {
    border: 1px dotted #363A47;
    border-radius: 10px;
    padding: 5px;
  }
}
</style>
<script>
import rf from 'requestfactory'
import moment from 'moment'
import Const from 'common/Const'
import Schedule from 'pages/physical/partials/Schedule'

const SCHEDULE_TYPE = {
  WEEKLY: 'weekly',
  MONTHLY: 'monthly'
}

export default {
  components: {
    Schedule
  },

  data () {
    return {
    }
  },

  computed: {
    mailType () {
      return Const.RECEIVER_EMAIL_TYPE.VEHICLE
    }
  },

  methods: {
    onClickSave () {
      this.$refs.schedule.saveSenderEmail()
      this.$refs.schedule.saveReceiverEmail()
      this.$refs.schedule.saveSchedule()

      this.$router.push('/inventory')
    }
  }
}
</script>
