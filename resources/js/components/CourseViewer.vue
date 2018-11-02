<template>
<div>
  <h2 class="title is-2 has-text-grey-dark">{{ theCourse.code }} {{ theCourse.title }}</h2>

  <div class="columns">
    <div class="column">

      <paper-heading :course="course" :subcategories="subcategories.main" category="main"></paper-heading>

      <paper-list :course="course" :papers="papers.main" category="main"></paper-list>

    </div><!-- /main-papers -->

    <div class="column">
      <h3 class="title has-text-grey">Resit</h3>
    </div><!-- /resit-papers-heading -->
  </div><!-- /resit-papers -->
</div>

</template>
<script>
export default {
  props: ["course", "papers", "subcategories"],
  data() {
    return {
      thePapers: this.papers,
      showModal: false,
      theCourse: this.course
    };
  },
  computed: {},
  methods: {
    approvalButtonText(category) {
      let key = `user_approved_${category}`;
      console.log(key);
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
      console.log(paper);
      this.thePapers[paper.category].unshift(paper);
    },
    getDownloadRoute(paper) {
      return route("paper.show", paper.id);
    },
    closeModal() {
      this.showModal = false;
    }
  }
};
</script>