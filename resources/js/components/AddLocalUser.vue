<template>
    <div v-on-clickaway="closePopup" style="position: relative;">
        <button class="button" @click.prevent="openPopup">
            <span class="icon">
                <i class="fas fa-user-plus"></i>
            </span>
            <span>
                Add Local
            </span>
        </button>
            <transition name="fade" mode="in-out">
                <div class="box shadow-lg paper-box" style="" v-show="showPopupBox">
                    <form>
                        <label class="label" for="username">
                            Username (GUID)
                            <span v-if="searchError" v-text="searchError" class="has-text-danger"/>
                        </label>
                        <div class="field has-addons">
                            <div class="control">
                                <input type="text" class="input" v-model="user.username" id="username" @input="user.lookedUp = false">
                            </div>
                            <div class="control">
                                <button class="button is-info" :class="{'is-loading': searching}" @click.prevent="searchForUser">
                                    <span class="icon">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </button>
                            </div>
                        </div>
                        <transition name="plain-flash" mode="in-out">

                        <div v-show="user.lookedUp">
                            <div class="field">
                                <div class="control">
                                    <label class="label" for="email">Email</label>
                                    <input type="email" class="input" v-model="user.email" id="email">
                                </div>
                            </div>
                            <div class="field">
                                <div class="control">
                                    <label class="label" for="forenames">Forenames</label>
                                    <input type="forenames" class="input" v-model="user.forenames" id="forenames">
                                </div>
                            </div>
                            <div class="field">
                                <div class="control">
                                    <label class="label" for="surname">Surname</label>
                                    <input type="surname" class="input" v-model="user.surname" id="surname">
                                </div>
                            </div>
                            <div class="field">
                                <div class="control">
                                    <button class="button is-info is-fullwidth" :class="{'is-loading': busy}" :disabled="!user.lookedUp" @click.prevent="submit">
                                        <span class="icon">
                                            <i class="fas fa-plus-circle"></i>
                                        </span>
                                        <span>
                                            Add User
                                        </span>
                                    </button>
                                    <p v-if="createError" class="has-text-danger" v-text="createError" />
                                </div>
                            </div>
                        </div>
                        </transition>
                    </form>
                </div>
            </transition>
    </div>
</template>

<script>
import { mixin as clickaway } from "vue-clickaway";

export default {
  mixins: [clickaway],

  data() {
    return {
      showPopupBox: false,
      busy: false,
      searching: false,
      searchError: false,
      createError: false,
      user: {
        username: "",
        email: "",
        forenames: "",
        surname: "",
        lookedUp: false
      }
    };
  },

  computed: {},

  methods: {
    openPopup() {
      this.showPopupBox = true;
    },
    closePopup() {
      this.showPopupBox = false;
    },
    searchForUser() {
        axios.get(route('user.search', {guid: this.user.username}))
            .then(res => {
                this.user.username = res.data.user.username;
                this.user.email = res.data.user.email;
                this.user.forenames = res.data.user.forenames;
                this.user.surname = res.data.user.surname;
                this.user.lookedUp = true;
                this.searchError = false;
                this.createError = false;
                console.log(res)
            })
            .catch(err => {
                console.error(err);
                this.searchError = "Not found";
            });
    },
    submit() {
      this.busy = true;
      axios
        .post(route("user.store"), this.user)
        .then(response => {
          location.reload();
        })
        .catch(error => {
            if (error.response) {
                if (error.response.status == 422) { // validation error on the server
                    this.createError = error.response.data.errors;
                }
            }
            // this.createError = error.response ? error.response : 'Error :-(';
          console.log(error);
          this.busy = false;
        });
    }
  }
};
</script>