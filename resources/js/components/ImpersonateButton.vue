<template>
    <div>
        <button class="button is-small" @click.prevent="impersonate">
            Impersonate
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
    impersonate() {
      axios
        .get(route("impersonate", this.user.id))
        .then(response => {
          window.location = "/home";
        })
        .catch(error => {
          console.error(error);
        });
    }
  }
};
</script>