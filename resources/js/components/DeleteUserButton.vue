<template>
    <div>
        <button class="button" @click.prevent="deleteUser">
            Disable User
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
    deleteUser() {
      axios
        .delete(route("admin.user.delete", this.user.id))
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