<template>
  <div id="users" class="page-container page">
    <div class="row user-page">
      <div class="col-md-8">
        <div class="panel left-pane">

          <div class="users-list block-item">
            <h4 class="uppercase">
              <img width="25" height="25" style="margin-right: 10px; margin-top: -7px;" src="/images/icons/icon-users-group.svg">
              List of users
            </h4>
            <div class="wrap-top">
              <div class="mb-3">
                <button class="btn-primary" @click.stop="onClickImport">Import Users</button>
                <button class="btn-primary" @click.stop="onClickExport">Export Users</button>
              </div>
              <div class="search-form">
                <input
                  type="text"
                  placeholder="Card ID"
                  class="input"
                  v-model="inputSearch" />
              </div>
            </div>

             <data-table2 :getData="getAllUsers"
                :limit="10"
                :column="6"
                :widthTable="'100%'"
                ref="datatable">
                <th>Username</th>
                <th>Card ID</th>
                <th>Employee ID</th>
                <th>Role</th>
                <th>Dept</th>
                <th>Action</th>
              <template slot="body" slot-scope="props">
                <tr>
                  <td>{{ props.item.login_name }}</td>
                  <td>{{ props.item.card_id }}</td>
                  <td>{{ props.item.employee_id }}</td>
                  <td>{{ props.item.role | formatUserRole }}</td>
                  <td>{{ props.item.dept }}</td>
                  <td class="small-col">
                    <a href="javascript:void(0)"
                        class="ml-4 mr-4" 
                        @click.stop="onClickUserDetail(props.item)"
                        v-if="canEditUser(props.item)">
                      <img width="25" height="25" src="/images/icons/icon-edit.svg">
                    </a>

                    <template v-if="isSuperAdmin || isAdmin">
                      <a href="javascript:void(0)"
                          @click.stop="onClickDeleteUser(props.item)"
                          v-if="visibleDeleteButton(props.item)">
                        <img width="25" height="25" src="/images/icons/icon-trash.svg">
                      </a>
                    </template>
                  </td>
                </tr>
              </template>
            </data-table2>
          </div>
        </div>
      </div>
      <div class="panel panel-right col-md-4">

        <div class="new-user block-item">
          <h4 class="uppercase">
            <img width="25" height="25" style="margin-right: 10px; margin-top: -7px;" src="/images/icons/icon-user-plus.svg">
            Add single user
          </h4>
          <div class="uppercase-label">
            <div class="field form-group">
              <label for="user_login_name" :class="{ 'color-red': errors.has('user-form.login_name') }">Username</label>
              <input class="form-control"
                  placeholder="User Login Name"
                  type="text" value=""
                  name="login_name"
                  data-vv-as="login name"
                  v-model="params.login_name"
                  v-validate="'required'"
                  data-vv-validate-on="none"
                  data-vv-scope="user-form"
                  :class="{ 'input-form-error': errors.has('user-form.login_name') }"
                  @focus="resetError">
              <span class="invalid-feedback"> {{ errors.first('user-form.login_name') }}</span>
            </div>
            <div class="field form-group">
              <label for="user_login_name" :class="{ 'color-red': errors.has('user-form.card_id') }">Card ID</label>
              <input class="form-control"
                  placeholder="Card ID"
                  type="text" value=""
                  name="card_id"
                  data-vv-as="card id"
                  v-model="params.card_id"
                  v-validate="'required'"
                  data-vv-validate-on="none"
                  data-vv-scope="user-form"
                  :class="{ 'input-form-error': errors.has('user-form.card_id') }"
                  @focus="resetError">
              <span class="invalid-feedback"> {{ errors.first('user-form.card_id') }}</span>
            </div>
            <div class="field form-group">
              <label for="user_employee_id" :class="{ 'color-red': errors.has('user-form.employee_id') }">Employee ID</label>
              <input class="form-control"
                  placeholder="Employee ID"
                  type="text" value=""
                  name="employee_id"
                  id="user_employee_id"
                  data-vv-as="employee id"
                  v-model="params.employee_id"
                  v-validate="'required'"
                  data-vv-validate-on="none"
                  data-vv-scope="user-form"
                  :class="{ 'input-form-error': errors.has('user-form.employee_id') }"
                  @focus="resetError">
              <span class="invalid-feedback"> {{ errors.first('user-form.employee_id') }}</span>
            </div>
            <div class="field form-group">
              <label for="user_password" :class="{ 'color-red': errors.has('user-form.password') }">Password</label>
              <input class="form-control"
                  placeholder="User Password"
                  type="password"
                  name="password"
                  id="user_password"
                  v-model="params.password"
                  v-validate="params.id ? 'min:6|max:100' : 'required|min:6|max:100'"
                  data-vv-validate-on="none"
                  data-vv-scope="user-form"
                  :class="{ 'input-form-error': errors.has('user-form.password') }"
                  @focus="resetError">
              <span class="invalid-feedback"> {{ errors.first('user-form.password') }}</span>
            </div>
            <div class="field form-group">
              <label for="user_role" :class="{ 'color-red': errors.has('user-form.role') }">Role</label>
              <select class="form-control"
                  placeholder="Select a User Role"
                  id="user_role"
                  v-model="params.role"
                  name="role"
                  v-validate="'required'"
                  data-vv-validate-on="none"
                  data-vv-scope="user-form"
                  :class="{ 'input-form-error': errors.has('user-form.role') }"
                  @focus="resetError">
                <option :value="role.value" v-for="role in roles">{{ role.name }}</option>
              </select>
              <span class="invalid-feedback"> {{ errors.first('user-form.role') }}</span>
            </div>
            <div class="field form-group">
              <label for="user_login_name" :class="{ 'color-red': errors.has('user-form.dept') }">Dept</label>
              <input class="form-control"
                  placeholder="Dept"
                  type="text" value=""
                  name="dept"
                  data-vv-as="dept"
                  v-model="params.dept"
                  v-validate="'required'"
                  data-vv-validate-on="none"
                  data-vv-scope="user-form"
                  :class="{ 'input-form-error': errors.has('user-form.dept') }"
                  @focus="resetError">
              <span class="invalid-feedback"> {{ errors.first('user-form.dept') }}</span>
            </div>
            <div class="field form-group">
              <label for="user_avatar" :class="{ 'color-red': errors.has('user-form.avatar') }">Avatar</label><br>
              <img class="mt-2 mb-2" width="100" style="margin-right: 10px; max-height: 150px" v-bind:src="avatar" v-if="avatar" >
              <input class="form-control"
                type="file"
                name="avatar"
                ref="avatarFile"
                @change="onChangeUpload" accept=".png, .jpg, .jpeg"
                @focus="resetError"
                data-vv-as="images file"
                data-vv-validate-on="none">
              <div class="note mt-2">Please choose .png, .jpg, .jpeg file</div>
              <span class="invalid-feedback"> {{ errors.first('user-form.avatar') }}</span>
            </div>
            <div class="field form-group">
              <label for="user_email" :class="{ 'color-red': errors.has('user-form.email') }">Email</label>
              <input class="form-control"
                     placeholder="Email"
                     type="text" value=""
                     name="email"
                     data-vv-as="email"
                     v-model="params.email"
                     v-validate="'email'"
                     data-vv-validate-on="none"
                     data-vv-scope="user-form"
                     :class="{ 'input-form-error': errors.has('user-form.email') }"
                     @focus="resetError">
              <span class="invalid-feedback"> {{ errors.first('user-form.email') }}</span>
            </div>
            <div class="actions">
              <input type="button"
                :value="isUserUpdate ? 'Update User' : 'Create User'"
                class="btn yellow-btn uppercase"
                @click.stop="onSaveUser" >

              <input type="button"
                value="Cancel"
                class="btn yellow-btn uppercase"
                @click.stop="onClickCancelUpdate"
                v-if="isUserUpdate" >
            </div>
          </div>
        </div>
      </div>
    </div>

    <users-import-modal @close="onUsersImporting" />
  </div>
</template>

<script>
  import rf from 'requestfactory'
  import { mapState } from 'vuex'
  import Const from 'common/Const'
  import RemoveErrorsMixin from 'common/RemoveErrorsMixin'
  import UsersImportModal from "./UsersImportModal";
  import {debounce} from "lodash";

  export default {
    components: {UsersImportModal},
    mixins: [RemoveErrorsMixin],

    data() {
      return {
        roles: Const.USER_ROLES,
        params: {},
        isUserUpdate: false,
        avatar: null,
        inputSearch: null,
      }
    },

    computed: {
      ...mapState(['user']),

      isSuperAdmin() {
        return this.user.role === Const.USER_ROLE_SUPER_ADMIN
      },

      isAdminSupport() {
        return this.user.role === Const.USER_ROLE_ADMIN_SUPPORT
      },

      isAdmin() {
        return this.user.role === Const.USER_ROLE_ADMIN
      }
    },

    watch: {
      user() {
        this.initRoles()
      },

      'params.user_id' (newValue) {
        this.isUserUpdate = newValue && !window._.isEmpty(`${newValue}`)
      },

      'params.login_name' (newValue) {
        if (window._.isEmpty(newValue)) {
          delete this.params.id
        }
      },

      inputSearch: debounce(function() {
        this.$nextTick(() => {
          this.$refs.datatable.refresh()
        })
      }, 300),
    },

    methods: {
      canEditUser(user) {
        const isOwner = this.user.id === user.id
        if (this.isAdminSupport) {
          const editable = user.role === Const.USER_ROLE_INSPECTOR || user.role === Const.USER_ROLE_STOREMAN
          return editable && !isOwner
        }
        if (this.isSuperAdmin) {
          return ! (user.role === Const.USER_ROLE_SUPER_ADMIN && !isOwner)
        }
        if (this.isAdmin) {
          return ! (user.role === Const.USER_ROLE_SUPER_ADMIN || (user.role === Const.USER_ROLE_ADMIN && !isOwner))
        }
        return true
      },

      visibleDeleteButton(user) {
        if (this.isSuperAdmin) {
          return user.role !== this.user.role
        }
        return user.role !== this.user.role
          && user.role !== Const.USER_ROLE_SUPER_ADMIN
      },

      initDefaultData() {
        this.initRoles()
        this.params = {
          user_id: null,
          login_name: null,
          password: null,
          role: Const.USER_ROLE_ADMIN_SUPPORT,
          avatar: null,
        }
        this.$refs.avatarFile.value=null
      },

      initRoles() {
        let roles = null
        switch (this.user.role) {
          case Const.USER_ROLE_SUPER_ADMIN:
            roles = Const.USER_ROLES
            break
          case Const.USER_ROLE_ADMIN_SUPPORT:
            roles = window._.filter(Const.USER_ROLES, item => {
              return item.value !== Const.USER_ROLE_SUPER_ADMIN
                && item.value !== Const.USER_ROLE_ADMIN
            })
            break
          case Const.USER_ROLE_ADMIN:
            roles = window._.filter(Const.USER_ROLES, item => {
              return item.value !== Const.USER_ROLE_SUPER_ADMIN
            })
            break
        }

        if (roles) {
          this.roles = roles
        }
      },

      getAllUsers(params) {
        params = {
          ...params,
          search_key: this.inputSearch,
        }
        return rf.getRequest('UserRequest').getAllUsers(params)
      },

      onClickUserDetail(user) {
        // this.params.id = user.id
        // this.params.user_id = user.id
        // this.params.login_name = user.login_name
        // this.params.login_name = user.login_name
        
        this.$refs.avatarFile.value=null
        this.params = {
          ...this.params,
          ...user,
          id: user.id,
          user_id: user.id,
          password: null,
          avatar: null
        }
        this.avatar = user.avatar;
      },

      onClickDeleteUser(user) {
        const _handler = () => {
          rf.getRequest('UserRequest').deleteUser({ id: user.id }).then(res => {
            this.showSuccess('Delete the user successfully')
            this.$refs.datatable.refresh()
          }).catch((error) => {
            console.error(error)
          })
        }

        this.confirmAction({ callback: _handler })
      },

      onClickCancelUpdate() {
        this.params = {}
      },

      onChangeUpload(e) {
        this.params.avatar = null;
        this.avatar = null;
        this.resetError();

        let files = e.target.files || e.dataTransfer.files;
        if (!files.length) {
          return;
        }

        this.params.avatar  = files[0];
      },

      async onSaveUser() {
        if (this.isSubmitting) {
          return
        }

        this.resetError()
        await this.$validator.validateAll('user-form')
        if (this.errors.any()) {
          return
        }

        this.startSubmit()
        this.submitRequest().then(res => {
          this.showSuccess()
          this.$refs.datatable.refresh()
          this.initDefaultData()
        }).catch(error => {
          this.processAndToastFirstError(error, 'user-form')
        })
        .finally(() => {
          this.endSubmit()
          this.resetError()
        })
      },

      submitRequest() {
        const formData = new FormData()
        for (let key in this.params) {
          if (this.params[key]) {
            formData.append(key, this.params[key]);
          }
        }

        if (this.params.id) {
          return rf.getRequest('UserRequest').updateAccount(formData)
        }
        return rf.getRequest('UserRequest').createNewAccount(formData)
      },

      onClickExport() {
        return rf.getRequest('UserRequest').exportUsers();
      },

      onClickImport() {
        const _handleSubmit = (formData) => {
          rf.getRequest('UserRequest').importUsers(formData)
            .then(res => {
              this.showSuccess('Successful!')
              this.$modal.hide('users-import-modal')
            })
            .catch(error => {
              // this.processErrors(error)
              this.processAndToastFirstError(error)
            })
        }

        this.$modal.show('users-import-modal', {
          href:'/imports/users_template.csv',
          handleSubmit: _handleSubmit
        })
      },

      onUsersImporting (complete) {
        this.$nextTick(() => {
          !!complete && this.$refs.datatable.refresh()
        })
      }
    },

    mounted() {
      this.initDefaultData()
    }
  }
</script>
<style lang="scss" scoped>
  @import "./../../../sass/common";

  #users {
    .user-page {
      .panel {
        .users-list {
          padding: 20px;
        }
        &.panel-right {
          background: none;
          box-shadow: none;
          padding: 0;
          .new-user {
            padding: 20px;
          }
        }
      }
      .uppercase {
        text-transform: uppercase;
      }
      .yellow-btn {
        @include btn-yellow();
      }
      h4 {
        font-size: 24px;
      }
      label {
        font-size: 16px;
        text-transform: uppercase;
      }
      .form-control {
        background-color: #11131D;
        height: 40px;
        color: #fff;
        font-size: 16px;
        line-height: 16px;
        padding: 10px 16px;
      }
      .block-item {
        background: #212430;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.25);
      }
    }
    .clearfix {
      content: "";
      clear: both;
      display: table;
    }
    .wrap-top {
      display: flex;
      justify-content: space-between;
      margin: 20px 0 10px 0;
      .search-form {
        width: 300px;
      }
    }
  }
</style>
