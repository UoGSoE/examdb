<template>
<div class="mb-8">
    <div class="level">
        <div class="level-left">
          <div class="level-item">

            <h3 class="title has-text-grey">
                <span>{{ category | pretty | capitalize }}</span>
                <transition name="fade" mode="in-out">
                    <span v-if="!secondResit && isApproved" title="Approved" key="approved">
                        <span class="icon has-text-success">
                            <i class="fas fa-check"></i>
                        </span>
                    </span>
                </transition>

            </h3>
          </div>

            <div class="level-item">
              <a class="button" :href="checklistRoute + '?category=' + category">
                <span class="icon">
                  <i class="fas fa-tasks"></i>
                </span>
                <span>Paper Checklist</span>
              </a>
            </div>

            <span class="level-item" v-if="is_local && canUpload">
                <main-paper-uploader
                    :course="course"
                    :category="category"
                    :subcategories='subcategories["main"]'
                    @added="paperAdded"
                >
                </main-paper-uploader>
            </span>
            <span class="level-item" v-if="is_local && canUpload">
                <comment-box
                    :course="course"
                    :category="category"
                    @added="paperAdded"
                >
                </comment-box>
            </span>

            <span class="level-item" v-if="is_external && canUpload">
                <main-paper-uploader
                    :course="course"
                    :category="category"
                    :subcategories='subcategories["external"]'
                    @added="paperAdded"
                >
                    <template slot="button-content">
                        <span class="icon has-text-success">
                        <i class="far fa-check-circle"></i>
                        </span>
                        <span>Add Comments</span>
                    </template>
                </main-paper-uploader>
            </span>

        <span class="level-item" v-if="!secondResit && canUpload && (is_moderator || is_external)">
            <button class="button" @click.prevent="toggleApproval(category)" v-html="approvalButtonText(category)">
            </button>
        </span>

    </div>
    </div><!-- /main-papers-heading -->
    </div>
</template>
<script>
import CommentBox from './CommentBox';
export default {
  props: ["course", "subcategories", "category", "canUpload"],
  components: {
    CommentBox,
  },
  filters: {
    pretty(value) {
      if (value == 'resit2') {
        return '2nd Resit';
      }
      return value;
    },
    capitalize: function(value) {
      if (!value) return "";
      value = value.toString();
      return value.charAt(0).toUpperCase() + value.slice(1);
    }
  },
  computed: {
    isApproved() {
      return this.course[`user_approved_${this.category}`];
    },
    secondResit() {
      return this.category == 'resit2';
    },
  },
  data() {
    return {
      is_local: !window.is_external,
      is_external: window.is_external,
      is_moderator: window.is_moderator,
      checklistRoute: route('course.checklist.create', this.course.id),
    };
  },
  methods: {
    approvalButtonText(category) {
      let key = `user_approved_${category}`;
      if (this.course[key]) {
        return `<span class="icon">
                      <i class="fas fa-thumbs-down"></i>
                  </span>
                  <span>
                      Unapprove
                  </span>`;
      }
      return `<span class="icon">
                    <i class="fas fa-thumbs-up"></i>
                </span>
                <span>
                    Approve
                </span>`;
    },
    toggleApproval(category) {
      let routeName = "paper.approve";
      let key = `user_approved_${category}`;
      if (this.course[key]) {
        routeName = "paper.unapprove";
      }
      axios
        .post(
          route(routeName, {
            course: this.course.id,
            category: category
          })
        )
        .then(response => {
          this.$emit("approval-toggled", response.data.course);
        })
        .catch(error => {
          console.log(error);
        });
    },
    paperAdded(paper) {
      this.$emit("paper-added", paper);
    }
  }
};
</script>