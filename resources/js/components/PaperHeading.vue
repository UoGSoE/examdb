<template>
    <div class="level">
        <div class="level-left">

            <span class="level-item">
                <h3 class="title has-text-grey">
                    <span>{{ category | capitalize }}</span>
                </h3>
            </span>

            <span class="level-item" v-if="is_local">
                <main-paper-uploader
                    :course="course"
                    category="main"
                    :subcategories='subcategories.main'
                    @added="paperAdded"
                >
                </main-paper-uploader>
            </span>

            <span class="level-item" v-if="is_local">
                <main-paper-uploader
                    :course="course"
                    category="main"
                    :subcategories='subcategories.solution'
                    @added="paperAdded"
                >
                    <template slot="button-content">
                        <span class="icon has-text-success">
                        <i class="far fa-check-circle"></i>
                        </span>
                        <span>Add Solution</span>
                    </template>
                </main-paper-uploader>
            </span>

            <span class="level-item" v-if="is_external">
                <main-paper-uploader
                    :course="course"
                    category="main"
                    :subcategories='subcategories.external'
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

        <span class="level-item" v-if="is_local">
            <button class="button" @click.prevent="toggleApproval('main')" v-html="approvalButtonText('main')">
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
      axios
        .post(
          route("paper.approve", {
            course: this.theCourse.id,
            category: category
          })
        )
        .then(response => {
          console.log(response);
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