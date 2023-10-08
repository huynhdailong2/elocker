<template>
  <div id="users-import-modal">
    <modal
      :name="name"
      height="auto"
      :clickToClose="false"
      @before-open="beforeOpen"
      class="custom-modal">
      <div class="header">
        <div class="title">Items Import</div>
        <div class="close" @click.stop="closeModal">
          <img src="/images/icons/icon-cancel.svg" width="22">
        </div>
      </div>
      <div class="content">
        <div>Please choose .csv file</div>
        <div class="form-input mt-2">
          <input type="file"
            name="fileUpload"
            @change="onChangeUploadCSV" accept=".csv"
            v-validate="'required'"
            @focus="resetError"
            data-vv-as="template csv file"
            data-vv-validate-on="none">
          <span class="invalid-feedback"> {{ errors.first('fileUpload') }}</span>
        </div>
        <div class="action">
          <button class="btn-primary" @click.stop="onClickSubmit">{{ getSubmitName('Submit') }}</button>
        </div>
        <div class="download"><a :href="href">Download Csv Template</a></div>
      </div>
    </modal>
  </div>
</template>
<script>
  import rf from 'requestfactory';
  import RemoveErrorsMixin from 'common/RemoveErrorsMixin';

  export default {
    props: {
      name: {type: String, default: 'users-import-modal'},
    },

    mixins: [RemoveErrorsMixin],

    data() {
      return {
        fileUpload: null,
        type: null,
        href: null
      }
    },

    computed: {
    },

    methods: {
      beforeOpen (event) {
        const { params } = event
        this.href = params?.href
        this.handleSubmit = params?.handleSubmit
      },

      closeModal(complete = false) {
        this.$modal.hide(this.name)
        this.$emit('closed', complete)
      },

      onChangeUploadCSV(e) {
        this.fileUpload = null;
        this.resetError();

        let files = e.target.files || e.dataTransfer.files;
        if (!files.length) {
          return;
        }

        this.fileUpload = files[0];
      },

      async onClickSubmit() {
        this.resetError();
        if(this.isSubmitting) {
          return;
        }
        await this.$validator.validate('fileUpload');
        if (this.errors.any()) {
          return;
        }

        const formData = new FormData()
        formData.append('file', this.fileUpload)

        if (this.handleSubmit) {
          this.handleSubmit(formData)
        }
        // rf.getRequest('AdminRequest').importSpares(formData)
        //   .then(res => {
        //     this.showSuccess('Uploading Successful')
        //     this.closeModal(true)
        //   })
        //   .catch(err => {
        //     this.processAndToastFirstError(err)
        //   })
      }
    }
  }
</script>
<style lang="scss" scoped>
  #users-import-modal {
    .content {
      color: #fff;
      padding: 20px;
      text-align: center;
      .invalid-feedback {
        text-align: center !important;
      }
      .action {
        margin-top: 50px;
      }
      .download {
        margin-top: 10px;
      }
    }
  }
</style>
