<template>
  <modal
    :name="name"
    :width="'550'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    class="custom-modal">
    <div class="header">
      <div class="title">Replenish By</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content">
      <div class="scan-personnel">
        <template v-if="user">
          <div class="info">
            <div>
              <div class="label">Name</div>
              <input type="text" class="input" disabled :value="user.login_name" >
            </div>
            <div>
              <div class="label">Employee ID</div>
              <input type="text" class="input" disabled :value="user.card_id" >
            </div>
            <div class="desc">Item(s) will be passed to the above-mentioned personnel.</div>
          </div>
        </template>
        <template v-else>
          <div class="desc">Please scan personnel ID</div>
          <div class="input-search"><input type="text" class="input" v-model="inputSearch" ref="userCardNo"></div>
        </template>
        <button class="btn btn-primary" @click.stop="onClickSubmit" v-if="user">Submit</button>
      </div>
    </div>
  </modal>
</template>
<style lang="scss" scoped>
.content {
  text-align: center;
  .scan-personnel {
    text-align: center;
    margin-bottom: 40px;
    .input-search {
      margin: 10px 20px;
    }
    .btn {
      margin-top: 10px;
    }
    .info {
      .label {
        width: 100px;
        display: inline-block;
      }
      input {
        width: 50%;
        margin: 10px 20px;
      }
    }
  }
  .btn-primary {
    padding-left: 30px;
    padding-right: 30px;
  }
}
</style>
<script>
import rf from 'requestfactory'
import { chain, debounce, cloneDeep } from 'lodash'

export default {
  props: {
    name: String,
    default: 'ScanRequesterEucItemModal'
  },

  data () {
    return {
      inputSearch: null,
      user: null,
      callback: null,
      data: [],
    }
  },

  watch: {
    inputSearch: debounce(function () {
      this.getUserInfoByCardId()
    }, 300)
  },

  mounted () {
    this.$nextTick(() => {
      this.$refs.userCardNo && this.$refs.userCardNo.focus()
    })
  },

  methods: {
    beforeOpen (event) {
      const { params } = event
      this.callback = params?.callback
      this.data = cloneDeep(params?.data || [])
    },

    close () {
      this.inputSearch = null
      this.user = null

      this.$modal.hide(this.name)
    },

    async onClickSubmit () {
      if (!this.callback || !this.user) {
        return
      }

      this.data = chain(this.data)
        .map(item => {
          return { ...item, requester_id: this.user.id }
        })
        .value()
      await this.callback(this.data)

      this.close()
    },

    getUserInfoByCardId () {
      rf.getRequest('UserRequest').getUserInfoByCardId({ card_id: this.inputSearch })
        .then(res => {
          this.user = res.data;
        })
    }
  }
}
</script>
