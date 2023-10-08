<template>
  <div class="issue-pol">
    <div class="content">
      <div class="input-form text-center">
        <span>POL Item: </span>
        <input
          type="text"
          class="input"
          placeholder="Seach by material number"
          v-model="inputSearch"
          ref="inputSearch">
        <button class="btn btn-primary">Search</button>
      </div>

      <issuing-pol-table :pols="data" @done="handleIssuingPol"/>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.issue-pol {
  .content {
    margin: auto;
    font-size: 16px;
    width: 70%;
    min-width: 700px;
    .input-form {
      margin-top: 30px;
      .input {
        margin-top: 10px;
        margin-bottom: 10px;
        width: 400px;
      }
    }
  }
}
</style>
<script>
import rf from 'requestfactory'
import Const from 'common/Const'
import { chain, debounce } from 'lodash'
import IssuingPolTable from './partial/IssuingPolTable'

export default {
  components: {
    IssuingPolTable
  },

  data () {
    return {
      inputSearch: null,
      data: []
    }
  },

  watch: {
    inputSearch: debounce(function (newVal) {
      this.getPolManagements()
    }, 300)
  },

  mounted () {
    this.getPolManagements()
  },

  methods: {
    getPolManagements () {
      const params = {
        no_pagination: true,
        search_key: this.inputSearch
      }

      rf.getRequest('AdminRequest').getPolManagements(params)
        .then(res => {
          this.data = res.data || []
        })
    },

    handleIssuingPol () {
      this.getPolManagements()
    }
  }
}
</script>
