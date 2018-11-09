<template>
    <div v-on-clickaway="closePopup" style="position: relative;">
        <button class="button" @click.prevent="openPopup">
            <span class="icon">
                <i class="fas fa-user-plus"></i>
            </span>
            <span>
                Add External
            </span>
        </button>
            <transition name="fade" mode="in-out">
                <div class="box shadow-lg paper-box" style="" v-show="showPopupBox">
                    <form>
                        <div class="field">
                            <label class="label" for="email">Email Address</label>
                            <div class="control">
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
                                <button class="button is-info is-fullwidth" :class="{'is-loading': busy}" :disabled="fieldsInvalid" @click.prevent="submit">
                                    <span class="icon">
                                        <i class="fas fa-plus-circle"></i>
                                    </span>
                                    <span>
                                        Add User
                                    </span>
                                </button>
                            </div>
                        </div>
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
      user: {
        username: "",
        email: "",
        forenames: "",
        surname: "",
        lookedUp: false
      }
    };
  },

  computed: {
    fieldsInvalid() {
      return (
        this.user.email.match(/[a-z0-9]+@[a-z0-9]+\.[a-z0-9][a-z0-9]+/i) ===
          null ||
        this.user.forenames.match(/[a-z]/i) === null ||
        this.user.surname.match(/[a-z]/i) === null
      );
    }
  },

  methods: {
    openPopup() {
      this.showPopupBox = true;
    },
    closePopup() {
      this.showPopupBox = false;
    },
    submit() {
      this.user.username = this.user.email;
      this.busy = true;
      axios
        .post(route("user.store"), this.user)
        .then(response => {
          location.reload();
        })
        .catch(error => {
          console.log(error);
        });
    }
  }
};
</script>