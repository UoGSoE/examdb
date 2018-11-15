<template>
    <div>
        <table class="table is-striped is-fullwidth is-hoverable">
            <thead>
                <tr>
                    <th>
                        Name
                    </th>
                    <th>
                        Username
                    </th>
                    <th>
                        Email
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in theUsers" :key="user.id">
                    <td>
                        <a href="showUser(user)">
                            <span v-if="user.is_external" class="icon has-text-info" title="External">
                                <i class="fas fa-globe-americas"></i>
                            </span>
                            {{ user.full_name }}
                        </a>
                    </td>
                    <td>
                        {{ user.username }}
                    </td>
                    <td>
                        <a :href="`mailto:${user.email}`">
                            {{ user.email }}
                        </a>
                    </td>
                    <td>
                        <div class="field is-grouped">
                            <admin-toggle-button :value="user" @update="update"></admin-toggle-button>
                            <impersonate-button :user="user"></impersonate-button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</template>
<script>
export default {
  props: ["users"],
  data() {
    return {
      theUsers: this.users
    };
  },
  methods: {
    showUser(user) {
      return route("user.show", user);
    },
    update(user) {
      let ix = this.theUsers.findIndex(u => u.id == user.id);
      this.theUsers = [
        ...this.theUsers.slice(0, ix),
        user,
        ...this.theUsers.slice(ix + 1)
      ];
      //   let tempUsers = this.theUsers;
      //   tempUsers.forEach((u, i) => {
      //     if (u.id == user.id) {
      //       tempUsers[i] = user;
      //       console.log(i);
      //     }
      //   });
      //   this.theUsers = tempUsers;
    }
  }
};
</script>