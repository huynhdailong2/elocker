<template>
  <div id="footer">
    Powered by DrK System
  </div>
</template>
<style lang="scss" scoped>
@import "./../../sass/_variables";
#footer {
  height: 50px;
  background: #11131D;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.25);
  font-size: 14px;
  line-height: 18px;
  color: #6D6E71;
  padding: 15px 26px;
  width: 100%;
  display: flex;
  align-items: center;
}
</style>
<script>
  export default {
    name: 'Footer',

    methods: {
      onClickHome () {
        this.$router.push('/home')
      }
    }
  }
</script>
