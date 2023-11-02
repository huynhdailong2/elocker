<template>
  <div class="item-form">
    <div class="block">
      <div class="text-center desc">Scan or Manual Input</div>
      <div class="form-search">
        <label>Service/WO:</label>
        <div>
          <input
            type="text"
            class="input"
            :class="{'error': errors.has('job_card')}"
            name="job_card"
            data-vv-as="service/mo"
            placeholder="Service/WO"
            v-model.trim="inputText"
            v-validate="'required'"
            @keypress.enter="onClickSearch"
            ref="inputText" >
            <span class="invalid-feedback ml-2" v-if="errors.has('job_card')">
              {{ errors.first('job_card') }}
            </span>
        </div>
        <button class="btn btn-primary" @click.stop="onClickSearch">Search</button>
      </div>

      <div class="form-input" v-if="visibleForm">
        <div class="row">
          <div class="col-6">
            <label>Service/WO#</label>
            <input
              type="text"
              class="input"
              :class="{'error': errors.has('input-form.card_num')}"
              name="card_num"
              data-vv-as="service/mo"
              placeholder="Service/WO"
              data-vv-scope="input-form"
              v-model="inputForm.card_num"
              v-validate="'required'" >
            <span class="invalid-feedback" v-if="errors.has('input-form.card_num')">
              {{ errors.first('input-form.card_num') }}
            </span>
          </div>
          <div class="col-6">
            <label>WO#&nbsp;&nbsp;&nbsp;&nbsp;</label>
            <input
              type="text"
              class="input"
              :class="{'error': errors.has('input-form.wo')}"
              name="wo"
              placeholder="WO#"
              data-vv-scope="input-form"
              v-model.trim="inputForm.wo"
              v-validate="'required'" >
            <span class="invalid-feedback" v-if="errors.has('input-form.wo')">
              {{ errors.first('input-form.wo') }}
            </span>
          </div>
        </div>
        <div class="row">
          <div class="col-6">
            <label>Vehicle No</label>
            <select
                class="input"
                :class="{'error': errors.has('input-form.vehicle_id')}"
                v-model="inputForm.vehicle_id"
                name="vehicle_id"
                data-vv-scope="input-form"
                data-vv-as="vehicle"
                v-validate="'required'" >
              <option :value="item.id" v-for="(item, index) in vehicles" :key="index">{{ item.vehicle_num }}</option>
            </select>
            <span class="invalid-feedback" v-if="errors.has('input-form.vehicle_id')">
              {{ errors.first('input-form.vehicle_id') }}
            </span>
          </div>
          <div class="col-6">
            <label>Vehicle Type&nbsp;&nbsp;&nbsp;&nbsp;</label>
            <input
              type="text"
              class="input"
              disabled
              v-model.trim="vehicleType" >
          </div>
        </div>
        <div class="row">
          <div class="col-6">
            <label>Platform&nbsp;&nbsp;&nbsp;&nbsp;</label>
            <input
              type="text"
              class="input"
              :class="{'error': errors.has('input-form.platform')}"
              name="platform"
              placeholder="Platform"
              data-vv-scope="input-form"
              v-model.trim="inputForm.platform"
              v-validate="'required'" >
            <span class="invalid-feedback" v-if="errors.has('input-form.platform')">
              {{ errors.first('input-form.platform') }}
            </span>
          </div>
        </div>
        <div>
          <button class="btn btn-primary" @click.stop="onClickSave">Save</button>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
  .item-form {
    .row {
      label {
        width: 130px;
      }
    }
    .form-search {
      display: flex;
      justify-content: center;
      align-items: baseline;
      .input {
        width: 350px;
        margin: 0 10px;
      }
      button {
        padding: 15px 40px;
        margin-top: 20px;
      }
    }
    .btn {
      margin-top: 20px;
      padding: 15px 30px;
    }
    .form-input {
      margin-top: 50px;
    }
  }
</style>
<script>
import rf from 'requestfactory'
import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
import { chain, isEmpty, debounce } from 'lodash'

export default {
  mixins: [RemoveErrorsMixin],

  data () {
    return {
      inputForm: {
        card_num: null,
        wo: null,
        vehicle_id: null,
        platform: null
      },
      vehicles: [],
      inputText: null,

      jobCard: null,
      isSearched: false
    }
  },

  computed: {
    vehicleType () {
      const result = chain(this.vehicles)
        .find(item => item.id === this.inputForm.vehicle_id)
        .value()
      return isEmpty(result) ? null : result.vehicle_type_name
    },

    visibleForm () {
      return this.isSearched && isEmpty(this.jobCard)
    }
  },

  watch: {
    inputText: debounce(function () {
      this.onClickSearch()
    }, 400)
  },

  mounted () {
    this.$refs.inputText.focus()
    this.getVehicles()
  },

  methods: {
    getVehicles() {
      const params = {
        no_pagination: true
      }
      rf.getRequest('VehicleRequest').getVehicles(params)
        .then(res => {
          this.vehicles = res.data || []
        })
    },

    async onClickSave () {
      const scope = 'input-form'
      this.errors.clear(scope)

      await this.$validator.validate(`${scope}.*`)
      if (this.errors.any()) {
        return
      }

      try {
        const res = await rf.getRequest('AdminRequest').createJobCard(this.inputForm)
        this.$emit('done', res.data)
        this.showSuccess('Successfully!');
      } catch (error) {
        this.processErrors(error, scope)
      }
    },

    async onClickSearch () {
      this.resetError()

      await this.$validator.validate('job_card')
      if (this.errors.any()) {
        return
      }

      try {
        const params = { card_num: this.inputText }
        const res = await rf.getRequest('AdminRequest').getJobCardByCardNumber(params)

        this.jobCard = res.data
        this.isSearched = true

        if (this.visibleForm) {
          this.inputForm.card_num = this.inputText
        } else {
          this.$emit('done', this.jobCard)
        }
      } catch (error) {
        this.processErrors(error, scope)
      }
    },
  }
}
</script>
