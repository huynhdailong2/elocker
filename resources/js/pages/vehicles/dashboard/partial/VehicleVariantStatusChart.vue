<template>
  <div class="vehicle-variant-status">
    <div class="chart">
      <stacked-column-chart :categories="categories" :data="serialData" />
    </div>
  </div>
</template>
<style lang="scss" scoped>
.vehicle-variant-status {
  .chart {
    position: relative;
  }
}
</style>
<script>
import { chain, forEach, toUpper, toLower, size, isEmpty } from 'lodash'
import moment from 'moment'
import Const from 'common/Const'
import StackedColumnChart from './StackedColumnChart'


const STATUS = {
  FAILED: 'failed',
  PASSED: 'passed'
}

const SERIAL = {
  UNSERVICEABLE: { value: 'unserviceable', name: 'Unserviceable' },
  SERVICEABLE: { value: 'serviceable', name: 'Serviceable' }
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
          name: 'Unserviceable',
          color: '#ed7d31',
          data: [],
          tooltip: null
        },
        {
          name: 'Serviceable',
          color: '#4472c4',
          data: [],
          tooltip: null
        }
      ]
    }
  },

  computed: {
    internalData () {
      return chain(this.data || [])
        .map(item => {
          item.variant = toLower(item.variant)
          return item
        })
        .orderBy(['variant'], ['asc'])
        .value()
    },

    categories () {
      return chain(this.internalData)
        .map(item => toUpper(item.variant))
        .uniq()
        .value()
    },

    serialUnserviceable () {
      return this.extractData(true)
    },

    serialServiceable () {
      return this.extractData(false)
    },

    serialData () {
      this.defaultValues[0].data = this.serialUnserviceable
      this.defaultValues[0].tooltipFormater = this.tooltipFormatter

      this.defaultValues[1].data = this.serialServiceable
      this.defaultValues[1].tooltipFormater = this.tooltipFormatter

      return this.defaultValues
    }
  },

  methods: {
    extractData (unserviceable) {
      const data = chain(this.internalData)
        .groupBy('variant')
        .value()

      const result = []
      forEach(this.categories, value => {
        const variant = toLower(value)
        // const variant = value

        const total = chain(data[variant] || [])
          .filter(item => unserviceable ? !!item.unserviceable : !item.unserviceable)
          .size()
          .value()

        result.push(total)
      })

      return result
    },

    tooltipFormatter (serialName, stackValue) {
      const getCondition = () => {
        switch (serialName) {
          case SERIAL.UNSERVICEABLE.name:
            return true
          case SERIAL.SERVICEABLE.name:
            return false
        }
        return false
      }

      const renderItem = (record, index) => {
        return `
          <tr>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ index + 1 }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ record.vehicle_num }</td>
            <td class="border border-secondary p-2 text-break text-center text-dark">${ toUpper(record.unit) }</td>
          </tr>
        `
      }

      const renderContent = () => {
        let tbody = ''

        chain(this.internalData)
          .filter(record => record.variant === toLower(stackValue) && record.unserviceable === getCondition())
          .forEach((record, index) => {
            tbody += renderItem(record, index)
          })
          .value()

        return !isEmpty(tbody)
          ? tbody
          : `
            <tr>
              <td colspan="3" class="border border-secondary p-2 text-break text-center text-dark">There is no data.</td>
            </tr>
          `
      }

      return `
        <table>
          <thead>
            <th class="border border-secondary p-2 text-break text-center text-dark">S/N</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">Veh#</th>
            <th class="border border-secondary p-2 text-break text-center text-dark">Unit</th>
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
