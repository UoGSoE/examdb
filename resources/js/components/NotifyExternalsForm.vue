<template>
  <div>
    <div class="field">
      <div class="control">
        <label for="area" class="label">Choose which area to notify externals for</label>
        <label class="radio">
          <input type="radio" name="area" value="glasgow" v-model="area" required />
          Glasgow
        </label>
        <label class="radio">
          <input type="radio" name="area" value="uestc" v-model="area" required />
          UESTC
        </label>
      </div>
    </div>
    <hr />
    <notify-externals-button :area="area"></notify-externals-button>
  </div>
</template>
<script>
export default {
  data() {
    return {
      area: null
    };
  }
};
</script>