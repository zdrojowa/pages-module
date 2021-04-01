<template>
    <div>
        <slot></slot>

        <b-nav align="right">
            <b-nav-item>
                <b-button type="bytton" variant="primary" @click="save">Zapisz</b-button>
            </b-nav-item>
        </b-nav>

        <div class="row">

            <b-form-group label="Video">
                <media-selector extensions="3gp,3g2,asf,wmv,avi,divx,evo,f4v,flv,mp4,mpg,mpeg" @media-selected="selectVideo"></media-selector>
            </b-form-group>

            <b-container fluid class="p-4 bg-dark">
                <div v-for="(video ,i) in hiro_videos" :key="i" class="thumbnail-img">
                    <b-embed type="video" aspect="4by3" controls poster="poster.png">
                        <source :src="video" type="video/webm">
                        <source :src="video" type="video/mp4">
                    </b-embed>
                    <b-button type="button" @click="removeVideos(i)" variant="danger">
                        <b-icon-trash></b-icon-trash>
                    </b-button>
                </div>
            </b-container>
        </div>

        <div class="row">

            <b-form-group label="Slides">
                <media-selector extensions="jpg,jpeg,png" @media-selected="selectImages"></media-selector>
            </b-form-group>

            <b-container fluid class="p-4 bg-dark d-flex">
                <div v-for="(image ,i) in hiro_images" :key="i" class="thumbnail-img">
                    <b-img thumbnail fluid :src="image"></b-img>
                    <b-button type="button" @click="removeImage(i)" variant="danger">
                        <b-icon-trash></b-icon-trash>
                    </b-button>
                </div>
            </b-container>
        </div>
    </div>
</template>

<script>

    export default {
        name: 'hiro',
        props : ['_id'],

        data() {
            return {
                hiro_video: '',
                hiro_images: [],
                hiro_videos: [],
            };
        },

        created() {
            this.getHiro();
        },

        computed: {

            url() {
                return '/dashboard/pages/' + this._id;
            }
        },

        methods: {

            removeImage: function(position) {
                this.hiro_images.splice(position, 1);
            },

            removeVideos: function(position) {
                this.hiro_videos.splice(position, 1);
            },

            selectVideo: function(url) {
                this.hiro_videos.push(url);
            },

            selectImages: function(url) {
                this.hiro_images.push(url);
            },

            getHiro: function() {
                let self = this;
                axios.get('/dashboard/pages/get?id=' + self._id)
                    .then(res => {

                        if (typeof res.data.hiro_video == 'undefined') {
                            self.hiro_video = '';
                        } else {
                            self.hiro_video = res.data.hiro_video;
                        }

                        if (typeof res.data.hiro_images == 'undefined') {
                            self.hiro_images = [];
                        } else {
                            if (Array.isArray(res.data.hiro_images)) {
                                self.hiro_images = res.data.hiro_images;
                            } else {
                                self.hiro_images = JSON.parse(res.data.hiro_images);
                            }
                        }

                        if (typeof res.data.hiro_videos == 'undefined') {
                            self.hiro_videos = [];
                        } else {
                            if (Array.isArray(res.data.hiro_videos)) {
                                self.hiro_videos = res.data.hiro_videos;
                            } else {
                                self.hiro_videos = JSON.parse(res.data.hiro_videos);
                            }
                        }
                    }).catch(err => {
                    console.log(err)
                })
            },

            save: function() {
                let self = this;

                let formData = new FormData();
                formData.append('_method', 'PUT');
                formData.append('hiro_video', this.hiro_video);
                formData.append('hiro_videos', JSON.stringify(this.hiro_videos));
                formData.append('hiro_images', JSON.stringify(this.hiro_images));

                axios.post(this.url, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(res => {
                    this.$bvToast.toast('Hero zaktualizowane', {
                        title: `Hero`,
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
