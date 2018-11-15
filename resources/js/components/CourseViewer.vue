<template>
<div>
  <h2 class="title is-2 has-text-grey-dark">
    {{ theCourse.code }} {{ theCourse.title }}
  </h2>

  <span v-if="user.is_admin">
    <staff-course-editor v-if="user.is_admin" :staff="staff" :externals="externals" :course="theCourse"></staff-course-editor>
    <hr />
  </span>

  <div class="columns">
    <div class="column">

      <paper-heading :course="theCourse" :subcategories="subcategories" category="main" @paper-added="paperAdded" @approval-toggled="approvalToggled"></paper-heading>

      <paper-list :course="theCourse" :papers="thePapers.main" category="main" @paper-removed="paperRemoved"></paper-list>

    </div><!-- /main-papers -->

    <div class="column">
      <h3 class="title has-text-grey">Resit</h3>
    </div><!-- /resit-papers-heading -->
  </div><!-- /resit-papers -->
</div>

</template>
<script>
var distanceInWordsToNow = require("date-fns/distance_in_words_to_now");
export default {
  props: ["course", "papers", "subcategories", "user", "staff", "externals"],
  data() {
    return {
      thePapers: this.papers,
      theCourse: this.course
    };
  },
  mounted() {
    setInterval(() => {
      this.updateDiffForHumans();
    }, 60000);
  },
  computed: {},
  methods: {
    approvalButtonText(category) {
      let key = `user_approved_${category}`;
      if (this.theCourse[key]) {
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
    paperAdded(paper) {
      let tempPapers = this.thePapers;
      tempPapers[paper.category].unshift(paper);
      this.thePapers = tempPapers;
    },
    paperRemoved(paper) {
      axios
        .delete(route("paper.delete", paper.id))
        .then(response => {
          this.thePapers = response.data.papers;
        })
        .catch(error => {
          console.error(error);
        });
    },
    getDownloadRoute(paper) {
      return route("paper.show", paper.id);
    },
    closeModal() {
      this.showModal = false;
    },
    approvalToggled(course) {
      this.theCourse = course;
    },
    updateDiffForHumans() {
      this.thePapers.main.forEach(paper => {
        const date = new Date(Date.parse(paper.created_at));
        paper.diff_for_humans = `${distanceInWordsToNow(date)} ago`;
      });
    }
  }
};
</script>