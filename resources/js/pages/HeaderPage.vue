<template>
  <div id="header">
    <img src="/images/full-logo.svg">
      <h2 class="tilele_page">{{ titlePage }}</h2>

      <div class="right_header">
        <div class="right_dropdown">
          <button class="box_button box_avt">
            {{ user.name | upperFirst }}
          </button>
          <img src="/images/icons/icon-dropdown-black.svg" />
          <div class="dropdown_action">
            <ul class="list_action">
              <li class="item"><a class="dropdown-item" href="#" @click.stop="onClickedLogout">Sign out</a></li>
            </ul>
          </div>
        </div>
      </div>
  </div>
</template>

<script>
  import AuthenticationUtils from 'common/AuthenticationUtils'
  import Const from 'common/Const'
  import { mapState } from 'vuex';

  export default {
    props: {
      titlePage: { type: String, default: ''}
    },

    data() {
      return {
        menus: [],
      };
    },

    computed: {
      ...mapState(['user']),
    },

    methods: {
      onClickedLogout() {
        AuthenticationUtils.removeAuthenticationData()
        window.location.href = '/'
        window.localStorage.removeItem('role')
      },

      gotoRouterName(name) {
        this.$router.push({name: name});
      }
    }
  }
</script>
<style lang="scss" scoped>
  @import "./../../sass/_variables";

  #header {
    height: 80px;
    width: 100%;
    display: flex;
    align-items: center;
    padding: 20px 45px;
    border-bottom: 1px solid #E3342F;
    background: #212430 url("/images/background_header.svg") no-repeat top left;
    color: #fff;

    .tilele_page {
      width: calc(100% - 300px);
      margin: 0px;
      color: #20222B;
      font-size: 22px;
      line-height: 22px;
      font-weight: bold;
    }

    .right_header {
      text-align: right;
      width: 300px;

      .box_button {
        box-shadow: none;
        border: none;
        padding: 0px;
        // width: 40px;
        font-weight: 600;
        margin-left: 5px;
        height: 40px;
        outline: none;
        background-color: transparent;

        &.box_avt {
          color: #fff;
          img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
          }
        }
      }
      

      .right_dropdown {
        display: inline-block;
        position: relative;
        float: right;
        text-align: left;

        &:hover {

          .dropdown_action {
            display: block;
            transition: all 0.3s;
          }
        }

        .dropdown_action {
          transition: all 0.3s;
          display: none;
          position: absolute;
          right: 0px;
          top: 100%;
          z-index: 10;
          padding-top: 4px;

          .list_action {
            width: 170px;
            filter: drop-shadow(0px 4px 8px rgba(0, 0, 0, 0.25));
            background: #FFFFFF;
            border-radius: 4px;
            padding: 15px 0px;

            li {
              display: block;
              width: 100%;

              >span,
              >a {
                display: block;
                width: 100%;
                font-size: 16px;
                line-height: 20px;
                color: #20222B;
                padding: 8px 5px 7px 19px;
                cursor: pointer;
                text-decoration: none;

                &:hover {
                  background: #F3CCCC;
                }
              }
            }
          }
        }
      }      
    }
  }
</style>
