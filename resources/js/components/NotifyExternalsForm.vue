<template>
  <div>
    <div class="field">
      <div class="control">
        <label for="area" class="label"
          >Choose which area to notify externals for</label
        >
        <label
          class="radio"
          v-for="discipline in disciplines"
          :key="discipline.title"
        >
          <input
            type="radio"
            name="area"
            :value="discipline.title"
            v-model="area"
            required
          />
          {{ discipline.title }}
        </label>
      </div>
    </div>
    <hr />
    <notify-externals-button :area="area"></notify-externals-button>
  </div>
</template>
<script>
export default {
  props: ["disciplines"],
  data() {
    return {
      area: null,
    };
  },
};
</script>
