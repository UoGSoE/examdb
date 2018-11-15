<template>
    <div>
        <button class="button is-small" :class="{'is-success': value.is_admin}" @click.prevent="toggle" title="Toggle admin status">
                Admin
        </button>
    </div>
</template>
<script>
export default {
  props: ["value"],
  data() {
    return {};
  },
  methods: {
    toggle() {
      axios
        .post(route("admin.toggle", this.value.id))
        .then(response => {
          console.log(response.data);
          this.$emit("update", response.data.user);
        })
        .catch(error => {
          console.error(error);
        });
    }
  }
};
</script>