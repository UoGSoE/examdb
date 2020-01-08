<template>
    <div>
        <hr />
        <h3 class="title is-3 has-text-grey">Archives</h3>
        <archived-paper-list :papers="papers" :course="course" />
    </div>
</template>
<script>
import ArchivedPaperList from './ArchivedPaperList'

export default {
    props: ['course', 'papers'],
    components: {
        ArchivedPaperList,
    },
    data() {
        return {};
    },
}
</script>