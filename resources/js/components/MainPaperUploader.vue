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
                    v-for="(sub, index) in dropdownOptions"
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
          <p class="help">Max file size: 20MB</p><br>

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
      @click.prevent="openDropdown"
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
      dropdownOptions: [],
    };
  },
  computed: {
      getButtonText() {
          let wording = '';
          if (this.buttontext === 'Main') {
              wording = 'Exam Paper';
          }
          if (this.buttontext === 'Solution') {
              wording = 'Solution';
          }
          if (this.buttontext === 'Assessment > 30% (> 25% UESTC)') {
              wording = 'Assessment > 30% (> 25% UESTC)';
          }
          return 'Add ' + wording;
      },
    secondResit() {
        console.log(this.category);
      return this.category == "resit2";
    },
  },
  methods: {
      openDropdown() {
        this.show = !this.show;
        this.getApplicableSubcategories();
      },
    getApplicableSubcategories() {
      if (this.secondResit) {
          console.log('FFFFFFFFFFF');
        this.dropdownOptions = ["Paper For Registry", "Solution For Archive"];
        return;
      }
      console.log('NOT 2nd Resit');
      console.log('here');
      axios.get(
          route('api.course.paper_options', this.course.code) + `?category=${this.category}&subcategory=${this.buttontext.toLowerCase()}`,
          {'headers': {'x-api-key': window.api_key}}
        )
        .then(res => {
            console.log('fred');
            this.dropdownOptions = res.data.data;
        }).catch(err => {
            console.log(err);
        });
      console.log('there');
    },
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
