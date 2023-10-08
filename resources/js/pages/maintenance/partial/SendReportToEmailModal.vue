<template>
  <modal
    :name="name"
    :width="'650px'"
    height="auto"
    :clickToClose="false"
    @before-open="beforeOpen"
    class="custom-modal">
    <div class="header">
      <div class="title">Send Report To Email</div>
      <div class="close" @click.stop="close">
        <img src="/images/icons/icon-cancel.svg" width="22">
      </div>
    </div>
    <div class="content">
      <div class="mb-2note">Note: Please press enter/space to add the typing.</div>
      <InputTag validate="email" v-model="emails" :addTagOnBlur="true" :add-tag-on-keys="[32, 13]" placeholder="Please input some emails, ex: email1,email2,..." />
      <div class="text-center">
        <button class="btn btn-primary mt-2 d-flex" @click="onOlickCallback" :disabled="this.emails.length === 0">
          <span class="mr-1">Send</span>
          <div class="loader" :class="{ loading }"></div>
        </button>
      </div>
    </div>
  </modal>
</template>
<style lang="scss" scoped>
.content {
  width: 100%;
  max-height: 500px;
  overflow: auto;
  .note {
    font-weight: bold;
    font-size: 12px;
  }
  .loader {
    width: 1em;
    height: 1em;
    z-index: -1;
    opacity: 0;
    transition: all .3s;
    transition-timing-function: ease-in;
    &::after {
      border-radius: 50%;
      border: .3em solid currentColor;
      border-left-color: transparent;
      content: " ";
      display: block;
      width: 2em;
      height: 2em;
      box-sizing: border-box;
      transform-origin: 0 0;
      transform: translateZ(0) scale(0.5);
    }
  }

  .loading {
    z-index: 1;
    opacity: 1;
    animation: ld-spin-fast 1s infinite linear;
  }
}

@keyframes ld-spin-fast {
  0% {
    animation-timing-function: cubic-bezier(0.5856,0.0703,0.4143,0.9297);
    transform: rotate(0);
  }

  100% {
    transform: rotate(1800deg);
  }
}
</style>
<script>
import InputTag from 'vue-input-tag'

export default {
  components: {
    InputTag
  },

  props: {
    name: {
      type: String,
      default: 'send-report-to-email-modal'
    }
  },

  data () {
    return {
      emails: [],
      loading: false,
      callback: null
    }
  },

  methods: {
    beforeOpen (event) {
      const { params } = event
      this.callback = params?.callback
    },

    async onOlickCallback () {
      if (this.emails.length === 0) return
      if (this.callback) {
        this.loading = true
        await this.callback(this.emails).finally(() => {
          this.showSuccess()
          this.loading = false
        })
      }
      this.emails = []
      this.loading = false
      this.close()
    },

    close () {
      this.emails = []
      this.$modal.hide(this.name)
    }
  }
}
</script>
