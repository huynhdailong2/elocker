<template>
  <div class="layout">
    <div class="bubble">
      <div class="icon" @click.stop="onClickHome">
        <img src="/images/icons/home.png">
        <label>Home</label>
      </div>
      <div class="icon" @click.stop="onClickPrevious">
        <img src="/images/icons/icon-back.svg" class="back">
        <label>Previous</label>
      </div>
    </div>

    <div class="content-app">
      <div class="clearfix right_layout">
        <header-page />
        <div class="clearfix"></div>
        <main id="main-content">
          <router-view></router-view>
        </main>
        <footer-page />
      </div>
    </div>

  </div>
</template>
<script>
  import HeaderPage from './HeaderPage';
  import FooterPage from './FooterPage';

  export default {
    components: {
      HeaderPage,
      FooterPage,
    },

    methods: {
      onClickHome() {
        this.$router.push('/home')
      },

      onClickPrevious(menu) {
        this.$router.back()
      }
    }
  }
</script>
<style lang="scss" scoped>
  @import "../../sass/variables";

  $widthMenu: 80px;
  $heightHeader: 80px;
  $heightFooter: 50px;

  .layout {
    position: relative;

    .bubble {
      position: absolute;
      top: 20%;
      width: $widthMenu; 
      transition: all 0.3s;
      color: #fff;
      box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.25);
      z-index: 10;
      background-color: #212430;
      .icon {
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        padding: 10px;
        img {
          width: 40px;
          &.back {
            width: 30px;
          }
        }
        label {
          cursor: pointer;
          margin-top: 5px;
          text-align: center;
        }
        &:first-child {
          border-bottom: 1px solid #363A47;
          margin-bottom: 5px;
        }
      }
    }

    .content-app {
      width: 100%;
      min-width: 768px;
      content: "";
      clear: both;
      display: flex;
      justify-content: flex-start;

      .right_layout {
        min-width: 100vw;
        min-height: 100vh;
        transition: width 0.3s;
        background: #ECECEC;

        #main-content {
          height: calc(100vh - #{$heightHeader} - #{$heightFooter});
          width: 100vw;
          padding-left: 70px;
          overflow: auto;
          background: #11131D;
          color: #fff;
          // background: #ECECEC  url("/images/bg_footer.svg") no-repeat bottom -50px right;
        }
      }
    }
  }
</style>
