<template>
  <div class="serviceability">
    <div class="chart">
      <stacked-column-chart :categories="categories" :data="serialData" />
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
          name: SERIAL.UNSERVICEABLE.name,
          color: '#EE5012',
          data: []
        },
        {
          name: SERIAL.SERVICEABLE.name,
          color: '#92D050',
          data: []
        }
      ]
    }
  },

  computed: {
    internalData () {
      return chain(this.data || [])
        .map(record => {
          record.unserviceable = !!record.unserviceable
          return record
        })
        .orderBy(['unit'], ['asc'])
        .value()
    },

    categories () {
      return chain(this.internalData)
        .map(item => toUpper(item.unit))
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
        .groupBy('unit')
        .value()

      const result = []
      forEach(this.categories, value => {
        const unit = toLower(value)

        const total = chain(data[unit] || [])
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
            <td class="border border-secondary p-2 text-break text-center text-dark">${ toUpper(record.variant) }</td>
          </tr>
        `
      }

      const renderContent = () => {
        let tbody = ''

        chain(this.internalData)
          .filter(record => record.unit === toLower(stackValue) && record.unserviceable === getCondition())
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
            <th class="border border-secondary p-2 text-break text-center text-dark">Variant</th>
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
