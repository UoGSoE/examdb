<template>
  <div class="level-item">
    <button class="button" :class="{'is-loading': busy}" @click.prevent="exportPapers" :disabled="done" v-text="buttonText"></button>
  </div>
</template>
<script>
export default {
  data() {
    return {
      done: false,
      busy: false,
      buttonText: "Export Papers for Registry"
    };
  },
  methods: {
    exportPapers() {
        this.busy = true;
      axios
        .post(route("export.paper.registry"))
        .then(res => {
          setTimeout(() => {
            this.buttonText = "Export started";
            this.done = true;
            this.busy = false;
          }, 1000);
        })
        .catch(err => {
          console.error(err);
        });
    }
  }
};
</script>