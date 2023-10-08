<template>
  <div id="vehicle-scheduling-print"
    :class="{'d-none': !isPrinting, 'd-block': isPrinting}"
    v-if="isPrinting">

    <div class="container mt-3">
<!--      <div class="row">-->
<!--        <h2 class="text-center mb-4">-->
<!--          <span class="font-weight-bold">Vehicle Scheduling</span>-->
<!--        </h2>-->
<!--      </div>-->

      <div class="row">
        <div class="border border-dark p-2 text-break text-center text-dark col-1">S/N</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2">Veh no.</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Variant</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Unit</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2">Last O Point Servicing</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">6 mth Plan</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">12 mth Plan</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">18 mth Plan</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">24 mth Plan</div>
      </div>

      <div class="row" v-for="(item, index) in data">
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ index + 1 }}</div>
        <div class=" col-2 border border-dark p-2 text-break text-center text-dark">{{ item.vehicle_num }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.variant }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.unit | uppercase }}</div>
        <div class=" col-2 border border-dark p-2 text-break text-dark text-center">{{ item.last_point_servicing | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
        <div :class="getBbClass(item, 'schedule_6_months', 'completion_date_6_months')" class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.schedule_6_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
        <div :class="getBbClass(item, 'schedule_12_months', 'completion_date_12_months')" class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.schedule_12_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
        <div :class="getBbClass(item, 'schedule_18_months', 'completion_date_18_months')" class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.schedule_18_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
        <div :class="getBbClass(item, 'schedule_24_months', 'completion_date_24_months')" class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.schedule_24_months | dateFormatter(Const.DATE_PATTERN, Const.CLIENT_DATE_PATTERN) }}</div>
      </div>
    </div>
  </div>
</template>

<script>
  import Const from 'common/Const'
  import moment from 'moment'

  export default {
    props: {
      config: {
        type: Object,
        default: () => { withoutQty: false }
      },

      data: {
        type: Array,
        default: () => [],
      }
    },

    data() {
      return {
        isPrinting: false,
        current: moment().format('DD-MM-YYYY HH:mm'),
        Const
      };
    },

    methods: {
      print () {
        this.isPrinting = true;
        this.$nextTick(() => {
          this.isPrinting = false;
          this.htmlToPaper(() => {
            this.$emit('CycleCount:closed');
          });
        })
      },

      htmlToPaper (callback) {
        let
          name = '_blank',
          specs = ['fullscreen=yes','titlebar=yes', 'scrollbars=yes'],
          replace = true,
          styles = ['/css/app.css'];
        specs = !!specs.length ? specs.join(',') : '';

        const element = document.getElementById('vehicle-scheduling-print');

        if(!element) {
          this.isPrinting = false;
          alert(`Element to print #vehicle-scheduling-print not found!`);
          return;
        }

        const url = '';
        const win = window.open(url, name, specs, replace);

        win.document.write(`
          <html>
            <head>
              <title>Vehicle Scheduling - ${this.current}</title>
            </head>
            <body>
              ${element.innerHTML}
            </body>
          </html>
        `);

        this.addStyles(win, styles);

        setTimeout(() => {
          win.document.close();
          win.focus();
          win.print();
          win.close();
          callback();
        }, 1000);
        return true;
      },

      addStyles (win, styles) {
        styles.forEach(style => {
          let link = win.document.createElement('link');
          link.setAttribute('rel', 'stylesheet');
          link.setAttribute('type', 'text/css');
          link.setAttribute('href', style);
          win.document.getElementsByTagName('head')[0].appendChild(link);
        });
      },

      getBbClass (record, attrPlan, attrCompletion) {
        const planDate = record[attrPlan]
        if (!planDate) {
          return
        }

        const completionDate = record[attrCompletion]
        if (completionDate) {
          return 'bg-green'
        }

        const diff = Math.ceil(moment(planDate).diff(moment(), 'months', true))
        switch (diff) {
          case 1:
            return 'bg-red';
            break;
          case 2:
            return 'bg-yellow';
            break;
          case 3:
            return 'bg-green';
            break;
        }
      },
    }
  }
</script>
