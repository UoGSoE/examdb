<template>
<div>
  <h2 class="title is-2 has-text-grey-dark">{{ course.code }} {{ course.title }}</h2>

  <div class="columns">
    <div class="column">
      <div class="level">
        <div class="level-left">
          <span class="level-item">
            <h3 class="title has-text-grey">
              Main Exam
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
          <button class="button level-item">
            <span class="icon has-text-success">
              <i class="far fa-check-circle"></i>
            </span>
            <span>Add Solution</span>
          </button>
        </div>
      </div>
        <transition-group name="flash" tag="span">
            <article class="media" v-for="paper in thePapers.main" :key="paper.id">
                <figure class="media-left">
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
                            <small>{{ paper.user.full_name }}</small>
                            <small>{{ paper.formatted_date }}</small>
                            <br>
                            <span v-if="paper.comments.length > 0">
                                {{ paper.comments[0].comment }}
                            </span>
                        </p>
                    </div>
                    <nav class="level is-mobile">
                        <div class="level-left">
                            <a class="level-item">
                                <span class="icon is-small"><i class="fas fa-reply"></i></span>
                            </a>
                            <a class="level-item">
                                <span class="icon is-small"><i class="fas fa-retweet"></i></span>
                            </a>
                            <a class="level-item">
                                <span class="icon is-small"><i class="fas fa-heart"></i></span>
                            </a>
                        </div>
                    </nav>
                </div>
                <div class="media-right">
                    <button class="delete"></button>
                </div>
            </article>
        </transition-group>
    </div>
    <div class="column">
      <h3 class="title has-text-grey">Resit Exam</h3>
    </div>
  </div>
</div>
</template>
<script>
export default {
  props: ["course", "papers", "subcategories"],
  data() {
    return {
      thePapers: this.papers
    };
  },
  methods: {
    paperAdded(paper) {
      console.log(paper);
      this.thePapers[paper.category].unshift(paper);
    },
    getDownloadRoute(paper) {
      return route("paper.show", paper.id);
    }
  }
};
</script>