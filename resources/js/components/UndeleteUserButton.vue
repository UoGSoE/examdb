<template>
    <div>
        <button class="button is-small" @click.prevent="unDelete">
            Re-enable
        </button>
    </div>
</template>
<script>
export default {
  props: ["user"],
  data() {
    return {};
  },
  methods: {
    unDelete() {
      axios
        .post(route("admin.user.undelete", this.user.id))
        .then(response => {
          window.location = route('user.index');
        })
        .catch(error => {
          console.error(error);
        });
    }
  }
};
</script>