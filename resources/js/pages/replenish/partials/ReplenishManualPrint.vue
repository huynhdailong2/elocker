<template>
  <div id="replenish-manual-print"
    :class="{'d-none': !isPrinting, 'd-block': isPrinting}"
    v-if="isPrinting">

    <div class="container mt-3">
      <div class="row">
      <h2 class="text-center mb-4">
        <span class="font-weight-bold">[{{ current }}] Manual Replenish</span>
      </h2>
      </div>

      <div class="row" style="background-color: #b3c6e7;">
        <div class="border border-dark p-2 text-break text-center text-dark col-1">S/N</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2">MPN</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2">SSN</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2">Description</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-3">Bin #</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Qty OH</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Qty RL</div>
      </div>

      <div class="row" v-for="(item, index) in data">
        <div class=" col-1 border border-dark p-2 text-break text-center text-dark">{{ index + 1 }}</div>
        <div class=" col-2 border border-dark p-2 text-break text-dark text-center">{{ item.material_no }}</div>
        <div class=" col-2 border border-dark p-2 text-break text-dark text-center">{{ item.part_no }}</div>
        <div class=" col-2 border border-dark p-2 text-break text-dark text-center">{{ item.name }}</div>
        <div class=" col-3 border border-dark p-2 text-break text-dark text-center">{{ item.shelf_name }} - {{ item.row }} - {{ item.bin }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ item.quantity_oh || 0 }}</div>
        <div class=" col-1 border border-dark p-2 text-break text-dark text-center">{{ getQtyRl(item) || 0 }}</div>
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

      const element = document.getElementById('replenish-manual-print');

      if(!element) {
        this.isPrinting = false;
        alert(`Element to print #replenish-manual-print not found!`);
        return;
      }

      const url = '';
      const win = window.open(url, name, specs, replace);

      win.document.write(`
        <html>
          <head>
            <title>Replenish Item - ${this.current}</title>
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

    getQtyRl (item) {
      if (has(item, 'inputForm')) {
        return item.inputForm.quantity
      }

      return item.quantity_rl
    }
  }
}
</script>
