<template>
    <button class="btn btn-info" type="button">Wybierz</button>
</template>

<script>
    export default {
        name: 'media-selector',
        props : ['extensions'],

        mounted: function() {

            var self = this;

            $(this.$el).mediaSelector({
                extensions: this.extensions
            }).on('media:selected', function(event, args) {
                self.$emit('media-selected', args.url);
            });
        }
    }
</script>
