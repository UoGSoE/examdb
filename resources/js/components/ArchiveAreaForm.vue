<template>
  <div>
    <div class="field">
      <div class="control">
        <label for="area" class="label">Choose which area to archive</label>
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
    <archive-all-papers-button :area="area"></archive-all-papers-button>
  </div>
</template>
<script>
export default {
  data() {
    return {
      showModal: false,
      area: null
    };
  }
};
</script>