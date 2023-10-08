<template>
  <div id="inventory-count-print"
    :class="{'d-none': !isPrinting, 'd-block': isPrinting}"
    v-if="isPrinting">

    <div class="container mt-3">
      <div class="row">
      <h2 class="text-center mb-4">
        <span class="font-weight-bold">Inventory Count</span>
      </h2>
      </div>

      <div class="row">
        <div class="border border-dark p-2 text-break text-center text-dark col-1">S/N</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2">MPN</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2">SSN</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2" :class="{'col-3': config.withoutQty}">Description</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-2">Location</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Item Type</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1" v-if="!config.withoutQty">OH Qty</div>
        <div class="border border-dark p-2 text-break text-center text-dark col-1">Count</div>
      </div>

      <div class="row" v-for="(item, index) in data">
        <div class="col-1 border border-dark p-2 text-break text-center text-dark">{{ index + 1 }}</div>
        <div class="col-2 border border-dark p-2 text-break text-dark text-center">{{ item.material_no }}</div>
        <div class="col-2 border border-dark p-2 text-break text-dark text-center">{{ item.part_no }}</div>
        <div class="col-2 border border-dark p-2 text-break text-dark text-left" :class="{'col-3': config.withoutQty}">{{ item.name }}</div>
        <div class="col-2 border border-dark p-2 text-break text-dark text-center">{{ item.cluster_name }} - {{ item.shelf_name }} - {{ item.row }} - {{ item.bin }}</div>
        <div class="col-1 border border-dark p-2 text-break text-dark text-center text-capitalize">{{ item.type }}</div>
        <div class="col-1 border border-dark p-2 text-break text-dark text-center" v-if="!config.withoutQty">{{ item.quantity_oh }}</div>
        <div class="col-1 border border-dark p-2 text-break text-dark"></div>
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

        const element = document.getElementById('inventory-count-print');

        if(!element) {
          this.isPrinting = false;
          alert(`Element to print #inventory-count-print not found!`);
          return;
        }

        const url = '';
        const win = window.open(url, name, specs, replace);

        win.document.write(`
          <html>
            <head>
              <title>Inventory Count - ${this.current}</title>
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
