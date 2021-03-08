<template>
  <div>
    <transition-group name="paper-flash" mode="in-out" tag="span">
      <article class="media" v-for="paper in papers" :key="paper.id">
        <figure class="media-left has-text-centered">
          <a v-if="paper.subcategory != 'comment' && paper.subcategory != 'Updated Checklist'" :href="getDownloadRoute(paper)" class="image is-64x64">
            <span class="icon is-large">
              <i :class="paper.icon + ' fa-3x'"></i>
            </span>
            <br>
            <span>{{ paper.formatted_size }}</span>
          </a>
          <span v-else class="image is-64x64 has-text-grey-light">
            <span class="icon is-large">
              <i class="far fa-comment fa-3x"></i>
            </span>
          </span>
        </figure>
        <div class="media-content">
          <div class="content">
            <p>
              <a v-if="paper.subcategory != 'comment'" :href="getDownloadRoute(paper)">
                <strong>{{ paper.original_filename }}</strong>
              </a>
              <small>{{ paper.formatted_date }} ({{ paper.diff_for_humans }})</small>
              <br>
              <small v-if="paper.subcategory != 'comment'">
                  <span v-if="paper.subcategory == 'Updated Checklist'"><strong>{{ getUserName(paper) }} updated the checklist</strong></span>
                <strong v-else>{{ paper.subcategory }}</strong>
              </small>
              <br>
              <span v-if="paper.comments && paper.comments.length > 0">
                <small>
                  <strong v-html="getUserName(paper)"></strong>
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
            v-if="user_admin || (paper.user_id == user_id && recentlyUploaded(paper) && paper.subcategory != 'Updated Checklist')"
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
import parseISO from 'date-fns/parseISO';
import differenceInMinutes from 'date-fns/differenceInMinutes';

export default {
  props: ["course", "papers", "category"],
  data() {
    return {
      showModal: false,
      paperToDelete: null,
      user_id: window.user_id,
      user_admin: window.user_admin
    };
  },
  methods: {
    getUserName(paper) {
      return paper.user ? paper.user.full_name : '<span class="tag">Disabled</span>'
    },
    paperAdded(paper) {
      this.$emit("paper-added", paper);
    },
    paperRemoved() {
      this.$emit("paper-removed", this.paperToDelete);
      this.showModal = false;
      this.paperToDelete = null;
    },
    getDownloadRoute(paper) {
      if (paper.subcategory == 'comment' || paper.subcategory == 'Updated Checklist') {
        return '';
      }
      return route("paper.show", paper.id);
    },
    openModal(paper) {
      this.paperToDelete = paper;
      this.showModal = true;
    },
    closeModal() {
      this.paperToDelete = null;
      this.showModal = false;
    },
    recentlyUploaded(paper) {
      const paperDate = parseISO(paper.created_at);
      return differenceInMinutes(new Date(), paperDate) < 30;
    }
  }
};
</script>

<style scoped>
.paper-flash-enter-active, .paper-flash-leave-active {
  background-color: hsl(0, 0%, 100%);
  transition: all .7s;
  opacity: 100%;
}
.paper-flash-enter, .paper-flash-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
  background-color: hsl(171, 100%, 41%);
}

</style>
