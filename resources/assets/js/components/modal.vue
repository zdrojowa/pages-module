<template>
    <b-modal :id="id" :title="modalTitle" hide-footer>

        <b-form-group label="Wybierz sekcję" class="mt-3">
            <multiselect v-model.lazy="section" track-by="id" label="name" placeholder="Zaczni pisać" :options="sections" :searchable="true" @search-change="getSections">
                <template slot="tag" slot-scope="{ option, remove }"><span class="custom__tag"><span>{{ option.name }}</span><span class="custom__remove" @click="remove(option)">❌</span></span></template>
            </multiselect>
        </b-form-group>

        <b-input-group prepend="Nazwa" class="mt-3">
            <b-form-input v-model="name"></b-form-input>
        </b-input-group>

        <b-input-group prepend="Etykieta" class="mt-3">
            <b-form-input v-model="label"></b-form-input>
        </b-input-group>

        <b-row>
            <b-col lg="4" class="pb-2"></b-col>
            <b-col lg="4" class="py-2">
                <b-button type="button" variant="primary" @click="save">{{ modalButton }}</b-button>
            </b-col>
        </b-row>
    </b-modal>
</template>

<script>
    export default {
        name: "modal",
        props:  {
            item: {
                required: false,
                type: Object,
                default: {id: 0, name: '', label: ''}
            },
            id: {
                required: false,
                type: String,
                default: ''
            }
        },

        data() {
            return {
                isNew: true,
                name: '',
                label: '',
                section: null,
                sections: []
            };
        },

        created() {
            this.isNew = this.item.id === 0;
            this.name  = this.item.name;
            this.label = this.item.label;

            if (!this.isNew) {
                this.getSection(this.item.id);
            }
        },

        computed: {
            modalTitle() {
                return this.isNew? 'Dodawanie' : 'Edytowanie';
            },

            modalButton() {
                return this.isNew ? 'Dodaj' : 'Zapisz';
            },
        },
        methods: {

            save() {
                this.$emit("save", {id: this.section.id, name: this.name, label: this.label});

                this.section = null;
                this.name    = '';
                this.label   = '';
            },

            getSection: function(id) {
                axios.get('/dashboard/pages-sections/get?id=' + id)
                    .then(res => {
                        this.section = res.data;
                    }).catch(err => {
                    console.log(err)
                })
            },

            getSections: function(query) {
                axios.get('/dashboard/pages-sections/get?query=' + query)
                    .then(res => {
                        this.sections = [];

                        res.data.forEach(item => {
                            this.sections.push(item);
                        });
                    }).catch(err => {
                    console.log(err)
                })
            }
        }
    };
</script>
