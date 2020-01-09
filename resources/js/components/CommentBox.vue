<template>
  <div v-on-clickaway="closePopup" style="position: relative;">
    <transition name="fade" mode="in-out">
      <div class="box shadow-lg paper-box" style v-show="show">
        <form>
          <div class="field">
            <div class="control">
              <textarea v-model="comment" class="textarea" placeholder="Comments..."></textarea>
            </div>
          </div>

          <div class="field">
            <div class="control">
              <button
                class="button is-info is-fullwidth"
                :class="{'is-loading': busy}"
                @click.prevent="submit"
                :disabled="!this.comment"
              >
                <span class="icon">
                  <i class="far fa-comment"></i>
                </span>
                <span>Add Comment</span>
              </button>
              <div v-if="failed" class="notification is-danger" v-text="errorMessage"></div>
            </div>
          </div>
        </form>
      </div>
    </transition>
    <button
      class="button"
      slot="reference"
      @click.prevent="show = !show"
      :class="{'is-loading': busy}"
    >
      <slot name="button-content">
        <span class="icon has-text-info">
          <i class="far fa-comment"></i>
        </span>
        <span>Add Comment</span>
      </slot>
    </button>
  </div>
</template>

<script>
import { mixin as clickaway } from "vue-clickaway";

export default {
  props: ["course", "category"],
  mixins: [clickaway],
  data() {
    return {
      comment: "",
      show: false,
      busy: false,
      error: false,
      failed: false,
      errorMessage: ""
    };
  },
  methods: {
    closePopup() {
      this.show = false;
    },
    submit() {
      if (!this.comment) {
        return false;
      }
      this.busy = true;
      axios
        .post(route("course.comment.store", this.course.id), {
          category: this.category,
          comment: this.comment
        })
        .then(response => {
          this.busy = false;
          this.show = false;
          this.comment = "";
          this.failed = false;
          this.$emit("added", response.data);
        })
        .catch(error => {
          this.failed = true;
          this.busy = false;
          this.errorMessage = "Could not add comment";
        });
    }
  }
};
</script>