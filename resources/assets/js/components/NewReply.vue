<template>
<div>
    <div v-if="signedIn">
        <div class="form-group">
            <textarea class="form-control"
                      rows="5"
                      id="body"
                      name="body"
                      placeholder="Have something to say?"
                      v-model="body"
                      required></textarea>
        </div>
        <div class="form-group">
            <button type="submit" @click="addReply" class="btn btn-default">Post</button>
        </div>
    </div>
    <div v-else>
        <p class="text-center">Please <a href="/login">sign in</a> to participate in this
            discussion.</p>
    </div>
</div>
</template>
<script>
    import "jquery.caret";
    import "at.js";
    export default {
        data() {
            return {
                body: ''
            }
        },
        computed: {
            signedIn() {
                return window.App.signedIn
            },
        },
        mounted() {
            $("#body").atwho({
                at: "@",
                delay: 750,
                callbacks: {
                    remoteFilter: function(query, callback) {
                        $.getJSON("/api/users", {name: query}, function(usernames) {
                            callback(usernames);
                        });
                    }
                }
            });
        },
        methods: {
            addReply() {
                axios.post(location.pathname + '/replies', { 'body': this.body})
                    .catch(error => {
                        flash(error.response.data, 'danger')
                    })
                    .then(({ data }) => {
                        this.body = ''

                        flash('Your reply has been posted.')
                        this.$emit('created', data)
                    });

            }
        }

    }
</script>