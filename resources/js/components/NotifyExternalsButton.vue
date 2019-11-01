<template>
  <div>
    <button class="button" @click.prevent="showModal = true">Notify Externals</button>
    <portal to="portal-modal" v-if="showModal">
      <div class="modal is-active">
        <div class="modal-background"></div>
        <div class="modal-card">
          <header class="modal-card-head">
            <p class="modal-card-title">Confirm notify externals</p>
            <button class="delete" aria-label="close" @click.prevent="showModal = false"></button>
          </header>
          <section class="modal-card-body">
            Are you
            <strong>
              <em>sure</em>
            </strong> you want to notify the externals for {{ area }}?
          </section>
          <footer class="modal-card-foot">
            <button class="button is-danger" @click.prevent="notifyExternals">Yes</button>
            <button class="button" @click.prevent="showModal = false">Cancel</button>
          </footer>
        </div>
      </div>
    </portal>
  </div>
</template>
<script>
export default {
  props: ["area"],
  data() {
    return {
        showModal: false,
    };
  },
  methods: {
    notifyExternals() {
      axios
        .post(route('admin.notify.externals', {
            area: this.area
        }))
        .then(response => {
          window.location = route('paper.index');
        })
        .catch(error => {
          console.error(error);
        });
    }
  }
};
</script>