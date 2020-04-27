<template>
    <div>

        <slot></slot>

        <div class="card">

            <div class="card-header clearfix">
                <h4 class="card-title float-left"><i class="mdi mdi-collage"></i> Sekcji</h4>
                <div class="float-right">
                    <b-button variant="success" v-b-modal="'0'"><i class="mdi mdi-plus"></i> Dodaj</b-button>
                </div>
            </div>

            <div class="card-body">
                <nested v-model="sections" :lang="lang"/>
            </div>

            <div class="card-footer clearfix">
                <div class="float-right">
                    <b-button variant="primary" @click="save"><i class="mdi mdi-content-save"></i> Zapisz sekcje</b-button>
                </div>
            </div>
        </div>

        <modal id='0' :item="element" :lang="lang" @save="add"></modal>
    </div>
</template>

<script>

    export default {
        name: 'page-section',
        props: {
            id: {
                required: true,
                type: String
            },
            lang: {
                required: false,
                type: String,
                default: 'pl'
            }
        },

        data() {
            return {
                element: {id: 0, name: '', label: ''},
                sections: []
            };
        },

        created() {
            this.getSections();
        },

        computed: {

            url: function () {
                return '/dashboard/pages/' + this.id;
            }
        },

        methods: {

            getSections() {
                let self = this;
                axios.get('/dashboard/pages/get?id=' + self.id)
                    .then(res => {
                        if (typeof res.data.sections == 'undefined') {
                            self.sections = [];
                        } else {
                            self.sections = JSON.parse(res.data.sections);
                        }
                    }).catch(err => {
                    console.log(err)
                })
            },

            add($event) {
                this.sections.push({id: $event.id, name: $event.name, label: $event.label});
                this.$bvModal.hide('0');
            },

            save: function() {
                let self = this;

                let formData = new FormData();
                formData.append('_method', 'PUT');
                formData.append('sections', JSON.stringify(this.sections));

                axios.post(this.url, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                    .then(res => {
                    }).catch(err => {
                    console.log(err);
                });
            }
        }
    }
</script>
