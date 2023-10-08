<template>
  <div class="page">
    <div class="title-page">
      <div class="text-center">Service/WO Management</div>
    </div>

    <nav class="nav">
      <a class="nav-link"
         :class="{'active': selected === TABS.LIST_SERVICE_MO}"
         href="javascript:void(0)"
         @click.stop="selected = TABS.LIST_SERVICE_MO">WO/Svc

      </a>
      <a class="nav-link"
         :class="{'active': selected === TABS.LIST_CLOSED_SERVICE_MO}"
         href="javascript:void(0)"
         @click.stop="selected = TABS.LIST_CLOSED_SERVICE_MO">Closed WO/Svc
      </a>
    </nav>

    <div class="content">
      <div class="list">
        <div class="block">
          <template v-if="selected === TABS.LIST_SERVICE_MO">
            <list-job-cards />
          </template>
          <template v-if="selected === TABS.LIST_CLOSED_SERVICE_MO">
            <list-closed-job-cards />
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
</style>

<script>
import ListJobCards from "./ListJobCards";
import ListClosedJobCards from "./ListClosedJobCards";

const TABS = {
  LIST_SERVICE_MO: 'list_service_mo',
  LIST_CLOSED_SERVICE_MO: 'list_closed_service_mo'
}

export default {
  components: {
    ListJobCards,
    ListClosedJobCards
  },

  data() {
    return {
      TABS,
      selected: TABS.LIST_SERVICE_MO
    }
  }
}
</script>
