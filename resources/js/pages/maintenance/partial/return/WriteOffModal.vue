<template>
  <modal :name="name" :width="'500'" height="auto" :clickToClose="false" @before-open="beforeOpen" class="custom-modal">
    <div class="header">
      <div class="title">Write Off Item</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content">
      <div class="form">
        <div class="mb-2">Fill a reason to write off the item.</div>
        <div>
          <input type="text" class="input" name="reason" placeholder="Reason" v-model="reason" v-validate="'required'">
          <span class="invalid-feedback" v-if="errors.has('reason')">
            {{ errors.first('reason') }}
          </span>
        </div>
      </div>

      <div class="action">
        <button class="btn btn-primary" @click.stop="onClickSubmit">Submit</button>
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
      default: 'write-off-modal'
    }
  },

  mixins: [RemoveErrorsMixin],

  data() {
    return {
      data: null,
      callback: null,
      reason: null
    }
  },

  methods: {
    beforeOpen(event) {
      const { params } = event
      this.data = params?.data
      this.callback = params?.callback
    },

    close() {
      this.$modal.hide(this.name)
    },

    async onClickSubmit() {
      let spare_id = this.data.spare_id;
      let bin_id = this.data.bin_id;
      // const returnSpareIds = [];
      // spares.forEach(spare => {
      //   returnSpareIds.push(spare.id);
      // });
      this.resetError()

      await this.$validator.validateAll()

      if (this.errors.any()) {
        return
      }

      const params = {
        return_spare_id: spare_id,
        return_bin_id: bin_id,
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
