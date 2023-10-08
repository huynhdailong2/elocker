<template>
  <div class="contain">
    <main id="content">
      <div class="auth-container"><slot></slot></div>
    </main>

   <!--  <footer id="footer">
      <page-footer/>
    </footer> -->
  </div>

</template>

<!-- <script>
  import PageFooter from '../PageFooter.vue';

  export default {
    components: {
      PageFooter,
    }
  }
</script>

<style lang="scss" scoped>
  @import "../../../../sass/common";

  #content {
    min-height: calc(100vh - 50px);
    padding-top: 70px;
    padding-bottom: 50px;
  }
</style>

<style lang="scss">
  @import "../../../../sass/common.scss";

  #content {
    .auth-container {
      font-family: $font-family-Montserrat-Medium;
      width: 570px;
      padding: 20px 10px;
      margin: 0px auto;
      // background: $background-catskill-white;
      .guest-logo {
        text-align: center;
        margin: 0 0 20px 0;
        img{
          width: 85px;
          height: 85px;
        }
      }
      .btn-primary {
        width: 100%;          
        -webkit-transition: 0.5s;
        transition: 0.5s;
        text-transform: uppercase;
        &:hover, &:active, &:focus{
          // background: $color-corn;
          // border-color: $color-corn;
          color: $color-white;
          -webkit-transition: 0.5s;
          transition: 0.5s;
        }
      }
      .guest-page-form{
        width: 320px;
        margin: 0 auto;
        .guest-page-form-header{
          .title{
            color: $color_white;
            margin: 0 0 20px;
          }
          .sub-title{
            // color: $color-grey-dusty;
            margin-bottom: 10px
          }
        }
        .guest-page-form-body{
          // font-size: $font-root;
          .text-yellow{
            // color: $text-yellow;
            font-weight: normal;
          }
          .input-group {
            // background-color: $background-catskill-white;
            margin-bottom: 25px;
            .txt {
              opacity: 0.8;
              color: $color_white;
              font-size: $font_smaller;
              line-height: 20px;
              margin: 0px 0px 10px 0px;
              display: block;
              width: 100%;
            }
            .form-input {
              height: 44px;
              border: 2px solid transparent;
              background-color: rgba(255, 255, 255, 0.2) !important;
              line-height: 20px;
              padding: 10px 15px 10px 15px;
              font-size: $font_smaller;
              color: $color_white;
              letter-spacing: 0.2px;
              border-radius: 25px;
              &:focus {
                border: 2px solid $color_bright_turquoise;
              }
            }
          }
          .form_actions{
            .btn-primary {
              color: $color_bright_turquoise;
              border: 2px solid $color_bright_turquoise;
              border-radius: 30px;
              height: 60px;
              background-color: transparent;
              font-size: 12px;
              text-transform: uppercase;
              letter-spacing: 1.6px;
              line-height: 20px;
              padding: 20px 15px;
              text-align: center;
              margin: 20px 0px;
              &:hover {
                color: $deep_sapphire;
                background: $color_white;
                text-decoration: none;
              }
            }
          }
        }
      }
      .t-uppercase {
        color: $color-white;
        text-transform: uppercase;
      }
    }
  }
</style> -->
