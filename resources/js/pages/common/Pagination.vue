<template>
  <div>
    <div class="VuePagination clearfix">
      <!-- <div class="group_number_items">
        <span class="txt_nb_itme">Number items</span>
        <select class="list_number_items" v-model="limit">
          <option v-for="item in optionPages" :value="item.value">{{ item.name }}</option>
        </select>
      </div> -->
      <div class="page_in">Page {{ pageParent }} of {{totalPages}}</div>
      <ul class="pagination VuePagination__pagination">

        <!--<li class="VuePagination__pagination-item page-item VuePagination__pagination-item-prev-chunk "-->
            <!--:class="{disabled : !allowedChunk(-1)}">-->
          <!--<a class="page-link" href="javascript:void(0);"-->
             <!--@click="setChunk(-1)">&lt;&lt;</a>-->
        <!--</li>-->


        <li class="VuePagination__pagination-item page-item VuePagination__pagination-item-prev-page page-prev"
            :class="{disabled : !allowedPage(page - 1)}">
          <a class="page-link " href="javascript:void(0);"
             @click="prev()"><img src="/images/icons/icon-pagination-left.svg" /></a> <!-- '←'  -->
        </li>

        <template v-for="item in pages">
          <li class="VuePagination__pagination-item page-item "
              :class="{active: parseInt(page) === parseInt(item)}">
            <a class="page-link" role="button"
               @click="setPage(item)">{{item}}</a>
          </li>
        </template>

        <li class="VuePagination__pagination-item page-item VuePagination__pagination-item-next-page page-next"
            :class="{disabled : !allowedPage(page + 1)}">
          <a class="page-link" href="javascript:void(0);"
             @click="next()"><img src="/images/icons/icon-pagination-right.svg" /></a> <!-- '→' -->
        </li>

        <!--<li class="VuePagination__pagination-item page-item VuePagination__pagination-item-next-chunk "-->
            <!--:class="{disabled : !allowedChunk(1)}">-->
          <!--<a class="page-link" href="javascript:void(0);"-->
             <!--@click="setChunk(1)">&gt;&gt;</a>-->
        <!--</li>-->
      </ul>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      pageParent: {
        type: Number,
        default: 1,
      },
      records: {
        type: Number,
        required: true
      },
      chunk: {
        type: Number,
        required: false,
        default: 6
      },
      perPage: {
        type: Number,
        required: true,
      },
    },
    data: function () {
      return {
        page: this.pageParent,
        limit: this.perPage,

        optionPages: [],
      }
    },
    watch: {
      records() {
        if (this.page > this.totalPages) {
          this.page = 1;
        }
      },
      pageParent() {
        this.page = this.pageParent;
      },
      limit(newValue) {
        this.$emit('change-limit', parseFloat(newValue));
      }
    },
    computed: {
      pages: function () {
        if (!this.records)
          return 1

        return range(this.paginationStart, this.chunk, this.totalPages)
      },
      totalPages: function () {
        return this.records ? Math.ceil(this.records / this.perPage) : 1
      },
      totalChunks: function () {
        return Math.ceil(this.totalPages / this.chunk)
      },
      currentChunk: function () {
        return Math.ceil(this.page / this.chunk)
      },
      paginationStart: function () {
        return ((this.currentChunk - 1) * this.chunk) + 1
      },
      pagesInCurrentChunk: function () {

        return this.paginationStart + this.chunk <= this.totalPages ? this.chunk : this.totalPages - this.paginationStart + 1

      },
    },
    methods: {
      setPage: function (page) {
        if (this.allowedPage(page)) {
          this.paginate(page)
        }
      },
      paginate (page) {
        this.page = page
        this.$emit('Pagination:page', page)
      },
      next: function () {
        return this.setPage(this.page + 1)
      },
      prev: function () {
        return this.setPage(this.page - 1)
      },
      setChunk: function (direction) {
        this.setPage((((this.currentChunk - 1) + direction) * this.chunk) + 1)
      },
      allowedPage: function (page) {
        return page >= 1 && page <= this.totalPages
      },
      allowedChunk: function (direction) {
        return (parseInt(direction) === 1 && this.currentChunk < this.totalChunks)
          || (parseInt(direction) === -1 && this.currentChunk > 1)
      },
      allowedPageClass: function (direction) {
        return this.allowedPage(direction) ? '' : 'disabled'
      },
      allowedChunkClass: function (direction) {
        return this.allowedChunk(direction) ? '' : 'disabled'
      },
      activeClass: function (page) {
        return parseInt(this.page) === parseInt(page) ? 'active' : ''
      },

      getOptionPages() {
        const limit = window._.cloneDeep(this.perPage);
        this.optionPages = [
          { value: limit, name: limit},
          { value: limit * 2, name: limit * 2},
          { value: limit * 5, name: limit * 5},
          { value: limit * 10, name: limit * 10},
        ];
      }
    },
    created() {
      this.getOptionPages();
    }
  }

  function range (start, chunk, total) {
    if( start + chunk > total) {
      start = Math.max(total - chunk + 1, 1);
    }
    var end = chunk > total ? total : chunk;
    return Array.apply(0, Array(end))
      .map(function (element, index) {
        return index + start
      })
  }
</script>

<style lang="scss" scoped>
  @import "./../../../sass/_variables";

  .VuePagination {
    margin: 0px 0px;
    border: 1px solid #363A47;
    border-top: none;
    width: 100%;
    padding: 6px 0px;

    .page_in {
      display: block;
      width: 130px;
      float: left;
      text-align: center;
      font-size: 14px;
      line-height: 18px;
      color: #6D6E71;
      margin-top: 6px;
    }

    .VuePagination__pagination {
      display: block;
      width: calc(100% - 130px);
      padding-right: 130px;
      float: left;
      text-align: center;
      margin: 0px;

      .VuePagination__pagination-item {
        display: inline-block;
        width: auto;
        min-width: 24px;
        height: 30px;
        outline: none;

        .page-link  {
          padding: 4px;
          background-color: transparent;
          border-radius: 0px;
          border: none;
          cursor: pointer;
          font-weight: 600;
          outline: none;
          box-shadow: none;

          img {
            margin-top: -2px;
          }
        }

        &.disabled {
          opacity: 0.3;
          cursor: default;
        }

        &.active {

          .page-link  {
            cursor: default;
            color: #ED1D24;
          }
        }
      }
    }
  }

</style>
