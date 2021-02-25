<template>
    <draggable
        v-bind="dragOptions"
        tag="div"
        class="item-container"
        :value="value"
        :list="list"
        @input="emitter"
    >
        <div class="item-group" :key="key(index, element)" v-for="(element, index) in realValue">
            <div class="item clearfix">
                <div class="float-left">
                    {{ element.name }}
                </div>
                <div class="float-right">
                  <b-button v-b-modal="key(index, element)" variant="primary">
                    <b-icon-pencil></b-icon-pencil>
                  </b-button>
                  <b-button variant="danger" @click="remove(index)">
                      <b-icon-trash></b-icon-trash>
                  </b-button>
                </div>
            </div>
            <b-modal :id="key(index, element)" title="Edycja" hide-footer>
                <b-row>
                    <b-col>
                        <b-form-group
                            label="Typ"
                        >
                            <b-form-select
                                v-model="element.type"
                                :options="types"
                            ></b-form-select>
                        </b-form-group>
                    </b-col>
                </b-row>
                <page-selector
                    v-if="element.type === 'page'"
                    :pages="pages"
                    :route="pageRoute"
                    :page="element.page"
                    @select="changePage(index, $event)"
                ></page-selector>
                <b-row v-else>
                    <b-col lg="6">
                        <b-form-group
                            label="Nazwa"
                        >
                            <b-form-input type="text" v-model="element.name"></b-form-input>
                        </b-form-group>
                    </b-col>
                    <b-col lg="6">
                        <b-form-group
                            label="Url"
                        >
                            <b-form-input type="text" v-model="element.url"></b-form-input>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-button variant="primary" @click="save" block>Zapisz</b-button>
                </b-row>
            </b-modal>
            <menu-structure
                class="item-sub"
                :list="element.items"
                :types="types"
                :pages="pages"
                :page-route="pageRoute"
            ></menu-structure>
        </div>
    </draggable>
</template>

<script>
    import draggable from 'vuedraggable'
    import PageSelector from "./PageSelector";
    export default {
        name: "menu-structure",
        props: {
            value: {
                required: false,
                type: Array,
                default: null
            },
            list: {
                required: false,
                type: Array,
                default: null
            },
            types: {
                required: false,
                type: Array,
                default: []
            },
            pages: {
                required: false,
                type: Array,
                default: []
            },
            pageRoute: {
                required: true,
                type: String
            }
        },

        components: {
            draggable,
            PageSelector
        },

        data() {
            return {
              items: []
            };
        },

        computed: {

            realValue() {
                return this.value ? this.value : this.list;
            },

            dragOptions() {
                return {
                    animation: 0,
                    group: "description",
                    disabled: false,
                    ghostClass: "ghost"
                };
            }
        },

        methods: {

            remove(index) {
                this.realValue.splice(index, 1);
                this.$emit('input', this.realValue);
            },

            changePage(index, $event) {
                this.realValue[index].page = $event.id
                this.realValue[index].name = $event.name
                this.$emit("input", this.realValue);
            },

            save(index, $event) {
                let element = this.realValue[index];

                this.$bvModal.hide(this.key(index, element));

                element.page          = $event.page;
                element.name          = $event.name;
                element.url           = $event.url;
                element.type          = $event.type;
                this.realValue[index] = element;

                this.$emit("input", this.realValue);
            },

            key(index, el) {
                return index + (el.page ? el.page : (el.name + el.url));
            },

            emitter(value) {
                this.$emit("input", value);
            }
        }
    };
</script>
