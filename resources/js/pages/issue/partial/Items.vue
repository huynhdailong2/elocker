<template>
  <div class="block spares">
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

    <ul class="list-type">
      <li :class="{active: selectedType == type.value}" v-on:click="onFilterByType(type.value)" v-for="type in itemType">{{type.name}}</li>
    </ul>

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

    <items-grid v-bind="$attrs" ref="spares" v-if="selectedMode === MODE.GRID" />
    <items-list v-bind="$attrs" ref="spares" v-if="selectedMode === MODE.LIST" />

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

  .list-type {
    display: inline-flex;
    justify-content: flex-start;
    justify-items: center;
    border: 1px solid #363A47;
    li {
      padding: 5px 20px 8px;
      cursor: pointer;
      &.active {
        background-color: #3490dc;
        color: #fff;
      }
    }
    li:not(:first-child) {
      border-left: 1px solid #363A47;
    }
  }
}
</style>
<script>
import {chain, isEmpty, debounce, toLower} from 'lodash'
import ItemsGrid from './ItemsGrid'
import ItemsList from './ItemsList'
import Const from 'common/Const'

const MODE = {
  LIST: 'list',
  GRID: 'grid'
}

export default {
  components: {
    ItemsGrid,
    ItemsList
  },

  data () {
    return {
      MODE,
      selectedMode: MODE.LIST,
      inputText: null,
      itemType: [],
      selectedType: '',
    }
  },

  mounted() {
    let listTypeSystem = chain(Const.ITEM_TYPE)
        .map(itemType => {
          const value = toLower(itemType.value);
          const name = itemType.name;
          return { value, name }
        })
        .value()
    this.itemType = [ ...[{name: 'All', value: ''}], ...listTypeSystem]
  },

  watch: {
    inputText: debounce(function () {
      this.onClickSearch()
    }, 400)
  },

  methods: {
    onClickSearch () {
      this.$refs.spares.filter({ text: this.inputText, type: this.selectedType })
    },
    onFilterByType(type) {
      this.selectedType = type;

      return this.$refs.spares.filter({ text: this.inputText, type: this.selectedType });
    }
  }
}
</script>
