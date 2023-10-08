<template>
  <modal
    :name="name"
    :width="'70%'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    class="custom-modal">
    <div class="header">
      <div class="title">Weighing History Detail</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content">
      <div class="table-scroller">
        <table>
          <thead>
          <th>No.</th>
          <th>Device ID</th>
          <th>Name</th>
          <th>Quantity</th>
          <th>Change Quantity</th>
          <th>Location</th>
          </thead>
          <tbody>
          <tr v-for="(item, index) in data">
            <td>{{ index + 1 }}</td>
            <td>{{ item.device_id }}</td>
            <td>{{ item.name }}</td>
            <td>{{ item.quantity }}</td>
            <td>{{ item.change_quantity }}</td>
            <td>{{ item.site_id + '-' + item.room_id + '-' + item.shelf_id + '-' + item.bin_id }}</td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </modal>
</template>
<style lang="scss" scoped>
.content {
  width: 100%;
  text-align: center;
  .form {
    margin-bottom: 30px;
  }
}
</style>
<script>
import rf from 'requestfactory'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'

export default {
  props: {
    name: {
      type: String,
      default: 'weighing-history-detail-modal'
    }
  },

  mixins: [RemoveErrorsMixin],

  data () {
    return {
      data: null,
      callback: null,
      reason: null
    }
  },

  methods: {
    beforeOpen (event) {
      const { params } = event
      this.data = params?.data
      console.log(params, this.data);
      this.callback = params?.callback
    },

    close () {
      this.$modal.hide(this.name)
    },

    async onClickSubmit () {
      this.resetError()

      await this.$validator.validateAll()

      if (this.errors.any()) {
        return
      }

      const params = {
        return_spare_id: this.data.return_spare_id,
        reason: this.reason
      }

      rf.getRequest('SpareRequest').writeOffSpareExpired(params)
        .then(res => {
          this.showSuccess('Successful!')
          !!this.callback && this.callback()
          this.close()
        })
        .catch(error => {
          this.processAndToastFirstError(error)
        })
    }
  }
}
</script>
