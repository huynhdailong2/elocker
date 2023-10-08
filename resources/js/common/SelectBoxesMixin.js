import { chain, each, every, isEmpty } from 'lodash'

export default {
  data () {
    return {
      data: [],
      checkedAll: false,
    }
  },

  computed: {
    selectedItems () {
      return chain(this.data)
        .filter(item => item.is_checked)
        .map(item => item.id)
        .value()
    },
  },

  watch: {
    data: {
      deep: true,
      handler () {
        this.checkedAll = isEmpty(this.data) ? false : every(this.data, 'is_checked')
      }
    }
  },

  methods: {
    onClickCheckedAll () {
      each(this.data, item => {
        this.$set(item, 'is_checked', !this.checkedAll)
      })

      this.afterSelectingBox()
    },

    onSelectBox(item, index) {
      this.$set(item, 'is_checked', !item.is_checked)

      this.checkedAll = isEmpty(this.data) ? false : every(this.data, 'is_checked')
      this.afterSelectingBox()
      this.afterSelectingOneBox(item)
    },

    resetCheckedbox () {
      each(this.data, item => {
        this.$set(item, 'is_checked', !item.is_checked)
      })
    },

    afterSelectingBox () {},

    afterSelectingOneBox(item) {}
  }
}
