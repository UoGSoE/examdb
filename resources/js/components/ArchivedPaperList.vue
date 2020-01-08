<template>
  <div>
    <div class="field">
      <div class="control">
        <input type="text" class="input" v-model="filter" placeholder="Filter..." />
      </div>
    </div>
    <div v-if="filteredPapers.length == 0">
        {{ filter ? 'No matches...' : 'None...' }}
    </div>
    <article class="media" v-for="paper in filteredPapers" :key="paper.id">
      <figure class="media-left has-text-centered">
        <a :href="getDownloadRoute(paper)" class="image is-64x64">
          <span class="icon is-large">
            <i :class="paper.icon + ' fa-3x'"></i>
          </span>
          <br />
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
            <br />
            <small>
              <strong>{{ paper.category }} - {{ paper.subcategory }}</strong>
            </small>
            <br />
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
    </article>
  </div>
</template>
<script>
export default {
  props: ["course", "papers"],
  computed: {
    filteredPapers() {
      if (!this.filter) {
        return this.papers;
      }
      const re = new RegExp(this.filter, "i");
      return this.papers.filter(paper => {
        if (re.test(paper.original_filename)) {
          return true;
        }
        if (re.test(paper.subcategory)) {
          return true;
        }
        return false;
      });
    }
  },
  data() {
    return {
      user_id: window.user_id,
      filter: ""
    };
  },
  methods: {
    getUserName(paper) {
      return paper.user
        ? paper.user.full_name
        : '<span class="tag">Disabled</span>';
    },
    getDownloadRoute(paper) {
      return route("archived.paper.show", paper.id);
    }
  }
};
</script>
