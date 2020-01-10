<template>
  <div>
    <div class="level">
      <div class="level-left">
        <h2
          class="title is-2 has-text-grey-dark level-item"
        >{{ theCourse.code }} {{ theCourse.title }}</h2>
      </div>
    </div>
    <div class="level" v-if="user.is_admin">
      <div class="level-left">
        <course-archive-papers-button :course="theCourse" class="level-item"></course-archive-papers-button>
        <a @click.prevent="disableCourse" class="button level-item">Disable Course</a>
        <a
          @click.prevent="notifyExternals"
          class="button level-item"
          :class="{'is-success': externalsNotified}"
          :disabled="externalsNotified"
          v-text="notifyButtonText"
        />
      </div>
    </div>
    <div class="columns">
      <div class="column">
        <p v-if="! is_external" class="subtitle">
          <b>Note:</b> the system will only notify other people of any changes when you upload a Paper Checklist
        </p>

        <span v-if="user.is_admin">
          <staff-course-editor
            v-if="user.is_admin"
            :staff="staff"
            :externals="externals"
            :course="theCourse"
          ></staff-course-editor>
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

    <div class="columns">
      <div class="column">
        <paper-heading
          :course="theCourse"
          :subcategories="subcategories"
          :can-upload="canUploadPapers"
          category="main"
          @paper-added="paperAdded"
          @approval-toggled="approvalToggled"
        ></paper-heading>

        <paper-list
          :course="theCourse"
          :papers="thePapers.main"
          category="main"
          @paper-removed="paperRemoved"
        ></paper-list>
      </div>
      <!-- /main-papers -->

      <div class="column">
        <paper-heading
          :course="theCourse"
          :subcategories="subcategories"
          :can-upload="canUploadPapers"
          category="resit"
          @paper-added="paperAdded"
          @approval-toggled="approvalToggled"
        ></paper-heading>

        <paper-list
          :course="theCourse"
          :papers="thePapers.resit"
          category="resit"
          @paper-removed="paperRemoved"
        ></paper-list>
      </div>
      <!-- /resit-papers-heading -->

      <div class="column" v-if="course.is_uestc">
        <paper-heading
          :course="theCourse"
          :subcategories="subcategories"
          :can-upload="canUploadPapers"
          category="resit2"
          @paper-added="paperAdded"
        ></paper-heading>

        <paper-list
          :course="theCourse"
          :papers="thePapers.resit2"
          category="resit2"
          @paper-removed="paperRemoved"
        ></paper-list>
      </div>
    </div>
    <!-- /resit-papers -->
  </div>
</template>
<script>
export default {
  props: ["course", "papers", "subcategories", "user", "staff", "externals"],
  data() {
    return {
      thePapers: this.papers,
      theCourse: this.course,
      is_external: window.is_external,
      externalsNotified: false
    };
  },
  computed: {
    disableRoute() {
      return route("course.disable", this.course.id);
    },
    notifyButtonText() {
      const suffix = this.course.external_notified ? ' Again' : '';
      return this.externalsNotified ? "Notified!" : "Notify Externals" + suffix;
    },
    canUploadPapers() {
      let inSetters = this.course.setters.find(
        setter => setter.id == this.user.id
      );
      let inModerators = this.course.moderators.find(
        moderator => moderator.id == this.user.id
      );
      let inExternals = this.course.externals.find(
        external => external.id == this.user.id
      );
      return inSetters || inModerators || inExternals;
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
    },
    disableCourse() {
      axios
        .post(route("course.disable", this.course.id))
        .then(res => {
          window.location = route("course.index");
        })
        .catch(err => {
          console.error(err);
        });
    },
    notifyExternals() {
      axios
        .post(route("admin.notify.externals_course", this.course.id))
        .then(res => {
          this.externalsNotified = true;
        })
        .catch(err => {
          console.error(err);
        });
    }
  }
};
</script>