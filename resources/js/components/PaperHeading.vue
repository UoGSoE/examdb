<template>
    <div class="level">
        <div class="level-left">

            <span class="level-item">
                <h3 class="title has-text-grey">
                    <span>{{ category | capitalize }}</span>
                    <transition name="fade" mode="in-out">
                        <span v-if="!secondResit && isApproved" title="Approved" key="approved">
                            <span class="icon has-text-success">
                                <i class="fas fa-check"></i>
                            </span>
                        </span>
                    </transition>
                </h3>
            </span>

            <span class="level-item" v-if="is_local">
                <main-paper-uploader
                    :course="course"
                    :category="category"
                    :subcategories='subcategories["main"]'
                    @added="paperAdded"
                >
                </main-paper-uploader>
            </span>

            <span class="level-item" v-if="is_external">
                <main-paper-uploader
                    :course="course"
                    :category="category"
                    :subcategories='subcategories.external.main'
                    @added="paperAdded"
                >
                    <template slot="button-content">
                        <span class="icon has-text-success">
                        <i class="far fa-check-circle"></i>
                        </span>
                        <span>Add Main Comments</span>
                    </template>
                </main-paper-uploader>
            </span>

            <span class="level-item" v-if="is_external">
                <main-paper-uploader
                    :course="course"
                    :category="category"
                    :subcategories='subcategories.external.solution'
                    @added="paperAdded"
                >
                    <template slot="button-content">
                        <span class="icon has-text-success">
                        <i class="far fa-check-circle"></i>
                        </span>
                        <span>Add Solution Comments</span>
                    </template>
                </main-paper-uploader>
            </span>

        <span class="level-item" v-if="is_local && !secondResit">
            <button class="button" @click.prevent="toggleApproval(category)" v-html="approvalButtonText(category)">
            </button>
        </span>

    </div>
    </div><!-- /main-papers-heading -->
</template>
<script>
export default {
  props: ["course", "subcategories", "category"],
  filters: {
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
    }
  },
  data() {
    return {
      is_local: !window.is_external,
      is_external: window.is_external
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