<template>
  <div class="spares">
    <div class="form-search">
      <label>Item P/N:</label>
      <div>
        <input
          type="text"
          class="input"
          placeholder="Item P/N"
          v-model="inputText"
          @keypress.enter="onClickSearch"
          ref="inputText" >
      </div>
      <button class="btn btn-primary" @click.stop="onClickSearch">Search</button>
    </div>

    <div class="view">
      <div class="option" :class="{active: selectedMode === MODE.LIST}" @click.stop="selectedMode = MODE.LIST">
        <img src="/images/icons/icon-list.svg" width="20">
        <span>List</span>
      </div>
      <div class="option" :class="{active: selectedMode === MODE.GRID}" @click.stop="selectedMode = MODE.GRID">
        <img src="/images/icons/icon-grid.svg" width="20">
        <span>Image</span>
      </div>
    </div>

    <items-grid-manual v-bind="$attrs" ref="spares" v-if="selectedMode === MODE.GRID" />
    <items-list-manual v-bind="$attrs" ref="spares" v-if="selectedMode === MODE.LIST" />

  </div>
</template>
<style lang="scss" scoped>
.spares {
  .form-search {
    display: flex;
    justify-content: center;
    align-items: baseline;
    margin-bottom: 40px;
    .input {
      width: 350px;
      margin: 0 10px;
    }
    button {
      padding: 15px 40px;
      margin-top: 20px;
    }
  }
  .view {
    right: 0;
    .option {
      cursor: pointer;
      display: inline-block;
      padding: 10px;
      border: 1px solid #c7cbce;
      border-radius: 5px;
      margin: 0 5px;
      span {
        margin-left: 10px;
      }
      &:hover {
        border-color: #3490dc;
      }
      &.active {
        background-color: #3490dc;
        color: #fff;
      }
    }
  }
}
</style>
<script>
import { chain, isEmpty, debounce } from 'lodash'
import ItemsGridManual from './ItemsGridManual'
import ItemsListManual from './ItemsListManual'

const MODE = {
  LIST: 'list',
  GRID: 'grid'
}

export default {
  components: {
    ItemsGridManual,
    ItemsListManual
  },

  data () {
    return {
      MODE,
      selectedMode: MODE.LIST,
      inputText: null
    }
  },

  watch: {
    inputText: debounce(function () {
      this.onClickSearch()
    }, 400)
  },

  methods: {
    onClickSearch () {
      this.$refs.spares.filter({ text: this.inputText })
    }
  }
}
</script>
