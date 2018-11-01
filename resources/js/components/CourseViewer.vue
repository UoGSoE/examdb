<template>
<div>
  <h2 class="title is-2 has-text-grey-dark">{{ course.code }} {{ course.title }}</h2>

  <div class="columns">
    <div class="column">
      <div class="level">
        <div class="level-left">
          <span class="level-item">
            <h3 class="title has-text-grey">
              Main
            </h3>
          </span>
          <span class="level-item">
            <main-paper-uploader
            :course="course"
            category="main"
            :subcategories='subcategories.main'
            @added="paperAdded"
            >
            </main-paper-uploader>
          </span>

           <span class="level-item">
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

          <span class="level-item">
              <button class="button" :class="{'is-outlined': !course.user_approved, 'is-success': course.user_approved}">
                  <span class="icon">
                      <i class="fas fa-thumbs-up"></i>
                  </span>
                  <span>
                      {{ approvalButtonText }}
                  </span>
              </button>
          </span>
        </div>
      </div>
        <transition-group name="flash" tag="span">
            <article class="media" v-for="paper in thePapers.main" :key="paper.id">
                <figure class="media-left has-text-centered">
                    <a :href="getDownloadRoute(paper)">
                        <span class="icon is-large">
                            <i :class="paper.icon + ' fa-3x'"></i>
                        </span>
                    </a>
                </figure>
                <div class="media-content">
                    <div class="content">
                        <p>
                            <a :href="getDownloadRoute(paper)">
                                <strong>{{ paper.original_filename }}</strong>
                            </a>
                            <small>{{ paper.formatted_date }} ({{ paper.diff_for_humans }})</small>
                            <br>
                            <small><strong>{{ paper.subcategory }}</strong></small>
                            <br />
                            <span v-if="paper.comments.length > 0">
                                <small><strong>{{ paper.user.full_name }}</strong></small>
                                <span class="icon is-small">
                                    <i class="far fa-comment"></i>
                                </span>

                                {{ paper.comments[0].comment }}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="media-right">
                    <button class="delete" title="Delete Paper" @click.prevent="showModal = true"></button>
                </div>
            </article>
        </transition-group>
    </div>
    <div class="column">
      <h3 class="title has-text-grey">Resit</h3>


    </div>
  </div>
<portal to="portal-modal" v-if="showModal">
    <div class="modal is-active">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Confirm deleting paper</p>
            <button class="delete" aria-label="close" @click.prevent="closeModal"></button>
            </header>
            <section class="modal-card-body">
                Are you <strong><em>sure</em></strong> you want do delete this paper?  This <em>cannot</em> be undone!
            </section>
            <footer class="modal-card-foot">
                <button class="button is-danger">Yes</button>
                <button class="button" @click.prevent="closeModal">Cancel</button>
            </footer>
        </div>
    </div>
</portal>
</div>

</template>
<script>
export default {
  props: ["course", "papers", "subcategories"],
  data() {
    return {
      thePapers: this.papers,
      showModal: false
    };
  },
  computed: {
    approvalButtonText() {
      if (this.course.user_approved) {
        return "Approved";
      }
      return "Approve?";
    }
  },
  methods: {
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