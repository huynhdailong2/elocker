<template>
  <div class="item-list">
    <div class="">

      <div class="form-search">
        <label>Item RL:</label>
        <div>
          <input
            type="text"
            class="input"
            name="inputText"
            placeholder="Replenish #"
            v-model.trim="inputText"
            @keypress.enter="onClickSearch"
            ref="inputText" >
        </div>
        <!-- <button class="btn btn-primary" @click.stop="onClickSearch">Search</button> -->
      </div>

      <template v-if="isSearching">
        <div v-if="replenishInfo">
          <div class="mt-2 mb-2">
            <button class="btn btn-primary pl-4 pr-4" @click.stop="onClickSubmit">Submit</button>
          </div>

          <div class="table-scroller">
            <table>
              <thead>
                <th>No</th>
                <th>MPN</th>
                <th>SSN</th>
                <th>Description</th>
                <th>Bin #</th>
                <th>Quantity OH</th>
                <th class="maw_145x">Stock Qty</th>
              </thead>
              <tbody>
                <template v-if="noData">
                  <tr>
                    <td colspan="9"><div class="text-center">There is no data.</div></td>
                  </tr>
                </template>
                <template v-else>
                  <tr v-for="(item, index) in replenishInfo.replenish_spares">
                    <td><div>{{ index + 1 }}</div></td>
                    <td><div>{{ item.spare.material_no }}</div></td>
                    <td><div>{{ item.spare.part_no }}</div></td>
                    <td><div>{{ item.spare.name }}</div></td>
                    <td><div>{{ item.bin_info.cluster_name }} - {{ item.bin_info.shelf_name }} - {{ item.bin_info.row }} - {{ item.bin_info.bin }}</div></td>
                    <td><div>{{ item.bin_info.quantity_oh || 0 }}</div></td>
                    <td><div>{{ item.quantity || 0 }}</div></td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>

        <div class="text-center" v-else>There is no data.</div>
      </template>

    </div>

  </div>
</template>
<style lang="scss" scoped>
.item-list {
  .form-search {
    display: flex;
    justify-content: center;
    align-items: baseline;
    margin-bottom: 40px;
    margin-top: 20px;
    .input {
      width: 300px;
      margin: 0 10px;
    }
    button {
      padding: 15px 40px;
      margin-top: 20px;
    }
  }
}
</style>
<script>
import rf from 'requestfactory'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
import { isEmpty, chain, debounce } from 'lodash'

export default {
  mixins: [RemoveErrorsMixin],

  data () {
    return {
      inputText: null,
      replenishInfo: null,
      isSearching: false
    }
  },

  computed: {
    noData () {
      return !this.replenishInfo || isEmpty(this.replenishInfo.replenish_spares)
    }
  },

  watch: {
    inputText: debounce(function (newVal) {
      this.onClickSearch()
    }, 300)
  },

  mounted () {
    this.$nextTick(() => {
      this.$refs.inputText.focus()
    })
  },

  methods: {
    async onClickSearch () {
      this.getReplenishAutoByUuid()
    },

    getReplenishAutoByUuid () {
      this.isSearching = true

      const params = {
        uuid: this.inputText
      }
      rf.getRequest('SpareRequest').getReplenishAutoByUuid(params)
        .then(res => {
          this.replenishInfo = res.data
        })
    },

    onClickSubmit () {
      const callback = () => {
        const params = {
          replenish_id: this.replenishInfo.id
        }

        rf.getRequest('SpareRequest').confirmReplenishAuto(params)
          .then(res => {
            this.showSuccess('Successful!')
            this.$router.push('/inventory')
          })
          .catch(error => {
            this.processErrors(error)
          })
      }

      this.confirmAction({ callback: callback })
    }
  }
}
</script>
