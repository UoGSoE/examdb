<template>
  <div>
    <h2 class="title is-2">Options</h2>

    <form method="POST">
      <div class="field">
        <label
          class="label"
          :class="{'has-text-danger': hasError('teaching_office_contact_glasgow')}"
        >Glasgow General Teaching Office Email</label>
        <div class="control">
          <input class="input" type="email" v-model="localOptions.teaching_office_contact_glasgow">
        </div>
      </div>

      <div class="field">
        <label
          class="label"
          :class="{'has-text-danger': hasError('teaching_office_contact_uestc')}"
        >UESTC General Teaching Office Email</label>
        <div class="control">
          <input class="input" type="email" v-model="localOptions.teaching_office_contact_uestc">
        </div>
      </div>

      <div class="field" v-for="option in optionList" :key="option.label">
        <label
          class="label"
          :class="{'has-text-danger': hasError(option.name)}"
          v-text="option.label"
        ></label>
        <div class="control">
          <input
            class="input"
            type="text"
            v-model="localOptions[option.name]"
            v-pikaday="pikadayOptions"
          >
        </div>
      </div>




      <hr />

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
    const optionList = [
        {
          label: 'Receive call for exam papers from admin staff',
          name: 'date_receive_call_for_papers'
        },
        {
          label: 'Deadline for Glasgow staff to submit exam materials to Management Database (staff are emailed 1 week before and again 1 day after the deadline if paperwork isn\'t complete)',
          name: 'glasgow_staff_submission_deadline'
        },
        {
          label: 'Deadline for UESTC staff to submit exam materials to Management Database (staff are emailed 1 week before and again 1 day after deadline if paperwork isn\'t complete)',
          name: 'uestc_staff_submission_deadline'
        },
        {
          label: 'Deadline for Internal moderation to be completed for UoG courses (staff are emailed 3 days before and again 1 day after the deadline if paperwork isn\'t complete)',
          name: 'glasgow_internal_moderation_deadline'
        },
        {
          label: 'Deadline for Internal moderation to be completed for UESTC courses (staff are emailed 3 days before and again 1 day after the deadline if paperwork isn\'t complete)',
          name: 'uestc_internal_moderation_deadline'
        },
        {
          label: 'Date UoG Teaching office will be notified to look at papers before alerting externals',
          name: 'date_remind_glasgow_office_externals'
        },
        {
          label: 'Date UESTC Teaching office will be notified to look at papers before alerting externals',
          name: 'date_remind_uestc_office_externals'
        },
        {
          label: 'Deadline for External moderation to be completed for UoG courses.',
          name: 'glasgow_external_moderation_deadline'
        },
        {
          label: 'Deadline for External moderation to be completed for UESTC courses',
          name: 'uestc_external_moderation_deadline'
        },
        {
          label: 'Deadline for print-ready version of UoG papers (UoG teaching office staff are emailed 1 day before and again 1 day after the deadline if paperwork isn\'t complete)',
          name: 'glasgow_print_ready_deadline'
        },
        {
          label: 'Deadline for print-ready version of UESTC papers (UESTC teaching office staff are emailed 1 days before and again 1 day after the deadline if the paperwork isn\'t complete)',
          name: 'uestc_print_ready_deadline'
        },
    ];
    let returnData = {
      localOptions: {
        teaching_office_contact_glasgow: this.options.teaching_office_contact_glasgow,
        teaching_office_contact_uestc: this.options.teaching_office_contact_uestc,
      },
      optionList: optionList,
      pikadayOptions: {
        format: "DD/MM/YYYY"
      },
      error: false,
      errors: []
    };
    optionList.each(opt => {
        returnData[opt.name] = this.options[opt.name]
          ? moment(
              this.options[opt.name],
              "YYYY-MM-DD"
            ).format("DD/MM/YYYY")
          : "";
    });
    return returnData;
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