<template>
    <VRow>
        <VCol cols="4">
            <v-sheet height="500">
                <v-sheet class="pa-3 bg-primary text-center">
                    <h2>User List</h2>
                </v-sheet>
                <div class="pa-2" id="online-user-list">
                    <div v-for="user in allusers" :key="user.id" @click="placeVideoCall(user.id, user.name)"
                        style="cursor:pointer;">
                        <v-list-item :prepend-avatar="avatar" :title="user.name">
                            <v-badge :color="getUserOnlineStatus(user.id) == 'Online' ? 'success' : 'error'" dot
                                floating location="left" class="pl-1">
                                &nbsp;&nbsp;{{
                        getUserOnlineStatus(user.id)
                    }}
                            </v-badge>
                        </v-list-item>
                        <v-divider inset></v-divider>
                    </div>
                </div>
            </v-sheet>
        </VCol>
        <v-divider inset vertical></v-divider>
        <VCol cols="8">
            <VCard class="text-sm-start" height="500">
                <div class="container">
                    <!--Placing Video Call-->
                    <div class="col-12 video-container" v-if="callPlaced">
                        <video ref="userVideo" muted playsinline autoplay class="cursor-pointer"
                            :class="isFocusMyself === true ? 'user-video' : 'partner-video'"
                            @click="toggleCameraArea" />
                        <video ref="partnerVideo" playsinline autoplay class="cursor-pointer"
                            :class="isFocusMyself === true ? 'partner-video' : 'user-video'" @click="toggleCameraArea"
                            v-if="videoCallParams.callAccepted" />
                        <div class="partner-video" v-else>
                            <div v-if="callPartner" class="column items-center q-pt-xl">
                                <div class="col q-gutter-y-md text-center">
                                    <p class="q-pt-md">
                                        <strong>{{ callPartner }}</strong>
                                    </p>
                                    <p>Calling...</p>
                                </div>
                            </div>
                        </div>
                        <div class="action-btns">
                            <v-btn density="comfortable" :icon="mutedAudio ? 'bx-microphone' : 'bx-microphone-off'"
                                color="secondary" @click="toggleMuteAudio" class="mr-2"></v-btn>
                            <!-- <button type="button" class="btn btn-info" @click="toggleMuteAudio">
                                {{ mutedAudio ? "Unmute" : "Mute" }}
                            </button> -->
                            <v-btn density="comfortable" :icon="mutedVideo ? 'bx-video' : 'bx-video-off'"
                                color="primary" @click="toggleMuteVideo" class="mr-2"></v-btn>
                            <!-- <button type="button" class="btn btn-primary mx-4" @click="toggleMuteVideo">
                                {{ mutedVideo ? "ShowVideo" : "HideVideo" }}
                            </button> -->
                            <v-btn density="comfortable" :icon="'bx-phone-off'" color="error" @click="endCall"></v-btn>
                            <!-- <button type="button" class="btn btn-danger" @click="endCall">
                                EndCall
                            </button> -->
                        </div>
                    </div>
                    <!-- End of Placing Video Call  -->

                    <!-- Incoming Call  -->
                    <div class="row" v-if="incomingCallDialog"
                        style="position: absolute;left: 50%;top:30%;transform: translate(-50%,50%);text-align: center;">
                        <div class="col">
                            <h3>
                                Incoming Call From
                            </h3>
                            <h2><strong>{{ callerDetails.name }}</strong></h2>
                            <div class="mt-2">
                                <v-btn density="comfortable" :icon="'bx-phone-off'" color="error" @click="declineCall"
                                    class="mr-3"></v-btn>
                                <!-- <button type="button" class="btn btn-danger" data-dismiss="modal" @click="declineCall">
                                    Decline
                                </button> -->
                                <v-btn density="comfortable" :icon="'bx-phone'" color="success"
                                    @click="acceptCall"></v-btn>
                                <!-- <button type="button" class="btn btn-success ml-5" @click="acceptCall">
                                    Accept
                                </button> -->
                            </div>
                        </div>
                    </div>

                    <div v-if="!callPlaced && !incomingCallDialog">
                        <h1 style="position: absolute;left: 50%;top:40%;transform: translate(-50%,50%)">
                            No One Available
                        </h1>
                    </div>
                    <!-- End of Incoming Call  -->
                </div>
            </VCard>

        </VCol>
    </VRow>
    <Loader :isLoading="loading"></Loader>

</template>

<script>
import Peer from "simple-peer";
import { getPermissions } from "../helper";
import { showPopup } from "../helper";
import axios from 'axios';
import Loader from "./../components/Loader.vue";
import avatar1 from '@images/avatar_white.gif'

export default {
    data() {
        return {
            avatar: avatar1,
            loading: false,
            allusers: [],
            authuserid: null,
            turn_url: null,
            turn_username: null,
            turn_credential: null,
            isFocusMyself: true,
            callPlaced: false,
            callPartner: null,
            mutedAudio: false,
            mutedVideo: false,
            videoCallParams: {
                users: [],
                stream: null,
                receivingCall: false,
                caller: null,
                callerSignal: null,
                callAccepted: false,
                channel: null,
                peer1: null,
                peer2: null,
            },
        };
    },

    mounted() {
        this.getDetails();
        this.initializeChannel();
        this.initializeCallListeners();
    },
    computed: {
        incomingCallDialog() {
            if (
                this.videoCallParams.receivingCall &&
                this.videoCallParams.caller !== this.authuserid
            ) {
                return true;
            }
            return false;
        },

        callerDetails() {
            console.log(this.videoCallParams.caller);

            if (
                this.videoCallParams.caller &&
                this.videoCallParams.caller !== this.authuserid
            ) {

                const incomingCaller = this.allusers.filter(
                    (user) => user.id === this.videoCallParams.caller
                );
                return {
                    id: this.videoCallParams.caller,
                    name: `${incomingCaller[0].name}`,
                };
            }
            return null;
        },
    },
    components: {
        Loader
    },
    methods: {
        getDetails() {
            this.loading = true
            const token = localStorage.getItem('APP_TOKEN')
            const headers = {
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                }
            }
            axios.get(this.$hostname + "/api/video/details", headers)
                .then(async response => {
                    this.allusers = response.data.result.allUsers
                    this.authuserid = response.data.result.authUserId
                    this.turn_url = response.data.result.turn_url
                    this.turn_username = response.data.result.turn_username
                    this.turn_credential = response.data.result.turn_credential

                    this.loading = false;
                })
                .catch(async error => {
                    this.loading = false
                    await showPopup('Error', error.response.data.message, error.response.data.status)
                });
        },

        initializeChannel() {
            this.videoCallParams.channel = window.Echo.join("presence-video-channel");
        },

        getMediaPermission() {
            return getPermissions()
                .then((stream) => {
                    this.videoCallParams.stream = stream;
                    if (this.$refs.userVideo) {
                        this.$refs.userVideo.srcObject = stream;
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        },

        initializeCallListeners() {
            this.videoCallParams.channel.here((users) => {
                this.videoCallParams.users = users;
            });

            this.videoCallParams.channel.joining((user) => {
                // check user availability
                const joiningUserIndex = this.videoCallParams.users.findIndex(
                    (data) => data.id === user.id
                );
                if (joiningUserIndex < 0) {
                    this.videoCallParams.users.push(user);
                }
            });

            this.videoCallParams.channel.leaving((user) => {
                const leavingUserIndex = this.videoCallParams.users.findIndex(
                    (data) => data.id === user.id
                );
                this.videoCallParams.users.splice(leavingUserIndex, 1);
            });
            // listen to incomming call
            this.videoCallParams.channel.listen("StartVideoChat", ({ data }) => {
                if (data.type === "incomingCall") {
                    // add a new line to the sdp to take care of error
                    const updatedSignal = {
                        ...data.signalData,
                        sdp: `${data.signalData.sdp}\n`,
                    };

                    this.videoCallParams.receivingCall = true;
                    this.videoCallParams.caller = data.from;
                    this.videoCallParams.callerSignal = updatedSignal;
                }
            });
        },
        async placeVideoCall(id, name) {
            this.callPlaced = true;
            this.callPartner = name;
            await this.getMediaPermission();
            this.videoCallParams.peer1 = new Peer({
                initiator: true,
                trickle: false,
                stream: this.videoCallParams.stream,
                config: {
                    iceServers: [
                        {
                            urls: this.turn_url,
                            username: this.turn_username,
                            credential: this.turn_credential,
                        },
                    ],
                },
            });
            const token = localStorage.getItem('APP_TOKEN')
            const headers = {
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                }
            }
            this.videoCallParams.peer1.on("signal", (data) => {
                // send user call signal
                axios
                    .post("/api/video/call-user", {
                        user_to_call: id,
                        signal_data: data,
                        from: this.authuserid,
                    }, headers)
                    .then(() => { })
                    .catch((error) => {
                        console.log(error);
                    });
            });

            this.videoCallParams.peer1.on("stream", (stream) => {
                console.log("call streaming");
                if (this.$refs.partnerVideo) {
                    this.$refs.partnerVideo.srcObject = stream;
                }
            });

            this.videoCallParams.peer1.on("connect", () => {
                console.log("peer connected");
            });

            this.videoCallParams.peer1.on("error", (err) => {
                console.log(err);
            });

            this.videoCallParams.peer1.on("close", () => {
                console.log("call closed caller");
            });

            this.videoCallParams.channel.listen("StartVideoChat", ({ data }) => {
                if (data.type === "callAccepted") {
                    if (data.signal.renegotiate) {
                        console.log("renegotating");
                    }
                    if (data.signal.sdp) {
                        this.videoCallParams.callAccepted = true;
                        const updatedSignal = {
                            ...data.signal,
                            sdp: `${data.signal.sdp}\n`,
                        };
                        this.videoCallParams.peer1.signal(updatedSignal);
                    }
                }
            });
        },

        async acceptCall() {
            this.callPlaced = true;
            this.videoCallParams.callAccepted = true;
            await this.getMediaPermission();
            this.videoCallParams.peer2 = new Peer({
                initiator: false,
                trickle: false,
                stream: this.videoCallParams.stream,
                config: {
                    iceServers: [
                        {
                            urls: this.turn_url,
                            username: this.turn_username,
                            credential: this.turn_credential,
                        },
                    ],
                },
            });
            const token = localStorage.getItem('APP_TOKEN')
            const headers = {
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                }
            }
            this.videoCallParams.receivingCall = false;
            this.videoCallParams.peer2.on("signal", (data) => {
                axios
                    .post("/api/video/accept-call", {
                        signal: data,
                        to: this.videoCallParams.caller,
                    }, headers)
                    .then(() => { })
                    .catch((error) => {
                        console.log(error);
                    });
            });

            this.videoCallParams.peer2.on("stream", (stream) => {
                this.videoCallParams.callAccepted = true;
                this.$refs.partnerVideo.srcObject = stream;
            });

            this.videoCallParams.peer2.on("connect", () => {
                console.log("peer connected");
                this.videoCallParams.callAccepted = true;
            });

            this.videoCallParams.peer2.on("error", (err) => {
                console.log(err);
            });

            this.videoCallParams.peer2.on("close", () => {
                console.log("call closed accepter");
            });

            this.videoCallParams.peer2.signal(this.videoCallParams.callerSignal);
        },
        toggleCameraArea() {
            if (this.videoCallParams.callAccepted) {
                this.isFocusMyself = !this.isFocusMyself;
            }
        },
        getUserOnlineStatus(id) {
            const onlineUserIndex = this.videoCallParams.users.findIndex(
                (data) => data.id === id
            );
            if (onlineUserIndex < 0) {
                return "Offline";
            }
            return "Online";
        },
        declineCall() {
            this.videoCallParams.receivingCall = false;
        },

        toggleMuteAudio() {
            if (this.mutedAudio) {
                this.$refs.userVideo.srcObject.getAudioTracks()[0].enabled = true;
                this.mutedAudio = false;
            } else {
                this.$refs.userVideo.srcObject.getAudioTracks()[0].enabled = false;
                this.mutedAudio = true;
            }
        },

        toggleMuteVideo() {
            if (this.mutedVideo) {
                this.$refs.userVideo.srcObject.getVideoTracks()[0].enabled = true;
                this.mutedVideo = false;
            } else {
                this.$refs.userVideo.srcObject.getVideoTracks()[0].enabled = false;
                this.mutedVideo = true;
            }
        },

        stopStreamedVideo(videoElem) {
            const stream = videoElem.srcObject;
            const tracks = stream.getTracks();
            tracks.forEach((track) => {
                track.stop();
            });
            videoElem.srcObject = null;
        },
        endCall() {
            // if video or audio is muted, enable it so that the stopStreamedVideo method will work
            if (!this.mutedVideo) this.toggleMuteVideo();
            if (!this.mutedAudio) this.toggleMuteAudio();
            this.stopStreamedVideo(this.$refs.userVideo);
            if (this.authuserid === this.videoCallParams.caller) {
                this.videoCallParams.peer1.destroy();
            } else {
                this.videoCallParams.peer2.destroy();
            }
            this.videoCallParams.channel.pusher.channels.channels[
                "presence-presence-video-channel"
            ].disconnect();

            setTimeout(() => {
                this.callPlaced = false;
            }, 3000);
        },
    },
};
</script>

<style scoped>


#video-row {
    width: 700px;
    max-width: 90vw;
}

#incoming-call-card {
    border: 1px solid #0acf83;
}

.video-container {
    width: 700px;
    height: 500px;
    max-width: 90vw;
    max-height: 500px;
    margin: 0 auto;
    position: relative;
}

.video-container .user-video {
    width: 25%;
    position: absolute;
    right: 10px;
    bottom: 50px;
    border: 1px solid #fff;
    border-radius: 6px;
    z-index: 2;
}

.video-container .partner-video {
    width: 100%;
    height: 100%;
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    top: 0;
    z-index: 1;
    margin: 0;
    padding: 0;
}

.video-container .action-btns {
    position: absolute;
    bottom: 20px;
    left: 50%;
    margin-left: -50px;
    z-index: 3;
    display: flex;
    flex-direction: row;
}

/* Mobiel Styles */
@media only screen and (max-width: 768px) {
    .video-container {
        height: 50vh;
    }
}
</style>