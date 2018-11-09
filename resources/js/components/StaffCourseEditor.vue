<template>
    <div class="columns">
        <div class="column">
            <h4 class="title is-4">
                Setters
            </h4>
            <v-select v-model="courseSetters" multiple :options="staff"></v-select>
        </div>
        <div class="column">
            <h4 class="title is-4">
                Moderators
            </h4>
            <v-select v-model="courseModerators" multiple :options="staff"></v-select>
        </div>
        <div class="column">
            <h4 class="title is-4">
                Externals
            </h4>
            <v-select v-model="courseExternals" multiple :options="externals"></v-select>
        </div>
    </div>
</template>
<script>
export default {
  props: ["staff", "externals"],
  data() {
    return {
      courseSetters: [],
      courseModerators: [],
      courseExternals: []
    };
  }
};
</script>