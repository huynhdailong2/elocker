<template>
  <div id="notfound">
    <div class="container">
      <img src="/images/icons/icon-404.svg">
      <div class="content text-break">
        <p>We can’t find the page you’re looking for.</p>
        <p>You can either return to the previous page, visit our homepage or contact our support team.</p>
        <!-- <button class="btn btn-primary" @click.stop="onClick">Home</button> -->
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    methods: {
      onClick () {
        this.$router.push('/home')
      }
    }
  }
</script>
<style lang="scss" scoped>
  @import "../../sass/_variables";

  #notfound {
    height: 100%;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      img {
        width: 200px;
        margin-right: 20px;
      }
      .content {
        width: 500px;
        p {
          font-size: 16px;
          margin-bottom: 2px;
        }
        .btn {
          margin-top: 20px;
          padding: 10px 25px;
        }
      }
    }
  }
</style>
