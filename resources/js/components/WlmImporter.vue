<template>
    <div>
        <button class="button" @click.prevent="importData" :class="{'is-loading': busy}" :disabled="disabled">
            <span class="icon">
                <i class="fas fa-sync-alt"></i>
            </span>
            <span>
                {{ buttonText }}
            </span>
        </button>
    </div>
</template>
<script>
export default {
  props: [],
  data() {
    return {
      busy: false,
      disabled: false,
      buttonText: "Sync From WLM"
    };
  },
  methods: {
    importData() {
      this.busy = true;
      throw "Hello!";
      axios
        .post(route("wlm.import"))
        .then(response => {
          setTimeout(() => {
            this.buttonText = "Import started";
            this.disabled = true;
            this.busy = false;
          }, 1000);
        })
        .catch(error => {
          this.buttonText = "Something went wrong 😢";
          this.disabled = true;
          this.busy = false;
        });
    }
  }
};
</script>