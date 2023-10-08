<template>
  <div class="serviceability">
    <div class="chart">
      <stacked-column-chart :options="optionsChart" :categories="categories" :data="serialData" />
    </div>
  </div>
</template>
<style lang="scss" scoped>
.serviceability {
  .chart {
    position: relative;
  }
}
</style>
<script>
import { chain, forEach, keys, toUpper, toLower, size, isEmpty, has, head, concat } from 'lodash'
import moment from 'moment'
import Const from 'common/Const'
import StackedColumnChart from 'pages/vehicles/dashboard/partial/StackedColumnChart'

const TYPE = {
  GREATER_2MONTHS_TO_EXPIRY: { value: 'greater_2months_to_expired', name: 'More than 2 months to expired' },
  _2MONTHS_TO_EXPIRY: { value: '2months_to_expired', name: '1 – 2 months to Expired or Due' },
  _1MONTH_TO_EXPIRY: { value: '1month_to_expired', name: 'Expired or Due / Less than 1 month' }
}

export default {
  components: {
    StackedColumnChart
  },

  props: {
    data: {
      type: Array,
      required: true
    },
    heightChart: Number
  },

  data () {
    return {
      options: {
        chart: {
          height: 320
        },
      },
      defaultValues: [
        {
          name: TYPE.GREATER_2MONTHS_TO_EXPIRY.name,
          color: '#92D050',
          data: []
        },
        {
          name: TYPE._2MONTHS_TO_EXPIRY.name,
          color: '#FFC011',
          data: []
        },
        {
          name: TYPE._1MONTH_TO_EXPIRY.name,
          color: '#FF0000',
          data: []
        }
      ],
    }
  },

  computed: {
    internalData () {
      const getDate = (record) => {
        const expiryDate = record.expiry_date
          ? moment.utc(record.expiry_date, Const.DATE_PATTERN).local()
          : moment().add(3, 'months')

        const calibrationDueDate = record.calibration_due
          ? moment.utc(record.calibration_due, Const.DATE_PATTERN).local()
          : moment().add(3, 'months')

        const loadHydrostaticTestDueDate = record.load_hydrostatic_test_due
          ? moment.utc(record.load_hydrostatic_test_due, Const.DATE_PATTERN).local()
          : moment().add(3, 'months')

        let date = expiryDate
        if (date > calibrationDueDate) {
          date = calibrationDueDate
        } else if (date > loadHydrostaticTestDueDate) {
          date = loadHydrostaticTestDueDate
        }

        return date
      }

      return chain(this.data || [])
        .map(record => {
          const headItem = head(record.configures)
          if (headItem) {
            record.expiry_date = headItem.expiry_date
            record.calibration_due = headItem.calibration_due
            record.load_hydrostatic_test_due = headItem.load_hydrostatic_test_due

            const expiryDate = getDate(headItem)
            record.expiryDateLocal = expiryDate
          }
          record.location = `${record.shelf_name} - ${record.row} - ${record.bin}`
          return record
        })
        .orderBy(['expiryDateLocal'], ['desc'])
        .value()
    },

    categories () {
      return [
        toUpper(Const.ITEM_TYPE.CONSUMABLE.name),
        toUpper(Const.ITEM_TYPE.PERISHABLE.name),
        toUpper(Const.ITEM_TYPE.DURABLE.name),
        toUpper(Const.ITEM_TYPE.AFES.name),
        toUpper(Const.ITEM_TYPE.LIFTING_EQUIPMENT.name),
        toUpper(Const.ITEM_TYPE.TORQUE_WRENCH.name),
      ]
    },

    greater2Months () {
      const { data } = this.extractData(TYPE.GREATER_2MONTHS_TO_EXPIRY)
      return data
    },

    greater2MonthsDetails () {
      const { details } = this.extractData(TYPE.GREATER_2MONTHS_TO_EXPIRY)
      return details
    },

    _2months () {
      const { data } = this.extractData(TYPE._2MONTHS_TO_EXPIRY)
      return data
    },

    _2MonthsDetails () {
      const { details } = this.extractData(TYPE._2MONTHS_TO_EXPIRY)
      return details
    },

    _1month () {
      const { data } = this.extractData(TYPE._1MONTH_TO_EXPIRY)
      return data
    },

    _1monthDetails () {
      const { details } = this.extractData(TYPE._1MONTH_TO_EXPIRY)
      return details
    },

    serialData () {
      this.defaultValues[0].data = this.greater2Months
      this.defaultValues[0].tooltipFormater = this.tooltipFormatter

      this.defaultValues[1].data = this._2months
      this.defaultValues[1].tooltipFormater = this.tooltipFormatter

      this.defaultValues[2].data = this._1month
      this.defaultValues[2].tooltipFormater = this.tooltipFormatter
      return this.defaultValues
    },

    optionsChart() {
      this.options.chart.height = this.heightChart;
      return this.options
    }
  },

  methods: {
    getTypeValue (name) {
      return chain(Object.values(Const.ITEM_TYPE))
          .filter(record => toUpper(record.name) === name)
          .map(record => record.value)
          .head()
          .value()
    },

    extractData (serialType) {
      const comparator = (record) => {
        const today = moment()
        const diff = record.expiryDateLocal < today ? 0 : record.expiryDateLocal.diff(today, 'months', true)
        switch (serialType) {
          case TYPE.GREATER_2MONTHS_TO_EXPIRY:
            return diff > 2
          case TYPE._2MONTHS_TO_EXPIRY:
            return diff >= 1 && diff <= 2
          case TYPE._1MONTH_TO_EXPIRY:
          default:
            return diff < 1
        }
      }
      const data = chain(this.internalData)
        .filter(comparator)
        .groupBy('type')
        .value()

      const result = []
      let details = []

      forEach(this.categories, value => {
        const type = this.getTypeValue(value)

        details = concat(details, data[type] || [])
        const total = size(data[type] || [])
        result.push(total)
      })

      return { data: result, details }
    },

    tooltipFormatter (serialName, stackValue) {
      const getData = () => {
        switch (serialName) {
          case TYPE.GREATER_2MONTHS_TO_EXPIRY.name:
            return this.greater2MonthsDetails
          case TYPE._2MONTHS_TO_EXPIRY.name:
            return this._2MonthsDetails
          case TYPE._1MONTH_TO_EXPIRY.name:
            return this._1monthDetails
        }
        return []
      }

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
            <td class="border border-secondary p-2 text-break text-center text-dark">${ record.name }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ record.material_no }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ formatDate(record.expiry_date) }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ formatDate(record.calibration_due) }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ formatDate(record.load_hydrostatic_test_due) }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ record.location }</td>
          </tr>
        `
      }

      const renderContent = () => {
        let tbody = ''

        chain(getData())
          .filter(record => record.type === toLower(this.getTypeValue(stackValue)))
          .forEach((record, index) => {
            tbody += renderItem(record, index)
          })
          .value()

        return !isEmpty(tbody)
          ? tbody
          : `
            <tr>
              <td colspan="7" class="border border-secondary p-2 text-break text-center text-dark">There is no data.</td>
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
            <th class="border border-secondary p-2 text-break text-center text-dark">Calibration Due</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">Load/Hydrostatic Test Due</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">Location</th>
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
