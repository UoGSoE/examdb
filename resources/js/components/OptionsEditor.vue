<template>
  <div>
    <h2 class="title is-2">Options</h2>

    <form method="POST">
      <div class="field">
        <label
          class="label"
          :class="{'has-text-danger': hasError('teaching_office_contact_glasgow')}"
        >Glasgow Teaching Office Contact</label>
        <div class="control">
          <input class="input" type="email" v-model="localOptions.teaching_office_contact_glasgow">
        </div>
      </div>

      <div class="field">
        <label
          class="label"
          :class="{'has-text-danger': hasError('teaching_office_contact_uestc')}"
        >UESTC Teaching Office Contact</label>
        <div class="control">
          <input class="input" type="email" v-model="localOptions.teaching_office_contact_uestc">
        </div>
      </div>

      <div class="field">
        <label
          class="label"
          :class="{'has-text-danger': hasError('main_deadline_glasgow')}"
        >Date Glasgow Externals will be notified to look at papers</label>
        <div class="control">
          <input
            class="input"
            type="text"
            v-model="localOptions.main_deadline_glasgow"
            v-pikaday="pikadayOptions"
          >
        </div>
      </div>
      <div class="field">
        <label
          class="label"
          :class="{'has-text-danger': hasError('main_deadline_uestc')}"
        >Date UESTC Externals will be notified to look at papers</label>
        <div class="control">
          <input
            class="input"
            type="text"
            v-model="localOptions.main_deadline_uestc"
            v-pikaday="pikadayOptions"
          >
        </div>
      </div>

      <hr>
      <div class="field">
        <div class="control">
          <button class="button" @click.prevent="save" :class="{ 'is-danger': error }">Save</button>
        </div>
      </div>
    </form>
  </div>
</template>
<script>
import V_Pikaday from "vue-pikaday-directive";
import moment from "moment";

export default {
  props: ["options"],
  directives: {
    pikaday: V_Pikaday
  },
  data() {
    return {
      localOptions: {
        teaching_office_contact_glasgow: this.options.teaching_office_contact_glasgow,
        teaching_office_contact_uestc: this.options.teaching_office_contact_uestc,
        main_deadline_glasgow: this.options.main_deadline_glasgow
          ? moment(
              this.options.main_deadline_glasgow,
              "YYYY-MM-DD"
            ).format("DD/MM/YYYY")
          : "",
        main_deadline_uestc: this.options.main_deadline_uestc
          ? moment(
              this.options.main_deadline_uestc,
              "YYYY-MM-DD"
            ).format("DD/MM/YYYY")
          : ""
      },
      pikadayOptions: {
        format: "DD/MM/YYYY"
      },
      error: false,
      errors: []
    };
  },
  methods: {
    save() {
      axios
        .post(route("admin.options.update", this.localOptions))
        .then(res => {
          console.log("saved");
          this.error = false;
        })
        .catch(err => {
          console.error(err);
          this.error = true;
          this.errors = err.response.data.errors;
        });
    },
    hasError(field) {
      if (!this.error) {
        return false;
      }
      if (this.errors[field]) {
        return true;
      }
      return false;
    }
  }
};
</script>