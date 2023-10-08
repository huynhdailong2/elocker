<template>
  <div class="item-list">
    <div>

      <template v-if="selectedAction === ACTION.REPLENISH_FORM">
        <item-form-auto
          :replenishment="selectedReplenishment"
          @back="onItemFormBackedHandler"
          @done="onItemFormDoneHandler" />
      </template>

      <template v-if="selectedAction === ACTION.REPLENISH_LIST">
        <div class="mt-2 mb-2">
          <button class="btn btn-primary" @click.stop="onClickAddNew">Add Replenish</button>
        </div>

        <div class="table-scroller">
          <table>
            <thead>
              <th>No</th>
              <th>Replenish #</th>
              <th>Last Editted</th>
              <th>Edit</th>
              <th>Delete</th>
            </thead>
            <tbody>
              <template v-if="noData">
                <tr><td colspan="5">There is no replenish request.</td></tr>
              </template>
              <template v-else>
                <tr v-for="(item, index) in replenishments">
                  <td><div>{{ index + 1 }}</div></td>
                  <td><div>{{ item.uuid }}</div></td>
                  <td><div>{{ item.updated_at | timestampFormatter }}</div></td>
                  <td>
                    <div><img src="/images/icons/icon-edit.svg" width="25" @click.stop="onClickEdit(item, index)"></div>
                  </td>
                  <td>
                    <div><img src="/images/icons/icon-trash.svg" width="25" @click.stop="onClickDelete(item, index)"></div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </template>

    </div>
  </div>
</template>
<style lang="scss" scoped>
</style>
<script>
import rf from 'requestfactory'
import { isEmpty } from 'lodash'
import ItemFormAuto from './ItemFormAuto'
import Const from 'common/Const'

const ACTION = {
  REPLENISH_FORM: 'replenish-form',
  REPLENISH_LIST: 'replenish-list'
}

export default {
  components: {
    ItemFormAuto
  },

  data () {
    return {
      replenishments: [],
      selectedReplenishment: null,
      ACTION,
      selectedAction: ACTION.REPLENISH_LIST
    }
  },

  computed: {
    noData () {
      return isEmpty(this.replenishments)
    }
  },

  created () {
    this.getReplenishAutoList()
  },

  methods: {
    getReplenishAutoList () {
      const params = {
        no_pagination: true,
        type: Const.REPLENISH_TYPE.AUTO.value
      }
      rf.getRequest('SpareRequest').getReplenishAutoList(params)
        .then(res => {
          this.replenishments = res.data || []
        })
    },

    onClickDelete (item, index) {
      rf.getRequest('SpareRequest').deleteReplenishAuto({id: item.id})
        .then(res => {
          this.refresh()
        })
    },

    onClickAddNew () {
      this.selectedAction = ACTION.REPLENISH_FORM
    },

    onClickEdit (item, index) {
      this.selectedReplenishment = item
      this.selectedAction = ACTION.REPLENISH_FORM
    },

    onItemFormBackedHandler () {
      this.selectedAction = ACTION.REPLENISH_LIST
      this.selectedReplenishment = null
      this.refresh()
    },

    onItemFormDoneHandler () {
      this.selectedAction = ACTION.REPLENISH_LIST
      this.selectedReplenishment = null
      this.refresh()
    },

    refresh () {
      this.getReplenishAutoList()
    }
  }
}
</script>
