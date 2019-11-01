<template>
  <div>
    <button class="button" @click.prevent="showModal = true">Anonymise User</button>
    <portal to="portal-modal" v-if="showModal">
      <div class="modal is-active">
        <div class="modal-background"></div>
        <div class="modal-card">
          <header class="modal-card-head">
            <p class="modal-card-title">Confirm anonymising user</p>
            <button class="delete" aria-label="close" @click.prevent="showModal = false"></button>
          </header>
          <section class="modal-card-body">
            Are you
            <strong>
              <em>sure</em>
            </strong> you want do anonymise this user? This
            <em>cannot</em> be undone!
          </section>
          <footer class="modal-card-foot">
            <button class="button is-danger" @click.prevent="anonymiseUser">Yes</button>
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
    anonymiseUser() {
      axios
        .post(route("gdpr.anonymise.user", this.user.id))
        .then(response => {
          window.location = route("user.show", this.user.id);
        })
        .catch(error => {
          console.error(error);
        });
    }
  }
};
</script>