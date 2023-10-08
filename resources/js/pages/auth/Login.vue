<template>
  <div class="page login-container clearfix">
    <div class="login-box clearfix" >
      <img alt="" class="drk-logo" src="/images/logo-login.svg" />

      <div class="login-form clearfix">
        <h2 class="title-login">Login</h2>
        <div class="form-group">
          <label>Login Name</label>
          <input
            class="input"
            name="login_name"
            data-vv-as="login ID"
            v-model="inforSigin.login"
            data-vv-validate-on="none"
            v-validate="'required'"
            type="text" >
          <span class="invalid-feedback" v-if="errors.has('login_name')">{{ errors.first('login_name') }}</span>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input
            class="input"
            name="password" 
            v-model="inforSigin.pass" 
            data-vv-validate-on="none"
            v-validate="'required|min:6|max:72'"
            type="password" >
          <span class="invalid-feedback" v-if="errors.has('password')">{{ errors.first('password') }}</span>
        </div>

        <div v-show="errors.has('error')">
          <span class="invalid-feedback"> {{ errors.first('error') }} </span>
        </div>
        <button class="btn btn-primary pl-5 pr-5 mt-3" :class="{ 'submitting': isSubmitting }" @click.stop="submit()">{{ getSubmitName('Login') }}</button>
      </div>
    </div>
  </div>
</template>

<script>
  import rf from 'requestfactory';
  import AuthenticationUtils from 'common/AuthenticationUtils';
  import RemoveErrorsMixin from 'common/RemoveErrorsMixin';

  export default {
    data() {
      return {
        modalMessage: '',
        isConfirming: false,
        isLoginInput: -1,
        login: 'login',

        inforSigin: {
          login: '',
          pass: ''
        }
      }
    },
    mixins: [RemoveErrorsMixin],
    methods: {
      async submit() {
        this.resetError();

        await this.$validator.validateAll();
        if (this.errors.any()) {
          return;
        }

        this.startSubmit();
        rf.getRequest('UserRequest').login(this.inforSigin.login, this.inforSigin.pass)
          .then(response => {
            this.endSubmit();
            AuthenticationUtils.saveAuthenticationData(response);
            const destination = this.$route.query.destination || '/';
            window.location.href = destination;
          })
          .catch(error => {
            this.endSubmit();
            const data = error.response.data;
            let message = data.message;
            if (data.error === 'invalid_credentials') {
              message = 'Invalid login name or password.';
            }
            this.errors.add({field: 'error', msg: message});
          });
      }
    },
    mounted() {
      window.addEventListener('keyup', (event) => {
        if (event.keyCode === 13) {
          this.submit();
        }
      });
    }
  }
</script>

<style lang="scss" scoped>
  @import "./../../../sass/common";

  .invalid-feedback {
    
    font-size: 12px;
    line-height: 16px;
    color: #ED1D24;
  }

  .login-container {
    min-height: 100vh;
    width: 100%;
    background: #11131D  url("/images/background_footer.svg") no-repeat bottom 0px right;

    .login-box {
      width: 500px;
      max-width: 100%;
      margin: 160px auto;
      text-align: center;

      .drk-logo {
        margin-bottom: 40px;
        max-width: 100%;
      }

      .login-form {
        width: 100%;
        background: #212430;
        color: #fff;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.25);
        padding: 38px 70px 67px 70px;

        .title-login {
          font-weight: bold;
          font-size: 22px;
          line-height: 28px;
          margin-bottom: 70px;
        }

        .btn_login {
          width: 100%;
          // background: linear-gradient(0deg, #417AF9 0%, #063694 100%);
          border-radius: 4px;
          margin: 0px;
          margin-top: 40px;
          box-shadow: none;
          opacity: 1;
          font-weight: bold;
          font-size: 16px;
          line-height: 20px;
          align-items: center;
          color: #FFFFFF;
          text-transform: capitalize;

          &.submitting {
            opacity: 0.5;
          }
        }

        .form-group {
          width: 100%;
          margin-bottom: 5px;
          position: relative;
          padding-bottom: 21px;

          .input_field {
            margin: 0px;

            &:after {
              background-color: #D7D7D7;
              height: 2px;
            }

            &.field_error {

              &:after {
                background-color: #ED1D24;
              }

              label {
                color: #ED1D24;
              }
            }
            .md-input {
              font-size: 16px;
              line-height: 20px;
              color: #20222B;
              -webkit-text-fill-color: #20222B;
            }
            .icon_error {
              color: #ED1D24;
            }
          }

          label {
            color: #6D6E71;
          }

          .invalid-feedback {
            font-size: 12px;
            line-height: 16px;
            color: #ED1D24;
            text-align: left;
            position: absolute;
            width: 100%;
            bottom: 0px;
            left: 0px;
            margin: 0px;
          }
        }
      }
    }
  }

</style>
