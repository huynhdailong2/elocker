<template>
  <div class="page notifications" ref="infoBox">
    <div class="title-page">
      <div class="text-center">Inventory Dashboard</div>
    </div>
    <div class="content">
      <div class="item">
        <div class="info">
          <label class="label">No. of OH EUC Item</label>
          <span class="value" v-tooltip="{
              content: tooltipOnEUC(),
              placement: 'bottom-center',
              trigger: 'hover',
              autoHide: false
              }">{{ totalEuc }}</span>
        </div>
      </div>
      <div class="item">
        <div class="info">
          <label class="label">On Loan Item</label>
          <div class="value" :class="{'red': isWarningSparesOnLoanExpired}" v-tooltip="{
              content: tooltipOnLoans(),
              placement: 'bottom-center',
              trigger: 'hover',
              autoHide: false
              }">
            {{ totalSpareLoan }}
          </div>
        </div>
      </div>
      <div class="item">
        <spares-expiry-chart :data="spares" :heightChart="heightChart" />
      </div>
      <div class="item">
        <pols-chart :data="pols" :heightChart="heightChart" />
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.page {
  &.notifications {
    height: calc(100vh - 130px);
  }
  .content {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    width: 100%;
    //max-width: 1200px;
    gap: 15px;
    .item {
      //width: 500px;
      //margin: 30px;
      display: grid;
      //grid-column-gap: 40px;
      //grid-row-gap: 56px;
      // grid-template-columns: repeat(auto-fill,minmax(320px,1fr));
      position: relative;
      background-color: #212430;
      width: calc(1/2*100% - (1 - 1/2) * 15px);
      .info {
        padding: 0 30px;
        margin: 40px 0;
        font-size: 25px;
        .label {
          margin-right: 20px;
        }
        .value {
          cursor: pointer;
          display: inline-block;
          padding: 0 10px;
          &.red {
            color: red;
          }
        }
      }
    }
  }
}
</style>
<script>
import rf from 'requestfactory'
import moment from 'moment'
import {chain, size, filter, concat, isEmpty, uniqBy} from 'lodash'
import Const from 'common/Const'
import SparesExpiryChart from './partial/SparesExpiryChart'
import PolsChart from './partial/PolsChart'

export default {
  components: {
    SparesExpiryChart,
    PolsChart
  },

  data () {
    return {
      interval: null,
      totalEuc: 0,
      sparesLoan: [],
      spares: [],
      pols: [],
      heightChart: 0,
      listEucBox: [],
    }
  },

  computed: {
    totalSpareLoan () {
      return chain(this.sparesLoan)
        .filter((record) => {
          return record.quantity > 0
        })
        .size()
        .value()
    },

    isWarningSparesOnLoanExpired () {
      // if (!this.isAfter4Pm()) {
      //   return false
      // }
      return !chain(this.sparesLoan)
        .filter(record => !! record.expired_return_time)
        .isEmpty()
        .value()
    }
  },

  mounted () {
    this.fetchData()

    this.interval = setInterval(() => {
      this.fetchData()
    }, 3000)

    this.matchHeight()
  },

  beforeDestroy () {
    clearInterval(this.interval)
  },

  methods: {
    matchHeight () {
      this.heightChart = this.$refs.infoBox.clientHeight - 300;
    },

    fetchData () {
      this.getEucLists()
      this.getSparesReportByLoan()
      this.getPolManagements()
      this.getSparesAssignedBin()
    },

    getEucLists () {
      const params = { no_pagination: true }
      rf.getRequest('AdminRequest').getEucLists(params)
        .then(res => {
          let spares = []
          chain(res.data || [])
            .each(record => {
              spares = concat(spares, record.spares)
            })
            .value()

          this.totalEuc = chain(spares)
            .uniq('spare_id')
            .size()
            .value()

          this.listEucBox = res.data;
        })
    },

    getSparesReportByLoan () {
      rf.getRequest('SpareRequest').getSparesReportByLoan({ no_pagination: true })
        .then(res => {
          this.sparesLoan = chain(res.data || [])
            .each(record => {
              this.$set(record, 'expired_return_time', this.isExpiredReturnTime(record))
            })
            .value()
        })
    },

    getPolManagements () {
      const types = [
        Const.POL_TYPE.OIL.value,
        Const.POL_TYPE.GREASE.value,
        Const.POL_TYPE.COOLANT.value,
        Const.POL_TYPE.APPLICATION.value
      ]
      const params = {
        no_pagination: true,
        types
      }
      rf.getRequest('AdminRequest').getPolManagements(params)
        .then(res => {
          this.pols = res.data || []
        })
    },

    getSparesAssignedBin () {
      const types = [
        Const.ITEM_TYPE.CONSUMABLE.value,
        Const.ITEM_TYPE.PERISHABLE.value,
        Const.ITEM_TYPE.DURABLE.value,
        Const.ITEM_TYPE.AFES.value,
        Const.ITEM_TYPE.TORQUE_WRENCH.value,
        Const.ITEM_TYPE.LIFTING_EQUIPMENT.value,
      ]
      const params = {
        no_pagination: true,
        types
      }
      rf.getRequest('AdminRequest').getSparesAssignedBin(params)
        .then(res => {
          this.spares = res.data || []
        })
    },

    isAfter4Pm () {
      const milestoneTime = moment('4:00pm', 'h:mma')
      const current = moment(moment().format("h:mma"), 'h:mma')
      return milestoneTime.isBefore(current)
    },

    isExpiredReturnTime (record) {
      const limitHours = 16
      const issuedDatetime = moment.utc(record.issued_date, Const.DATETIME_PATTERN).local()
      const limitDatetime = issuedDatetime.clone().startOf('day').add(limitHours, 'hours')

      const now = moment()
      const isSameDay = now.diff(limitDatetime, 'days') === 0

      if (issuedDatetime > limitDatetime && isSameDay) {
        return false
      }

      return now > limitDatetime
    },

    tooltipOnEUC () {
      const renderItem = (record, eucBoxOrder, index) => {
        return `
          <tr>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ index + 1 }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ record.spare.material_no }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ record.spare.name }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ record.quantity_oh }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ eucBoxOrder }</td>
          </tr>
        `
      }

      const renderContent = () => {
        let tbody = ''

        let idx = 0;
        this.listEucBox.forEach((eucBox, index) => {
          eucBox.spares.forEach((record) => {
            tbody += renderItem(record, eucBox.order, idx)
            ++idx;
          });
        })

        return !isEmpty(tbody)
            ? tbody
            : `
            <tr>
              <td colspan="5" class="border border-secondary p-2 text-break text-center text-dark">There is no data.</td>
            </tr>
          `
      }

      return `
        <table>
          <thead>
            <th class="border border-secondary p-2 text-break text-center text-dark">S/N</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">MPN</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">Item Details</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">OH Qty</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">EUC Box</th>
          </thead>
          <tbody>
            ${ renderContent() }
          </tbody>
        </table>
      `
    },

    tooltipOnLoans () {
      const formatDate = (value) => {
        if (!value) {
          return 'N/A'
        }
        return moment.utc(value, Const.DATE_PATTERN).local().format(Const.CLIENT_DATE_PATTERN)
      }

      const renderItem = (record, index) => {
        return `
          <tr>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ index + 1 }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ record.spare_name }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ record.material_no }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ formatDate(record.expiry_date) }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ record.issued_to }</td>
          </tr>
        `
      }

      const renderContent = () => {
        let tbody = ''

        chain(this.sparesLoan || [])
          .filter(record => {
            return this.isAfter4Pm() ? !!record.expired_return_time : true
          })
          // Filter item not full return
          .filter(record => {
            return record.quantity > 0
          })
          .forEach((record, index) => {
            tbody += renderItem(record, index)
          })
          .value()

        return !isEmpty(tbody)
          ? tbody
          : `
            <tr>
              <td colspan="5" class="border border-secondary p-2 text-break text-center text-dark">There is no data.</td>
            </tr>
          `
      }

      return `
        <table>
          <thead>
            <th class="border border-secondary p-2 text-break text-center text-dark">S/N</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">Item Details</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">MPN</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">Expiry/Cal Due Date</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">User</th>
          </thead>
          <tbody>
            ${ renderContent() }
          </tbody>
        </table>
      `
    }
  }
}
</script>
