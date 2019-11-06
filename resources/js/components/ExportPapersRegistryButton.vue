<template>
    <div class="level-item">
        <button class="button" @click.prevent="exportPapers" :disabled="done" v-text="buttonText"></button>
    </div>
</template>
<script>
export default {
    data() {
        return {
            done: false,
            buttonText: 'Export Papers for Registry'
        }
    },
    methods: {
        exportPapers() {
            axios.post(route('export.paper.registry'))
                .then(res => {
                    this.done = true;
                    this.buttonText = 'Export started'
                })
                .catch(err => {
                    console.error(err);
                });
        }
    }
}
</script>