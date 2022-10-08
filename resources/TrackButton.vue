<template>
    <button @click="() => markAsRead(event)" class="text-red-500 hover:text-red-700  font-bold px-2 rounded">
        {{text}}
    </button>
</template>

<script>
    export default {
        props: ['event', 'context', 'text'],
        methods: {
            markAsRead(event) {
                this.$store.dispatch('track', {
                    event: this.event,
                    context: this.context,
                });
            }
        }
    }
</script>