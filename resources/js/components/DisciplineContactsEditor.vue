<template>
  <div>
    <h2 class="title is-2">Discipline Contacts</h2>

    <form method="POST">
      <div v-for="(discipline, index) in disciplines" :key="discipline.id" class="field">
        <label class="label" :for="`discipline_contact_${discipline.title}`">{{ discipline.title }} Email</label>
        <div class="control">
          <input class="input" type="email" v-model="localDisciplines[index].contact" :id="`discipline_contact_${discipline.title}`"/>
        </div>
      </div>

      <hr />
      <div class="field">
        <div class="control">
          <button class="button" @click.prevent="save" :class="{ 'is-danger': error }">Save</button>
        </div>
      </div>
    </form>
  </div>
</template>
<script>
export default {
  props: ["disciplines"],
  data() {
    return {
      localDisciplines: this.disciplines,
      error: false,
      errors: []
    };
  },
  methods: {
    save() {
      axios
        .post(route("discipline.contacts.update"), {disciplines: this.localDisciplines.map(disc => {
            return {
                id: disc.id,
                contact: disc.contact,
            };
        })})
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