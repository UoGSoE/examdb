<template>
  <div v-on-clickaway="closePopup" style="position: relative;">
    <transition name="fade" mode="in-out">
      <div class="box shadow-lg paper-box" style v-show="show">
        <form>
          <label class="label">Pick a category and file</label>
          <div class="field has-addons">
            <div class="control is-expanded">
              <div class="select is-fullwidth">
                <select v-model="subcategory" required>
                  <option
                    v-for="(sub, index) in applicableSubcategories"
                    :key="`sub-${index}`"
                    :value="sub"
                    :disabled="sub == '---'"
                  >{{ sub }}</option>
                </select>
              </div>
            </div>
            <div class="control">
              <div class="file">
                <label class="file-label">
                  <input class="file-input" type="file" name="resume" ref="paper" />
                  <span class="file-cta">
                    <span class="file-icon">
                      <i class="fas fa-upload" />
                    </span>
                    <span class="file-label">Choose a file…</span>
                  </span>
                </label>
              </div>
            </div>
          </div>

          <div class="field">
            <div class="control">
              <textarea v-model="comment" class="textarea" placeholder="Optional Comments..."></textarea>
            </div>
          </div>

          <div class="field">
            <div class="control">
              <button
                class="button is-info is-fullwidth"
                :class="{'is-loading': busy}"
                @click.prevent="submit"
              >
                <span class="icon">
                  <i class="fas fa-upload"></i>
                </span>
                <span>Upload</span>
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
          <i class="far fa-question-circle"></i>
        </span>
        <span v-text="getButtonText"></span>
      </slot>
    </button>
  </div>
</template>

<script>
import { mixin as clickaway } from "vue-clickaway";

export default {
  props: ["course", "category", "subcategories", 'buttontext'],
  mixins: [clickaway],
  data() {
    return {
      comment: "",
      subcategory: "",
      show: false,
      busy: false,
      error: false,
      failed: false,
      errorMessage: '',
    };
  },
  computed: {
      getButtonText() {
          return 'Add ' + this.buttontext;
      },
    secondResit() {
      return this.category == "resit2";
    },
    applicableSubcategories() {
      if (this.secondResit) {
        return ["Second Resit File"];
      }
      return this.subcategories;
    }
  },
  methods: {
    closePopup() {
      this.show = false;
    },
    submit() {
      if (!this.$refs.paper.files[0]) {
        return false;
      }
      if (!this.subcategory) {
        return false;
      }
      this.busy = true;
      let data = this.getFormData();
      axios
        .post(route("course.paper.store", this.course.id), data)
        .then(response => {
          this.busy = false;
          this.show = false;
          this.comment = "";
          this.subcategory = "";
          this.failed = false;
          this.$emit("added", response.data);
        })
        .catch(error => {
          this.failed = true;
          this.busy = false;
          this.errorMessage = 'Could not upload paper...';
        });
    },
    getFormData() {
      let data = new FormData();
      data.set("category", this.category);
      data.set("subcategory", this.subcategory);
      data.set("comment", this.comment);
      data.set(
        "paper",
        this.$refs.paper.files[0],
        this.$refs.paper.files[0].name
      );
      return data;
    }
  }
};
</script>
