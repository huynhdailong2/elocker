<template>
  <div class="page">
    <div class="title-page">
      <div class="text-center">Issue</div>
    </div>
    <div class="content">

      <items :jobCard="jobCard" v-if="jobCard"/>
      <item-form @done="handleGetJobCard" v-else/>

    </div>
  </div>
</template>
<style lang="scss" scoped>
  .page {
    .content {
      margin: auto;
      font-size: 16px;
      width: 70%;
      min-width: 700px;
    }
  }
</style>
<script>
import ItemForm from './partial/ItemForm'
import Items from './partial/Items'

export default {
  components: {
    ItemForm,
    Items
  },

  data () {
    return {
      jobCard: null,
    }
  },

  methods: {
    handleGetJobCard (data) {
      this.jobCard = data
    }
  }
}
</script>
