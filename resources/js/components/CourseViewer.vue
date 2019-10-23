<template>
<div>
  <div class="columns">
    <div class="column">
      <div class="level">
        <div class="level-left">
          <h2 class="title is-2 has-text-grey-dark level-item">
            {{ theCourse.code }} {{ theCourse.title }}
          </h2>
        </div>
        <div class="level-right">
          <a :href="archiveRoute" class="button level-item">Archive Papers</a>
        </div>
      </div>
      <p class="subtitle"><b>Note:</b> the system will only notify other people of any changes when you upload a Paper Checklist</p>

      <span v-if="user.is_admin">
        <staff-course-editor v-if="user.is_admin" :staff="staff" :externals="externals" :course="theCourse"></staff-course-editor>
        <hr />
      </span>
    </div>
    <div class="column is-one-quarter is-hidden-mobile" v-if="!user.is_admin">
      <div>
        <table class="table">
          <tbody>
            <tr>
              <th>Setters</th>
              <td>{{ course.setters.map(user => user.full_name).join(', ') }}</td>
            </tr>
            <tr>
              <th>Moderators</th>
              <td>{{ course.moderators.map(user => user.full_name).join(', ') }}</td>
            </tr>
            <tr>
              <th>Externals</th>
              <td>{{ course.externals.map(user => user.full_name).join(', ') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <hr />
  <div class="columns">
    <div class="column">

      <paper-heading :course="theCourse" :subcategories="subcategories" category="main" @paper-added="paperAdded" @approval-toggled="approvalToggled"></paper-heading>

      <paper-list :course="theCourse" :papers="thePapers.main" category="main" @paper-removed="paperRemoved"></paper-list>

    </div><!-- /main-papers -->

    <div class="column">
      <paper-heading :course="theCourse" :subcategories="subcategories" category="resit" @paper-added="paperAdded" @approval-toggled="approvalToggled"></paper-heading>

      <paper-list :course="theCourse" :papers="thePapers.resit" category="resit" @paper-removed="paperRemoved"></paper-list>
    </div><!-- /resit-papers-heading -->

    <div class="column" v-if="course.is_uestc">
      <paper-heading :course="theCourse" :subcategories="subcategories" category="resit2" @paper-added="paperAdded"></paper-heading>

      <paper-list :course="theCourse" :papers="thePapers.resit2" category="resit2" @paper-removed="paperRemoved"></paper-list>
    </div>
  </div><!-- /resit-papers -->
</div>

</template>
<script>
export default {
  props: ["course", "papers", "subcategories", "user", "staff", "externals"],
  data() {
    return {
      thePapers: this.papers,
      theCourse: this.course
    };
  },
  computed: {
    archiveRoute() {
      return route('course.papers.archive_form', this.course.id);
    }
  },
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
    }
  }
};
</script>