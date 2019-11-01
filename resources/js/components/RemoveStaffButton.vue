<template>
  <div>
    <button class="button" @click.prevent="showModal = true">Clear all staff from courses</button>
    <portal to="portal-modal" v-if="showModal">
      <div class="modal is-active">
        <div class="modal-background"></div>
        <div class="modal-card">
          <header class="modal-card-head">
            <p class="modal-card-title">Confirm clear all staff</p>
            <button class="delete" aria-label="close" @click.prevent="showModal = false"></button>
          </header>
          <section class="modal-card-body">
            Are you
            <strong>
              <em>sure</em>
            </strong> you want to remove <em>all</em> staff from <em>all</em> courses? This
            <em>cannot</em> be undone!
          </section>
          <footer class="modal-card-foot">
            <button class="button is-danger" @click.prevent="removeStaff">Yes</button>
            <button class="button" @click.prevent="showModal = false">Cancel</button>
          </footer>
        </div>
      </div>
    </portal>
  </div>
</template>
<script>
export default {
  props: ["user"],
  data() {
    return {
        showModal: false,
    };
  },
  methods: {
    removeStaff() {
      axios
        .post(route('admin.courses.clear_staff'))
        .then(response => {
          window.location = route('course.index');
        })
        .catch(error => {
          console.error(error);
        });
    }
  }
};
</script>