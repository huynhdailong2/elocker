<template>
  <div id="expiring-items-print"
    :class="{'d-none': !isPrinting, 'd-block': isPrinting}"
    v-if="isPrinting">

    <div class="container mt-3">
      <div class="row">
      <h2 class="text-center mb-4">
        <span class="font-weight-bold">Loan Count</span>
      </h2>
      </div>

      <div class="row" style="background-color: #b3c6e7;">
        <div class="border border-dark p-2 text-break text-center text-dark col-1">WO#/Platform</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Trans Date</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Vehicle</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Item Details</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Part #</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Area Use</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Quantity</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Loan By/Loan To</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Trans</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Load/Hydrostatic Test Due</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Calibration Due</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Expiring Date</div>
      </div>

      <div class="row" :class="{'bg-green': item.fully_returned, 'bg-red': item.expired_return_time }" v-for="(item, index) in data">
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.wo }}/{{ item.platform }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-center text-dark">{{ item.issued_date | dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY') }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.vehicle_num }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.spare_name }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.part_no }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.torque_area }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.quantity }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.issued_by }}/{{ item.issued_to }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.tnx }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.load_hydrostatic_test_due | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY') }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.calibration_due | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY') }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.expiry_date | dateFormatter(Const.DATE_PATTERN, 'DD-MM-YYYY') }}</div>
      </div>
    </div>
  </div>
</template>
<script>
  import Const from 'common/Const'

  export default {
    props: {
      config: {
        type: Object,
        default: () => { withoutQty: false }
      },

      data: {
        type: Array,
        default: () => []
      }
    },

    data() {
      return {
        isPrinting: false,
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

        const element = document.getElementById('expiring-items-print');

        if(!element) {
          this.isPrinting = false;
          alert(`Element to print #expiring-items-print not found!`);
          return;
        }

        const url = '';
        const win = window.open(url, name, specs, replace);

        win.document.write(`
          <html>
            <head>
              <title>Expiring Items #${this.uuid} - Drk Inventory System</title>
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
      }
    }
  }
</script>
