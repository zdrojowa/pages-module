<template>
    <div>

        <slot></slot>

        <div class="card mb-2">
            <div class="card-header">
                <h4><i class="mdi mdi-image"></i> Hiro</h4>
            </div>

            <div class="card-body row">

                <div class="form-group col-lg-4">
                    <label>Video</label>

                    <media-selector extensions="3gp,3g2,asf,wmv,avi,divx,evo,f4v,flv,mp4,mpg,mpeg" @media-selected="selectVideo"></media-selector>

                    <button @click="removeVideo" class="btn-danger" type="button">
                        <i class="mdi mdi-delete"></i>
                    </button>

                    <a v-if="hiro_video" :href="hiro_video" target="_blank">
                        <div class="thumbnail">
                            <i class="mdi mdi-file-video-outline text-white"></i>
                        </div>
                    </a>
                </div>

                <div class="form-group col-lg-8">
                    <label>Images</label>

                    <media-selector extensions="jpg,jpeg,png" @media-selected="selectImages"></media-selector>

                    <div class="img-preview">
                        <div v-for="(image ,i) in hiro_images" :key="i" class="thumbnail-img">
                            <button @click="removeImage(i)" class="btn-danger" type="button">
                                <i class="mdi mdi-delete"></i>
                            </button>
                            <img :src="image"/>
                        </div>
                    </div>
                </div>
            </div>
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
                hiro_images: []
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
                this.save();
            },

            removeVideo: function(position) {
                this.hiro_video = '';
                this.save();
            },

            selectVideo: function(url) {
                this.hiro_video = url;
                this.save();
            },

            selectImages: function(url) {
                this.hiro_images.push(url);
                this.save();
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
                            self.hiro_images = JSON.parse(res.data.hiro_images);
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
                formData.append('hiro_images', JSON.stringify(this.hiro_images));

                axios.post(this.url, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                    .then(res => {
                        // self.getHiro();
                    }).catch(err => {
                    console.log(err);
                });
            }
        }
    }
</script>
