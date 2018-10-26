<template>
    <div class="loginbox">
        <div class="columns is-centered">

            <div class="column is-one-third">

                <div class="shadow-lg login-form">
                    <div class="login-header">
                        <h1 class="title is-1">ExamDB Login</h1>
                    </div>
                        <article style="background: #FF7777; color: white; text-align: center;" class="p-8" v-show="false">
                            <b>Incorrect username or password</b>
                        </article>

                    <form method="POST" action="/login" class=" p-8 ">
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
                            <button class="button is-info is-fullwidth" v-html="loginButtonText"></button>
                        </div>
                    </form>
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
      password: ""
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

  methods: {}
};
</script>