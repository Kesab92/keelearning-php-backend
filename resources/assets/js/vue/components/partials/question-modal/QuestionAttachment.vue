<template>
    <div class="attachment-container">
        <div v-if="attachment.type === 0">
            <img :src="attachment.url" />
        </div>
        <div v-else-if="attachment.type === 1">
            <audio controls>
                <source :src="attachment.url" type="audio/mp3">
            </audio>
        </div>
        <div v-else>
            <a :href="attachment.url" target="_blank">
                <small>Vorschau in einem neuen Fenster anzeigen</small>
            </a>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            attachment: {
                type: Object,
                required: true
            }
        }
    }
</script>

<style lang="scss" scoped>
    #app {
        .attachment-container {
            text-align: center;

            img {
                max-width: calc(100% - 52px);
                max-height: 250px;
            }
        }
    }
</style>