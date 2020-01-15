<template>
  <div class="level-item">
    <button class="button" :class="{'is-loading': busy}" @click.prevent="exportChecklists" :disabled="done" v-text="buttonText"></button>
  </div>
</template>
<script>
export default {
  data() {
    return {
      done: false,
      busy: false,
      buttonText: "Export Checklists"
    };
  },
  methods: {
    exportChecklists() {
        this.busy = true;
      axios
        .post(route("checklist.bulk_download"))
        .then(res => {
          setTimeout(() => {
            this.buttonText = "Email will be sent when ready";
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