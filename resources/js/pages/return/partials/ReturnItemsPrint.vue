<template>
  <div id="return-items-print"
    :class="{'d-none': !isPrinting, 'd-block': isPrinting}"
    v-if="isPrinting">

    <div class="container mt-3">
      <div class="row">
        <h2 class="text-center mb-4">
          <span class="font-weight-bold">[{{ current }}] Return Items</span>
        </h2>
      </div>

      <div class="row">
        <h2 class="text-center mb-4">
          Return Type: <span class="font-weight-bold">{{ action }}</span>
        </h2>
      </div>

      <div class="row" v-if="receiver">
        <h2 class="text-center mb-4">
          Receiver: <span class="font-weight-bold">{{ receiver.login_name }}</span>
        </h2>
      </div>

      <!-- <div class="row" style="background-color: #b3c6e7;">
        <div class="border border-dark p-2 text-break text-center text-dark col-1">S/N</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">MPN</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2">SSN</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2">Description</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-3">Bin #</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Qty On Loan</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Qty Return</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Item Sate</div>
      </div>

      <div class="row" v-for="(item, index) in data">
        <div class=" col-1 border border-dark p-2 text-break text-center text-dark">{{ index + 1 }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.spare.material_no }}</div>
        <div class=" col-2 border border-dark p-2 text-break text-dark text-center">{{ item.spare.part_no }}</div>
        <div class=" col-2 border border-dark p-2 text-break text-dark text-center">{{ item.spare.name }}</div>
        <div class=" col-3 border border-dark p-2 text-break text-dark text-center">{{ item.bin.cluster.name }} - {{ item.bin.shelf.name }} - {{ item.bin.row }} - {{ item.bin.bin }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.quantity_loan || 0 }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.newQuantity || 0 }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.state | upperFirst }}</div>
      </div> -->

      <div class="row">
        <table class="" style="width: 100%;">
          <tr>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-weight: bold; font-size: 12px;">S/N</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-weight: bold; font-size: 12px;">MPN</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-weight: bold; font-size: 12px;">SSN</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-weight: bold; font-size: 12px;">Description</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-weight: bold; font-size: 12px;">Bin #</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-weight: bold; font-size: 12px;">Qty On Loan</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-weight: bold; font-size: 12px;">Qty Return</th>
            <th class="border border-dark p-2 text-break text-center text-dark" style="background-color: #b3c6e7; font-weight: bold; font-size: 12px;">Item Sate</th>
          </tr>
          <tr v-for="(item, index) in data">
            <td class="border border-dark p-2 text-break text-center text-dark" style="font-weight: bold; font-size: 12px;">{{ index + 1 }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark" style="font-weight: bold; font-size: 12px;">{{ item.spare.material_no }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark" style="font-weight: bold; font-size: 12px;">{{ item.spare.part_no }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark" style="font-weight: bold; font-size: 12px;">{{ item.spare.name }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark" style="font-weight: bold; font-size: 12px;">{{ item.bin.cluster.name }} - {{ item.bin.shelf.name }} - {{ item.bin.row }} - {{ item.bin.bin }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark" style="font-weight: bold; font-size: 12px;">{{ item.quantity_loan || 0 }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark" style="font-weight: bold; font-size: 12px;">{{ item.newQuantity || 0 }}</td>
            <td class="border border-dark p-2 text-break text-center text-dark" style="font-weight: bold; font-size: 12px;">{{ item.state | upperFirst }}</td>
          </tr>
        </table>
      </div>

    </div>
  </div>
</template>
<script>
import moment from 'moment'
import { has } from 'lodash'

export default {
  props: {
    data: {
      type: Array,
      default: () => []
    },

    action: {
      type: String,
      default: null
    },

    receiver: {
      type: Object,
      default: null
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

      const element = document.getElementById('return-items-print');

      if(!element) {
        this.isPrinting = false;
        alert(`Element to print #return-items-print not found!`);
        return;
      }

      const url = '';
      const win = window.open(url, name, specs, replace);

      win.document.write(`
        <html>
          <head>
            <title>Return Items - ${this.current}</title>
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
