<template>
    <div>
        <slot></slot>

        <b-nav align="right">
            <b-nav-item>
                <b-button type="bytton" variant="success"v-b-modal="'0'">
                    <b-icon-plus></b-icon-plus> Dodaj
                </b-button>
            </b-nav-item>
            <b-nav-item>
                <b-button type="bytton" variant="primary" @click="save">Zapisz</b-button>
            </b-nav-item>
        </b-nav>

        <nested v-model="sections"/>

        <modal id='0' :item="element" @save="add"></modal>
    </div>
</template>

<script>

    export default {
        name: 'page-section',
        props: {
            id: {
                required: true,
                type: String
            }
        },

        data() {
            return {
                element: {id: 0, name: '', label: '', link: ''},
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
                            self.sections = res.data.sections;
                        }
                    }).catch(err => {
                    console.log(err)
                })
            },

            add($event) {
                this.sections.push({id: $event.id, name: $event.name, label: $event.label, link: $event.link});
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
                }).then(res => {
                    this.$bvToast.toast('Sekcje zaktualizowane', {
                        title: `Sekcje`,
                        variant: 'success',
                        solid: true
                    })
                }).catch(err => {
                    this.$bvToast.toast(err, {
                        title: `Błąd`,
                        variant: 'danger',
                        solid: true
                    })
                });
            }
        }
    }
</script>
