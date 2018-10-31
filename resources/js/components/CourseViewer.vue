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
            <ul>
                <li v-for="paper in papers.main" :key="paper.id">
                    {{ paper.filename }}
                    <span class="has-text-grey-light">{{ paper.created_at }}</span>
                </li>
            </ul>
        </div>
        <div class="column">
            <h3 class="title has-text-grey">Resit Exam</h3>
        </div>
    </div>
</div>
</template>
<script>
export default {
  props: ["course", "subcategories"],
  data() {
    return {
      papers: {
        main: [],
        resit: []
      }
    };
  },
  methods: {
    paperAdded(paper) {
      console.log(paper);
      this.papers[paper.category].unshift(paper);
    }
  }
};
</script>