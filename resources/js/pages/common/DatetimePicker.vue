<template>
  <div class="datetime-picker">
    <date-picker
      v-model="internalValue"
      :config="{
        format: 'HH:mm:ss DD/MM/YYYY',
        useCurrent: false,
        showClear: true,
        showClose: true,
        icons: {
          time: 'fa fa-clock-o',
          date: 'fa fa-calendar',
          up: 'fa fa-arrow-up',
          down: 'fa fa-arrow-down',
          previous: 'fa fa-chevron-left',
          next: 'fa fa-chevron-right',
          today: 'fa fa-calendar-check',
          clear: 'fa fa-trash',
          close: 'fa fa-times-circle'
        }
      }" />
  </div>
</template>
<script>
  import 'bootstrap/dist/css/bootstrap.css';
  import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';
  import 'font-awesome/css/font-awesome.min.css';
  import DatePicker from 'vue-bootstrap-datetimepicker';

  export default {
    props: ['value'],
    components: {
      DatePicker
    },
    data() {
      return {
        internalValue: null,
      }
    },
    watch: {
      internalValue(newValue) {
        this.$emit('input', newValue);
      },

      value(newValue) {
        this.internalValue = newValue || new Date();
      }
    },
    created() {
    }
  }
</script>
<style lang="scss" scoped>
  .datetime-picker {
    display: inline-block;
    width: 180px;
  }
</style>
