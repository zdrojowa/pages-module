<template>
    <button class="btn btn-info" type="button">Wybierz</button>
</template>

<script>
    export default {
        name: 'media-selector',
        props : {
            extensions: {
                type: String
            },
            data: {
                required: false,
                type: String,
                default: ''
            }
        },

        mounted: function() {

            var self = this;

            $(this.$el).mediaSelector({
                extensions: this.extensions
            }).on('media:selected', function(event, args) {
                self.$emit('media-selected', args.url);
                self.$emit('media-selected-event', args.url, self.data);
            });
        }
    }
</script>
