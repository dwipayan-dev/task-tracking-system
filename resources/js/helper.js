import Swal from 'sweetalert2';
import axios from "axios";

export const request = async (method, url, data) => {
    const token = localStorage.getItem('APP_TOKEN')
    if (token !== undefined || token !== "") {
        const headers = {
            headers: {
                Authorization: 'Bearer ' + token,
                Accept: 'application/json',
            }
        }
        let response = null;
        switch (method) {
            case 'get':
                response = await axios.get(url, headers)
                break;
            case 'post':
                response = await axios.post(url, data, headers)
                break;
            case 'put':
                response = await axios.put(url, data, headers)
                break;
            case 'delete':
                response = await axios.delete(url, headers)
                break;
            default:
                break;
        }
        return response
    }
    return false
}
export const showPopup = async (title, message, status) => {
    Swal.fire({
        title: title,
        text: message,
        icon: status,
        confirmButtonText: 'OK',
    });
}

export const URL = "https://task-tracker.quocent.com"

export const getPermissions = () => {
    // Older browsers might not implement mediaDevices at all, so we set an empty object first
    if (navigator.mediaDevices === undefined) {
        navigator.mediaDevices = {};
    }

    // Some browsers partially implement mediaDevices. We can't just assign an object
    // with getUserMedia as it would overwrite existing properties.
    // Here, we will just add the getUserMedia property if it's missing.
    if (navigator.mediaDevices.getUserMedia === undefined) {
        navigator.mediaDevices.getUserMedia = function (constraints) {
            // First get ahold of the legacy getUserMedia, if present
            const getUserMedia =
                navigator.webkitGetUserMedia || navigator.mozGetUserMedia;

            // Some browsers just don't implement it - return a rejected promise with an error
            // to keep a consistent interface
            if (!getUserMedia) {
                showPopup("Error", "getUserMedia is not implemented in this browser", "error");
                return Promise.reject(
                    new Error("getUserMedia is not implemented in this browser")
                );
            }

            // Otherwise, wrap the call to the old navigator.getUserMedia with a Promise
            return new Promise((resolve, reject) => {
                getUserMedia.call(navigator, constraints, resolve, reject);
            });
        };
    }
    navigator.mediaDevices.getUserMedia =
        navigator.mediaDevices.getUserMedia ||
        navigator.webkitGetUserMedia ||
        navigator.mozGetUserMedia;

    return new Promise((resolve, reject) => {
        navigator.mediaDevices
            .getUserMedia({ video: true, audio: true })
            .then(stream => {
                resolve(stream);
            })
            .catch(err => {
                reject(err);
                //   throw new Error(`Unable to fetch stream ${err}`);
            });
    });
};