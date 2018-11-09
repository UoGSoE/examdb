<template>
    <div>
        <div class="columns">
            <div class="column">
                <h4 class="title is-4 has-text-grey">
                    Setters
                </h4>
                <v-select v-model="courseSetters" multiple :options="staff"></v-select>
            </div>
            <div class="column">
                <h4 class="title is-4 has-text-grey">
                    Moderators
                </h4>
                <v-select v-model="courseModerators" multiple :options="staff"></v-select>
            </div>
            <div class="column">
                <h4 class="title is-4 has-text-grey">
                    Externals
                </h4>
                <v-select v-model="courseExternals" multiple :options="externals"></v-select>
            </div>
        </div>
        <div class="field">
            <div class="control">
                <button class="button" @click.prevent="update" :class="{'is-loading': busy}">
                    <span class="icon">
                        <i class="fas fa-sync-alt"></i>
                    </span>
                    <span>
                        Update
                    </span>
                </button>
            </div>
        </div>
        <p v-if="error" class="has-text-danger">
            {{ error }}
        </p>
    </div>
</template>
<script>
export default {
  props: ["staff", "externals", "course"],
  data() {
    return {
      courseSetters: [],
      courseModerators: [],
      courseExternals: [],
      busy: false,
      error: ""
    };
  },
  mounted() {
    this.courseSetters = this.course.setters.map(this.extractSelectValues);
    this.courseModerators = this.course.moderators.map(
      this.extractSelectValues
    );
    this.courseExternals = this.course.externals.map(this.extractSelectValues);
  },
  methods: {
    extractSelectValues(user) {
      return {
        value: user.id,
        label: `${user.full_name} (${user.username})`
      };
    },
    update() {
      this.busy = true;
      this.error = "";
      axios
        .post(route("course.users.update", this.course.id), {
          setters: this.courseSetters.map(setter => setter.value),
          moderators: this.courseModerators.map(moderator => moderator.value),
          externals: this.courseExternals.map(external => external.value)
        })
        .then(response => {
          this.busy = false;
        })
        .catch(error => {
          console.error(error);
          this.error = "Error updating staff list";
        });
    }
  }
};
</script>