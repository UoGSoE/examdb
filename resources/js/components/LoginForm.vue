<template>
    <div class="loginbox">
        <div class="columns is-centered">

            <div class="column is-one-third">

                <div class="shadow-lg login-form">
                    <div class="login-header">
                        <h1 class="title is-1">ExamDB Login</h1>
                    </div>
                    <article style="background: #FF7777; color: white; text-align: center;" class="p-8" v-show="errorMessage">
                        <b>{{ errorMessage }}</b>
                    </article>
                    <transition name="fade" mode="out-in">
                    <article key="1" style="background: hsl(0, 0%, 100%); color: hsl(0, 0%, 21%); text-align: center;" class="p-8" v-if="successMessage">
                        {{ successMessage }}
                    </article>

                    <form key="2" method="POST" action="/login" class=" p-8 " v-if="!successMessage">
                        <div class="field">
                        <label class="label">Username <span class="has-text-grey has-text-weight-light">(or email for Externals)</span></label>
                        <p class="control">
                            <input class="input" type="text" name="username" v-model="username" autofocus>
                        </p>
                        </div>
                        <transition name="fade">
                            <div class="field" v-show="!isExternal">
                                <label class="label">Password</label>
                                <p class="control">
                                    <input class="input" type="password" name="password" v-model="password">
                                </p>
                            </div>
                        </transition>
                        <hr />
                        <div class="field">
                            <button class="button is-info is-fullwidth" :class="{'is-loading': busy}" v-html="loginButtonText" @click.prevent="login"></button>
                        </div>
                    </form>
                    </transition>
                </div>
            </div>

        </div>
    </div><!-- loginbox -->
</template>
<script>
export default {
  data() {
    return {
      username: "",
      password: "",
      busy: false,
      errorMessage: "",
      successMessage: ""
    };
  },

  computed: {
    isExternal() {
      return this.username.includes("@");
    },

    loginButtonText() {
      if (this.isExternal) {
        return `
          <span class="icon">
            <i class="far fa-envelope-open"></i>
          </span>
          <span>
            Send me a login link
          </span>
        `;
      }
      return "Log In";
    }
  },

  methods: {
    login() {
      this.busy = true;

      if (this.isExternal) {
        this.loginExternal();
        return;
      }

      axios
        .post("/login", { username: this.username, password: this.password })
        .then(response => {
          window.location.replace(route("home"));
        })
        .catch(error => {
          this.errorMessage = "Invalid username or password";
        });
    },

    loginExternal() {
      axios
        .post(route("external-generate-login"), { email: this.username })
        .then(response => {
          this.busy = false;
          this.successMessage =
            "Please check your email - you should recieve a secure login link shortly";
        })
        .catch(error => {
          this.errorMessage = "There was an unexpected error...";
        });
    }
  }
};
</script>