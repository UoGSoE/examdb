<template>
  <div>
    <button class="button" @click.prevent="showModal = true">Archive Papers</button>
    <portal to="portal-modal" v-if="showModal">
      <div class="modal is-active">
        <div class="modal-background"></div>
        <div class="modal-card">
          <header class="modal-card-head">
            <p class="modal-card-title">Confirm archive papers</p>
            <button class="delete" aria-label="close" @click.prevent="showModal = false"></button>
          </header>
          <section class="modal-card-body">
            Are you
            <strong>
              <em>sure</em>
            </strong> you want to archive <em>all</em> papers and checklists on this course? This
            <em>cannot</em> be undone!
          </section>
          <footer class="modal-card-foot">
            <button class="button is-danger" @click.prevent="archivePapers">Yes</button>
            <button class="button" @click.prevent="showModal = false">Cancel</button>
          </footer>
        </div>
      </div>
    </portal>
  </div>
</template>
<script>
export default {
  props: ["course"],
  data() {
    return {
        showModal: false,
    };
  },
  methods: {
    archivePapers() {
        console.log('here');
      axios
        .post(route('course.papers.archive', this.course.id))
        .then(response => {
            console.log('there');
            window.location.replace(route('course.show', this.course.id));
        })
        .catch(error => {
            console.log('whaaa');
          console.error(error);
        });
    }
  }
};
</script>