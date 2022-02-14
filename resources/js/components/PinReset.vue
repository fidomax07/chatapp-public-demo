<template>
  <div class="row justify-content-center pt-5">
    <div
      id="cover-spin"
      v-show="resetStarted && !resetCompleted">
    </div>
    <div class="col-sm-8 col-md-6 col-lg-5 col-xl-4">
      <div class="card text-white pin-reset-card">
        <div class="card-header">PIN Reset</div>
        <div class="card-body">
          <div
            v-if="serverErrors"
            class="mb-3">
            <p class="text-muted mb-0">Server errors:</p>
            <div
              v-for="(val, prop) in serverErrors"
              :key="prop"
              class="ps-2">
              <small class="form-text text-muted">{{ val[0] }}</small>
            </div>
          </div>
          <form
            v-if="!resetCompleted"
            @submit.prevent="requestPinReset">

            <div class="mb-3">
              <label
                for="email"
                class="form-label">Admin email</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                <input
                  type="text"
                  class="form-control"
                  id="email"
                  maxlength="20"
                  ref="adminEmail"
                  placeholder="someone@example.com"
                  v-model="$v.form.admin_email.$model">
              </div>
              <div v-if="$v.form.admin_email.$dirty">
                <div>
                  <small
                    class="form-text text-muted"
                    v-if="!$v.form.admin_email.required">Admin email required
                  </small>
                </div>
                <div>
                  <small
                    class="form-text text-muted"
                    v-if="!$v.form.admin_email.email">Admin email should be a valid email
                  </small>
                </div>
              </div>
            </div>

            <div class="mb-5">
              <label
                for="admin_password"
                class="form-label">Admin password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                <input
                  type="password"
                  class="form-control"
                  id="admin_password"
                  v-model.lazy="$v.form.admin_password.$model">
              </div>
              <div v-if="$v.form.admin_password.$dirty">
                <div>
                  <small
                    class="form-text text-muted"
                    v-if="!$v.form.admin_password.required">Admin password is required
                  </small>
                </div>
                <div>
                  <small
                    class="form-text text-muted"
                    v-if="!$v.form.admin_password.minLength">Admin password must have at least {{
                      $v.form.admin_password.$params.minLength.min
                    }} characters
                  </small>
                </div>
              </div>
            </div>


            <div class="mb-3">
              <label
                for="phone"
                class="form-label">Phone number</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                <span class="input-group-text">{{ phoneCountryCode }}</span>
                <input
                  type="text"
                  class="form-control"
                  id="phone"
                  maxlength="20"
                  v-mask="'## ###-###'"
                  placeholder="4x 999 999"
                  v-model="$v.form.phone.$model">
              </div>
              <div v-if="$v.form.phone.$dirty">
                <div>
                  <small
                    class="form-text text-muted"
                    v-if="!$v.form.phone.required">Phone number is required
                  </small>
                </div>
                <div>
                  <small
                    class="form-text text-muted"
                    v-if="!$v.form.phone.minLength">Phone must have at least {{
                      $v.form.phone.$params.minLength.min - 2 /* minus space and dash of v-mask */
                    }} numbers
                  </small>
                </div>
                <div>
                  <small
                    class="form-text text-muted"
                    v-if="!$v.form.phone.maxLength">Phone must have maximum {{
                      $v.form.phone.$params.maxLength.max
                    }} numbers
                  </small>
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label
                for="pin"
                class="form-label">PIN</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input
                  type="text"
                  class="form-control"
                  maxlength="4"
                  id="pin"
                  v-mask="'####'"
                  placeholder="0000"
                  v-model="$v.form.pin.$model">
              </div>
              <div v-if="$v.form.pin.$dirty">
                <div>
                  <small
                    class="form-text text-muted"
                    v-if="!$v.form.pin.required">PIN is required
                  </small>
                </div>
                <div>
                  <small
                    class="form-text text-muted"
                    v-if="!$v.form.pin.numeric || !$v.form.pin.integer">PIN should have only integers
                  </small>
                </div>
                <div>
                  <small
                    class="form-text text-muted"
                    v-if="!$v.form.pin.minLength || !$v.form.pin.maxLength">PIN should have exactly {{
                      $v.form.pin.$params.minLength.min
                    }} digits
                  </small>
                </div>
              </div>
            </div>


            <div class="mb-3">
              <label
                for="pin_confirmation"
                class="form-label">PIN Confirmation</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input
                  type="text"
                  class="form-control"
                  maxlength="4"
                  id="pin_confirmation"
                  v-mask="'####'"
                  placeholder="0000"
                  v-model="$v.form.pin_confirmation.$model">
              </div>
              <div v-if="$v.form.pin_confirmation.$dirty">
                <div>
                  <small
                    class="form-text text-muted"
                    v-if="!$v.form.pin_confirmation.sameAsPin">PIN confirmation should match the PIN
                  </small>
                </div>
              </div>
            </div>

            <button
              type="submit"
              class="btn btn-dark">Submit
            </button>
          </form>

          <div
            v-else
            class=" pin-reset-success">
            <h5 class="card-title">
              <svg
                class="pin-reset-checkmark"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24">
                <path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.25 17.292l-4.5-4.364 1.857-1.858 2.643 2.506 5.643-5.784 1.857 1.857-7.5 7.643z" />
              </svg>
            </h5>
            <p class="card-text">You have successfully reset your PIN. <br> You can leave this page now.</p>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {email, integer, maxLength, minLength, numeric, required, sameAs} from 'vuelidate/lib/validators'

export default {
  data () {
    return {
      form: {
        admin_email: '',
        admin_password: '',
        phone: '',
        pin: '',
        pin_confirmation: ''
      },
      phoneCountryCode: '+383',
      serverErrors: null,
      resetStarted: false,
      resetCompleted: false
    }
  },
  validations: {
    form: {
      admin_email: {
        required,
        email
      },
      admin_password: {
        required,
        minLength: minLength(6)
      },
      phone: {
        required,
        minLength: minLength(10), // 8 + 2 -> (space and dash of v-mask)
        maxLength: maxLength(20)
      },
      pin: {
        required,
        numeric,
        integer,
        minLength: minLength(4),
        maxLength: maxLength(4)
      },
      pin_confirmation: {
        sameAsPin: sameAs('pin')
      }
    }
  },
  mounted () {
    this.$refs.adminEmail.focus()
  },
  methods: {
    async requestPinReset () {
      this.$v.$touch()
      if (this.$v.$invalid) {
        return
      }

      var response
      try {
        this.resetStarted = true
        //await new Promise((resolve) => setTimeout(() => resolve(), 3000))

        response = await axios.post('/api/admin/pin/reset', this.buildPayload())
      } catch (error) {
        this.serverErrors = error.response.status == 422
          ? error.response.data.errors
          : { message: ['Internal Error'] }
        this.resetStarted = false
        return
      }

      this.resetCompleted = true
      this.resetStarted = false
      this.serverErrors = null
      this.resetForm()
    },
    buildPayload () {
      return _.mapValues(this.form, (val, key) => {
        if (key == 'phone') {
          return `${this.phoneCountryCode} ${val}`
        }
        return val
      })
    },
    resetForm () {
      for (const prop in this.form) {
        this.form[prop] = ''
      }
    }
  }
}
</script>
