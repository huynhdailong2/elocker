<template>
  <div class="stacked-chart">
    <div class="draw">
      <highcharts :options="chartOptions" :ref="refChart" />
    </div>
  </div>
</template>
<style lang="scss" scoped>
.stacked-chart {
  .draw {
    ::v-deep .highcharts-container {
      .highcharts-credits {
        display: none;
      }
      .highcharts-tooltip > span {
         max-height: 100px;
         overflow-y: auto;
         pointer-events: initial;
      }
    }
  }
}
</style>
<script>
import Highcharts from 'highcharts'
import { Chart } from 'highcharts-vue'
import { chain } from 'lodash'

export default {
  components: {
    highcharts: Chart
  },

  props: {
    options: {
      type: Object,
      default: () => {}
    },

    categories: {
      type: Array,
      required: true
    },

    data: {
      type: Array,
      required: true
    }
  },

  data () {
    return {
      refChart: `chart-${Date.now()}`,
      chartOptions: {
        chart: {
          height: 270,
          type: 'column',
          backgroundColor: '#212430'
        },
        title: {
          text: ''
        },
        xAxis: {
          labels: {
            // rotation: 315,
            style: {
              color: '#fff'
            }
          },
          title: {
            text: ''
          },
          categories: [],
          lineColor: '#363A47'
        },
        yAxis: {
          labels: {
            style: {
              color: '#fff',
              border: 'none'
            }
          },
          title: {
            text: ''
          },
          gridLineColor: '#363A47',
        },
        // tooltip: {
        //   headerFormat: '<b>{point.x}</b><br/>',
        //   pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
        // },
        tooltip: {
          useHTML: true,
          formatter: function () {
            const currentSeries = this.series,
              chart = currentSeries.chart,
              stackName = currentSeries.name

            const getDefault = (currentPoint) => {
              let stackValues = ''

              chart.series.forEach((series) => {
                series.points.forEach((point) => {
                  if (currentSeries.userOptions.stack === series.userOptions.stack && currentPoint.key === point.category) {
                    stackValues += `${series.name}: ${point.y} <br/>`
                  }
                })
              })

              return `
                <b>Stack name: </b>${stackName}<br/>
                <b>${this.x}</b><br/>
                ${stackValues}
                Total: ${this.point.stackTotal}
              `
            }

            const userOptions = this.series.userOptions
            return userOptions.tooltipFormater
              ? userOptions.tooltipFormater(stackName, this.x)
              : getDefault(this)
          }
        },
        legend: {
          itemStyle: {
             color: '#fff'
          },
          itemHoverStyle: {
             color: '#3490dc'
          }
        },
        plotOptions: {
          column: {
            stacking: 'normal',
            pointWidth: 40,
            borderWidth: 0,
            // color: '#363A47',
            dataLabels: {
              enabled: true,
              style: {
                textOutline: 0,
                color: '#fff'
              }
            }
          }
        },
        series: []
      }
    }
  },

  computed: {
    year () {
      return (new Date()).getFullYear()
    }
  },

  watch: {
    data: {
      deep: true,
      handler (oldValue, newValue) {
        this.initData()
      }
    }
  },

  mounted () {
    this.initData()
  },

  methods: {
    initData () {
      this.chartOptions = { ...this.chartOptions, ...this.options }
      this.chartOptions.xAxis.categories = this.categories
      this.chartOptions.series = this.data

      // this.chartOptions.tooltip.headerFormat = `<b>{point.x} ${this.year}</b><br/>`

      this.$nextTick(() => {
        this.$refs[this.refChart].chart.setSize(null)
      })
    }
  }
}
</script>
