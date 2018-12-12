<template>
  <div>
    <transition-group name="flash" mode="in-out" tag="span">
      <article class="media" v-for="paper in papers" :key="paper.id">
        <figure class="media-left has-text-centered">
          <a :href="getDownloadRoute(paper)" class="image is-64x64">
            <span class="icon is-large">
              <i :class="paper.icon + ' fa-3x'"></i>
            </span>
            <br>
            <span>{{ paper.formatted_size }}</span>
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
              <small>
                <strong>{{ paper.subcategory }}</strong>
              </small>
              <br>
              <span v-if="paper.comments && paper.comments.length > 0">
                <small>
                  <strong>{{ paper.user.full_name }}</strong>
                </small>
                <span class="icon is-small">
                  <i class="far fa-comment"></i>
                </span>
                {{ paper.comments[0].comment }}
              </span>
            </p>
          </div>
        </div>
        <div class="media-right">
          <button
            class="delete"
            title="Delete Paper"
            @click.prevent="openModal(paper)"
            v-if="paper.user_id == user_id"
          ></button>
        </div>
      </article>
    </transition-group>
    <portal to="portal-modal" v-if="showModal">
      <div class="modal is-active">
        <div class="modal-background"></div>
        <div class="modal-card">
          <header class="modal-card-head">
            <p class="modal-card-title">Confirm deleting paper</p>
            <button class="delete" aria-label="close" @click.prevent="closeModal"></button>
          </header>
          <section class="modal-card-body">
            Are you
            <strong>
              <em>sure</em>
            </strong> you want do delete this paper? This
            <em>cannot</em> be undone!
          </section>
          <footer class="modal-card-foot">
            <button class="button is-danger" @click="paperRemoved">Yes</button>
            <button class="button" @click.prevent="closeModal">Cancel</button>
          </footer>
        </div>
      </div>
    </portal>
  </div>
</template>
<script>
export default {
  props: ["course", "papers", "category"],
  data() {
    return {
      showModal: false,
      paperToDelete: null,
      user_id: window.user_id
    };
  },
  methods: {
    paperAdded(paper) {
      this.$emit("paper-added", paper);
    },
    paperRemoved() {
      this.$emit("paper-removed", this.paperToDelete);
      this.showModal = false;
      this.paperToDelete = null;
    },
    getDownloadRoute(paper) {
      return route("paper.show", paper.id);
    },
    openModal(paper) {
      this.paperToDelete = paper;
      this.showModal = true;
    },
    closeModal() {
      this.paperToDelete = null;
      this.showModal = false;
    }
  }
};
</script>