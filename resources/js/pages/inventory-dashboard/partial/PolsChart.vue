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
import randomColor from 'randomcolor'
import { chain, forEach, keys, toUpper, toLower, size, isEmpty, has, head, concat, cloneDeep } from 'lodash'
import moment from 'moment'
import Const from 'common/Const'
import StackedColumnChart from 'pages/vehicles/dashboard/partial/StackedColumnChart'

const TYPE = {
  GREATER_2MONTHS_TO_EXPIRY: { value: 'greater_2months_to_expired', name: 'More than 2 months to expired' },
  _2MONTHS_TO_EXPIRY: { value: '2months_to_expired', name: '1 – 2 months to expired' },
  _1MONTH_TO_EXPIRY: { value: '1month_to_expired', name: 'Expired / Less than 1 month' }
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
        legend: {
          enabled: false
        },
        chart: {
          height: 320
        },
      },
      colours: [],
      details: {}
    }
  },

  computed: {
    internalData () {
      return chain(this.data || [])
        .value()
    },

    categories () {
      return [
        toUpper(Const.POL_TYPE.OIL.value),
        toUpper(Const.POL_TYPE.GREASE.value),
        toUpper(Const.POL_TYPE.COOLANT.value),
        toUpper(Const.POL_TYPE.APPLICATION.value)
      ]
    },

    materialData () {
      return chain(this.internalData)
        .map(record => record.material_number)
        .uniq()
        .value()
    },

    availableColors () {
      return chain(this.colours)
        .filter(record => !record.used)
        .value()
    },

    defaultSerial () {
      this.generateColours(size(this.materialData))
      const mapColours = chain(this.colours)
        .filter(record => !!record.legend)
        .keyBy('legend')
        .value()

      const data = chain(this.materialData)
        .map((mpn, index) => {
          const colorObj = mapColours[mpn] ? mapColours[mpn] : head(this.availableColors)
          this.$set(colorObj, 'used', 1)
          this.$set(colorObj, 'legend', mpn)
          return { name: mpn, color: colorObj.color, data: [] }
        })
        .value()

      return data
    },

    serialData () {
      const defaultValues = cloneDeep(this.defaultSerial)

      forEach(defaultValues, record => {
        const legend = record.name

        const { total } = this.extractData(legend)
        record.data = total
        record.tooltipFormater = this.tooltipFormatter
      })

      return defaultValues
    },

    optionsChart() {
      this.options.chart.height = this.heightChart;
      return this.options
    }
  },

  mounted () {
    this.generateColours(10)
  },

  methods: {
    generateColours (blockSize = 10) {
      const availableSize = size(this.availableColors)
      if (availableSize >= blockSize) {
        return
      }

      const isNewColor = (newColor) => {
        return chain(this.colours)
          .filter(record => record.color === newColor)
          .isEmpty()
          .value()
      }

      let index = 0
      while (index < blockSize) {
        const color = randomColor()
        if (isNewColor(color)) {
          this.colours.push({ color, used: 0, legend: null })
          index++
        }
      }
    },

    extractData (mpn) {
      let total = []

      const data = chain(this.internalData)
        .filter(record => record.material_number === mpn)
        .groupBy('type')
        .value()

      forEach(this.categories, value => {
        const type = toLower(value)
        // total.push(size(data[type]))
        total.push(data[type] ? +(data[type][0].quantity_oh) : 0)
      })

      return { total }
    },

    tooltipFormatter (serialName, stackValue) {
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
            <td class="border border-secondary p-2 text-break text-center text-dark">${ record.description || '' }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ record.material_number }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ formatDate(record.expiry_date) }</td>
          </tr>
        `
      }

      const renderContent = () => {
        let tbody = ''

        chain(this.internalData)
          .filter(record => record.type === toLower(stackValue) && record.material_number === serialName)
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
            <th class="border border-secondary p-2 text-break text-center text-dark">Description</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">MPN</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">Expiry Date</th>
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
