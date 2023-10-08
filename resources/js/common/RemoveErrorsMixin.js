import { head } from 'lodash'

export default {
  methods: {
    resetError () {
      this.errors.clear();
    },

    convertRemoteErrors(error, scope = null) {
      const errors = error.response.data.errors || {};
      for (const field in errors) {
        for (const error of errors[field]) {
          this.errors.add({field: field, msg: error, scope });
        }
      }

      if (!this.errors.any()) {
        this.errors.add({field: 'error', msg: 'Some error occurred. Please try again later'});
      }
    },

    processErrors (error, scope = null) {
      this.convertRemoteErrors(error, scope)
      if (this.errors.has('error')) {
        this.showError(error.response.data.message);
      }
    },

    processAndToastFirstError (error, scope = null) {
      this.convertRemoteErrors(error, scope)

      const _getFirstError = () => {
        const firstError = head(this.errors.items)
        return firstError.msg
      }

      this.showError(_getFirstError());

      // remove error
      this.errors.remove('error')
    }
  }
}
