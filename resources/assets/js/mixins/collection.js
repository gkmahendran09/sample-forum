export default {
    data() {
      return {
          items: []
      }
    },
    methods: {
        add(items) {
            this.items.push(items)

            this.$emit('added')
        },

        remove(index) {
            this.items.splice(index, 1)

            this.$emit('removed')
        }
    }
}