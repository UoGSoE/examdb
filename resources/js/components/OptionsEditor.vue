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
          :class="{'has-text-danger': hasError('external_deadline_glasgow')}"
        >Deadline for Glasgow paper submissions (staff are emailed 1week before and again 1day after the deadline if the paperwork isn't complete)</label>
        <div class="control">
          <input
            class="input"
            type="text"
            v-model="localOptions.internal_deadline_glasgow"
            v-pikaday="pikadayOptions"
          >
        </div>
      </div>
      <div class="field">
        <label
          class="label"
          :class="{'has-text-danger': hasError('internal_deadline_uestc')}"
        >Deadline for UESTC paper submissions (staff are emailed 1week before and again 1day after the deadline if the paperwork isn't complete)</label>
        <div class="control">
          <input
            class="input"
            type="text"
            v-model="localOptions.internal_deadline_uestc"
            v-pikaday="pikadayOptions"
          >
        </div>
      </div>

      <div class="field">
        <label
          class="label"
          :class="{'has-text-danger': hasError('internal_deadline_glasgow')}"
        >Date Glasgow Teaching office will be notified to look at papers before alerting externals</label>
        <div class="control">
          <input
            class="input"
            type="text"
            v-model="localOptions.external_deadline_glasgow"
            v-pikaday="pikadayOptions"
          >
        </div>
      </div>
      <div class="field">
        <label
          class="label"
          :class="{'has-text-danger': hasError('external_deadline_uestc')}"
        >Date UESTC Teaching office will be notified to look at papers before alerting externals</label>
        <div class="control">
          <input
            class="input"
            type="text"
            v-model="localOptions.external_deadline_uestc"
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
        external_deadline_glasgow: this.options.external_deadline_glasgow
          ? moment(
              this.options.external_deadline_glasgow,
              "YYYY-MM-DD"
            ).format("DD/MM/YYYY")
          : "",
        external_deadline_uestc: this.options.external_deadline_uestc
          ? moment(
              this.options.external_deadline_uestc,
              "YYYY-MM-DD"
            ).format("DD/MM/YYYY")
          : "",
        internal_deadline_glasgow: this.options.internal_deadline_glasgow
          ? moment(
              this.options.internal_deadline_glasgow,
              "YYYY-MM-DD"
            ).format("DD/MM/YYYY")
          : "",
        internal_deadline_uestc: this.options.internal_deadline_uestc
          ? moment(
              this.options.internal_deadline_uestc,
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