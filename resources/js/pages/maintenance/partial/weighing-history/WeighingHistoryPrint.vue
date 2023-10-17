<template>
  <div id="weighing-history-print"
    :class="{'d-none': !isPrinting, 'd-block': isPrinting}"
    v-if="isPrinting">

    <div class="container mt-3">
      <div class="row">
      <!-- <h2 class="mb-4">
        <span class="font-weight-bold">Weighing System Transaction</span>
      </h2> -->
      </div>

      <div class="row">
        <table class="" style="width: 100%;">
          <tr>
            <th style="background-color: #b3c6e7; font-weight: normal; font-size: 12px;">User Name</th>
            <th style="background-color: #b3c6e7; font-weight: normal; font-size: 12px;">Card ID</th>
            <th style="background-color: #b3c6e7; font-weight: normal; font-size: 12px;">Trans Date</th>
            <th style="background-color: #b3c6e7; font-weight: normal; font-size: 12px;">Item Name</th>
            <th style="background-color: #b3c6e7; font-weight: normal; font-size: 12px;">Device ID</th>
            <th style="background-color: #b3c6e7; font-weight: normal; font-size: 12px;">Qty Change</th>
            <th style="background-color: #b3c6e7; font-weight: normal; font-size: 12px;">OH Change</th>
          </tr>
          <tr v-for="(item, index) in data">
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.weighing_history.name }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.weighing_history.card_id }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.created_at | dateFormatter(Const.DATETIME_PATTERN, Const.DATETIME) }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.name }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.bin_id }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.change_quantity }}</td>
            <td class=" border text-break text-center" style="font-size: 12px;">{{ item.quantity }}</td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</template>
<script>
  import moment from 'moment'
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
        current: moment().format('DD-MM-YYYY HH:mm'),
        Const,
      };
    },

    methods: {
      print () {
        this.isPrinting = true;
        this.$nextTick(() => {
          this.isPrinting = false;
          this.htmlToPaper(() => {
            this.$emit('WeighingSystemTransaction:closed');
          });
        })
      },

      htmlToPaper (callback) {
        let
          name = '_blank',
          specs = ['fullscreen=yes','titlebar=yes', 'scrollbars=yes'],
          replace = true,
          styles = ['/css/print.css'];
        specs = !!specs.length ? specs.join(',') : '';

        const element = document.getElementById('weighing-history-print');

        if(!element) {
          this.isPrinting = false;
          alert(`Element to print #weighing-history-print not found!`);
          return;
        }

        const url = '';
        const win = window.open(url, name, specs, replace);

        win.document.write(`
          <html>
            <head>
              <title>Weighing System Transaction - ${this.current}</title>
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
