<script>
    import Replies from '../components/Replies.vue'
    export default {
        props: ['initialRepliesCount'],

        components: {
            'replies': Replies
        },

        data() {
            return {
                repliesCount: this.initialRepliesCount
            }
        }
    }
</script>