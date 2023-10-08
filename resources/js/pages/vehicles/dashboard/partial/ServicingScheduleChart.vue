<template>
  <div class="operational-check">
    <div class="chart">
      <stacked-column-chart :categories="categories" :data="serialData" />
    </div>
  </div>
</template>
<style lang="scss" scoped>
.assets {
  .chart {
    position: relative;
  }
}
</style>
<script>
import { chain, forIn, keys, size, concat, toLower, toUpper, isEmpty } from 'lodash'
import moment from 'moment'
import Const from 'common/Const'
import StackedColumnChart from './StackedColumnChart'

const SCHEDULE = {
  _6_MONTH: { value: '6_month', name: '6 months' },
  _12_MONTH: { value: '12_month', name: '12 months' },
  _18_MONTH: { value: '18_month', name: '18 months' },
  _24_MONTH: { value: '24_month', name: '24 months' }
}

export default {
  components: {
    StackedColumnChart
  },

  props: {
    data: {
      type: Array,
      required: true
    }
  },

  data () {
    return {
      defaultValues: [
        {
          name: SCHEDULE._6_MONTH.name,
          color: '#EB6F15',
          data: []
        },
        {
          name: SCHEDULE._12_MONTH.name,
          color: '#6D6E71',
          data: []
        },
        {
          name: SCHEDULE._18_MONTH.name,
          color: '#669827',
          data: []
        },
        {
          name: SCHEDULE._24_MONTH.name,
          color: '#156BBB',
          data: []
        }
      ]
    }
  },

  computed: {
    internalData () {
      return chain(this.data || [])
        // .map(record => {
        //   const createdAt = moment.utc(record.created_at).local()
        //   return { ...record, month: createdAt.month() + 1 }
        // })
        .value()
    },

    categories () {
      return [...Array(12).keys()]
        .map(i => moment().month(i).format('MMM-YY'))
    },

    _6months () {
      const { data } = this.extractData('schedule_6_months')
      return data
    },

    _6monthsDetails () {
      const { details } = this.extractData('schedule_6_months')
      return details
    },

    _12months () {
      const { data } = this.extractData('schedule_12_months')
      return data
    },

    _12monthsDetails () {
      const { details } = this.extractData('schedule_12_months')
      return details
    },

    _18months () {
      const { data } = this.extractData('schedule_18_months')
      return data
    },

    _18monthsDetails () {
      const { details } = this.extractData('schedule_18_months')
      return details
    },

    _24months () {
      const { data } = this.extractData('schedule_24_months')
      return data
    },

    _24monthsDetails () {
      const { details } = this.extractData('schedule_24_months')
      return details
    },

    serialData () {
      this.defaultValues[0].data = this._6months
      this.defaultValues[0].tooltipFormater = this.tooltipFormatter

      this.defaultValues[1].data = this._12months
      this.defaultValues[1].tooltipFormater = this.tooltipFormatter

      this.defaultValues[2].data = this._18months
      this.defaultValues[2].tooltipFormater = this.tooltipFormatter

      this.defaultValues[3].data = this._24months
      this.defaultValues[3].tooltipFormater = this.tooltipFormatter

      return this.defaultValues
    }
  },

  methods: {
    extractData (attr) {
      const currentYear = moment().format('YYYY')

      const data = chain(this.internalData)
        .filter(record => !!record[attr])
        .filter(record => {
          const date = moment.utc(record[attr]).local()
          return currentYear === date.format('YYYY')
        })
        .map(record => {
          const date = moment.utc(record[attr]).local()
          return { ...record, month: date.month() + 1, month_name: date.format('MMM-YY') }
        })
        .groupBy('month')
        .value()

      const result = new Array(12).fill(0)
      let details = []
      chain(keys(data))
        .sortBy()
        .each(month => {
          details = concat(details, data[month] || [])
          result[month - 1] = size(data[month] || [])
        })
        .value()

      return { data: result, details }
    },

    tooltipFormatter (serialName, stackValue) {
      const getData = () => {
        switch (serialName) {
          case SCHEDULE._6_MONTH.name:
            return this._6monthsDetails
          case SCHEDULE._12_MONTH.name:
            return this._12monthsDetails
          case SCHEDULE._18_MONTH.name:
            return this._18monthsDetails
          case SCHEDULE._24_MONTH.name:
            return this._24monthsDetails
        }
        return []
      }

      const formatDate = (value) => {
        if (!value) {
          return value
        }
        return moment.utc(value, Const.DATE_PATTERN).local().format(Const.CLIENT_DATE_PATTERN)
      }

      const renderItem = (record, index) => {
        return `
          <tr>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ index + 1 }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ record.vehicle_num }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ toUpper(record.variant) }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ formatDate(record.schedule_24_months) }</td>
          </tr>
        `
      }

      const renderContent = () => {
        let tbody = ''

        chain(getData())
          .filter(record => record.month_name === stackValue)
          .forEach((record, index) => {
            tbody += renderItem(record, index)
          })
          .value()

        return !isEmpty(tbody)
          ? tbody
          : `
            <tr>
              <td colspan="4" class="border border-secondary p-2 text-break text-center text-dark">There is no data.</td>
            </tr>
          `
      }

      return `
        <table>
          <thead>
            <th class="border border-secondary p-2 text-break text-center text-dark">S/N</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">Veh#</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">Variant</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">24-mth Servicing Plan Date</th>
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
