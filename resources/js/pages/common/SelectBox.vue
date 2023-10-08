<template>
  <div class="select-box">
    <div class="name"
      @click.stop="toggle = !toggle">
      <div class="ellipsis" v-if="name">{{ name }}</div>
      <div class="ellipsis placeholder" v-else>{{ placeholder }}</div>
      <div class="more" v-if="more">+{{ more }}</div>
      <div class="arrow" :class="{rotate: toggle}">
        <img src="/images/icons/icon-arrow-down.svg" />
      </div>
    </div>

    <div class="options" :style="{height: `${number * 35}px`}" v-if="toggle" v-click-outside="closeDropdown">
      <div class="item" v-for="item in options" @click.stop="onSelect(item)">
        <div class="ellipsis">{{ item.name }}</div>
        <img class="selected" src="/images/icons/icon-tick.svg" v-if="isSelected(item)"/>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.select-box {
  position: relative;
  .name {
    border: 1px solid #767676;
    padding: 5px 10px;
    padding-right: 35px;
    border-radius: 2px;
    height: 40px;
    cursor: pointer;
    display: flex;
    .placeholder {
      color: #7d7d7d;
    }
    .more {
      margin-left: 5px;
      padding: 5px 7px;
      border: 1px solid #e9e9eb;
      background-color: #f4f4f5;
      color: #7d7d7d;
      font-size: 12px;
    }
    .arrow {
      position: absolute;
      right: 15px;
      transition: all 0.45s 0.25s;
      img {
        width: 15px;
      }
      &.rotate {
        transform: rotate(180deg);
      }
    }
  }
  .options {
    position: absolute;
    top: 40px;
    width: 100%;
    background-color: #fff;
    border: 1px solid;
    z-index: 10;
    overflow-y: auto;
    .item {
      padding: 5px 10px;
      cursor: pointer;
      position: relative;
      display: flex;
      padding-right: 30px;
      &:hover {
        background-color: #d6cbcb;
      }
      .selected {
        width: 15px;
        position: absolute;
        top: 10px;
        right: 10px;
      }
    }
  }
}
</style>
<script>
import vClickOutside from 'v-click-outside'
import { chain, includes, head, size } from 'lodash'

export default {
  props: {
    value: {
      type: [String, Number, Array],
      default: null
    },

    placeholder: {
      type: [String, Number],
      default: 'Select'
    },

    options: {
      type: Array,
      default: () => []
    },

    multiple: {
      type: Boolean,
      default: false
    },

    number: {
      type: Number,
      default: 5
    }
  },

  data () {
    return {
      toggle: false
    }
  },

  computed: {
    selectedItems () {
      return chain(this.options)
        .filter(item => this.isSelected(item))
        .value()
    },

    name () {
      const first = head(this.selectedItems)
      return first ? first.name : null
    },

    more () {
      const length = size(this.selectedItems)
      return length ? (length - 1) : 0
    }
  },

  directives: {
    clickOutside: vClickOutside.directive
  },

  methods: {
    getValues () {
      return this.multiple
        ? (Array.isArray(this.value) ? this.value : [this.value])
        : [this.value]
    },

    isSelected (item) {
      const values = this.getValues()
      return includes(values, item.value)
    },

    onSelect (item) {
      const addValue = (item) => {
        return this.multiple
          ? chain(this.value).concat(item.value).filter(item => !!item).value()
          : item.value
      }

      const remveValue = (object) => {
        return chain(this.getValues())
          .filter(item => item !== object.value)
          .value()
      }

      const values = this.isSelected(item) ? remveValue(item) : addValue(item)
      this.$emit('input', values)

      !this.multiple && this.closeDropdown()
    },

    closeDropdown () {
      this.toggle = false
    }
  }
}
</script>
