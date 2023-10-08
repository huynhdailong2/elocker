<template>
  <div id="main-app">
    <router-view></router-view>

    <confirmation-modal />
  </div>

</template>
<script>
import ConfirmationModal from './common/ConfirmationModal'

export default {
  components: {
    ConfirmationModal
  }
}
</script>
