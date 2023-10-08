<template>
  <div id="expiring-items-print"
    :class="{'d-none': !isPrinting, 'd-block': isPrinting}"
    v-if="isPrinting">

    <div class="container mt-3">
      <div class="row">
      <h2 class="text-center mb-4">
        <span class="font-weight-bold">Expired Count</span>
      </h2>
      </div>

      <div class="row" style="background-color: #b3c6e7;">
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Expiring Date</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2">Item Details</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Part #</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2">Qty</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Lead Time</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Item location</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Remarks</div>
      </div>

      <div class="row" v-for="(item, index) in data">
        <div class=" col-1 border border-dark p-2 text-break text-center text-dark">{{ item.issued_date | dateFormatter('YYYY-MM-DD HH:mm:ss', 'DD-MM-YYYY') }}</div>
        <div class=" col-2 border border-dark p-2 text-break text-dark text-center">{{ item.spare_name }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.part_no }}</div>
        <div class=" col-2 border border-dark p-2 text-break text-dark text-center">{{ item.quantity_oh }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center"></div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.location }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center"></div>
      </div>
    </div>
  </div>
</template>
<script>
  import moment from 'moment'

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
        current: moment().format('DD-MM-YYYY HH:mm')
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
              <title>Expired Items - ${this.current}</title>
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
