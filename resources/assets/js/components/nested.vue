<template>
    <div class="row item-conteiner">
        <draggable class="list-group" ghost-class="ghost" @input="emitter" :value="value">
            <div class="list-group-item" v-for="(element, index) in value" :key="key(element)">
                <div class="item clearfix">
                    <div class="float-left">
                        {{ element.name }}
                        <span v-if="element.label"> | {{ element.label }}</span>
                    </div>
                    <div class="float-right">
                        <button type="button" aria-label="Close" class="close" @click="remove(index)">×</button>

                        <b-button class="close mx-1" v-b-modal="key(element)">
                            <i class="mdi mdi-pencil"></i>
                        </b-button>
                    </div>
                </div>
                <modal :id="key(element)" :item="element" :lang="lang" @save="save(index, $event)"></modal>
            </div>
        </draggable>
    </div>
</template>

<script>
    export default {
        name: "nested",
        props: {
            value: {
                required: false,
                type: Array,
                default: null
            },
            lang: {
                required: false,
                type: String,
                default: 'pl'
            }
        },

        data() {
            return {
            };
        },

        methods: {

            remove(index) {
                this.value.splice(index, 1);
                this.$emit('input', this.value);
            },

            save(index, $event) {
                let element = this.value[index];

                this.$bvModal.hide(this.key(element));

                element.id        = $event.id;
                element.name      = $event.name;
                element.label     = $event.label;
                this.value[index] = element;

                this.$emit("input", this.value);
            },

            key(el) {
                return el.id + el.name + el.label;
            },

            emitter(value) {
                this.$emit("input", value);
            }
        }
    };
</script>
