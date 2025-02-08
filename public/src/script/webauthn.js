/**
 * convert RFC 1342-like base64 strings to array buffer
 * @param {mixed} obj
 * @returns {undefined}
 */
function recursiveBase64StrToArrayBuffer(obj) {
    let prefix = '=?BINARY?B?';
    let suffix = '?=';
    if (typeof obj === 'object') {
        for (let key in obj) {
            if (typeof obj[key] === 'string') {
                let str = obj[key];
                if (str.substring(0, prefix.length) === prefix && str.substring(str.length - suffix.length) === suffix) {
                    str = str.substring(prefix.length, str.length - suffix.length);

                    let binary_string = window.atob(str);
                    let len = binary_string.length;
                    let bytes = new Uint8Array(len);
                    for (let i = 0; i < len; i++)        {
                        bytes[i] = binary_string.charCodeAt(i);
                    }
                    obj[key] = bytes.buffer;
                }
            } else {
                recursiveBase64StrToArrayBuffer(obj[key]);
            }
        }
    }
}

/**
 * Convert a ArrayBuffer to Base64
 * @param {ArrayBuffer} buffer
 * @returns {String}
 */
function arrayBufferToBase64(buffer) {
    let binary = '';
    let bytes = new Uint8Array(buffer);
    let len = bytes.byteLength;
    for (let i = 0; i < len; i++) {
        binary += String.fromCharCode( bytes[ i ] );
    }
    return window.btoa(binary);
}

$(document).ready(
    $('#webauthn-add').click(async () => {
        try {
            if ($('input[name=passkeyName]').val().length < 1) {
                alert('Please enter the PassKey name.');
                return false;
            }
    
            const options = JSON.parse($('#webauthnCredential').val());
    
            recursiveBase64StrToArrayBuffer(options);
    
            // WebAuthn API 사용
            const credential = await navigator.credentials.create(options);
    
            challenge = {
                authenticatorAttachment: credential.authenticatorAttachment,
                id: credential.id.replace(/-/g, '+').replace(/_/g, '/'),
                type: credential.type,
                rawId: credential.rawId ? arrayBufferToBase64(credential.rawId) : null,
                response: {
                    attestationObject: credential.response.attestationObject ? arrayBufferToBase64(credential.response.attestationObject) : null,
                    clientDataJSON: credential.response.clientDataJSON  ? arrayBufferToBase64(credential.response.clientDataJSON) : null,
                    getTransports: credential.response.getTransports ? credential.response.getTransports() : null
                }
            }
            
            var f = document.createElement('form');
            var i = document.createElement('input');
            var j = document.querySelector('input[name=passkeyName]').cloneNode(true);
            i.type = 'hidden';
            i.name = 'challenge';
            i.value = JSON.stringify(challenge);
            f.appendChild(i);
            f.appendChild(j);
            f.method = 'post';
            f.action = window.location.href;
            document.body.appendChild(f);
            console.log(f);
            f.submit();
        } catch (err) {
            if (err.name === "InvalidStateError")
                window.alert("통신 오류\nThe authenticator was previously registered")
            else
                window.alert('통신 오류\n' + err.message || 'unknown error occured');
        }
    }),
    $('#webauthnInput').click(async () => {
        try {
            const options = JSON.parse($('#webauthnCredential').val());

            recursiveBase64StrToArrayBuffer(options);

            // WebAuthn API 사용
            const credential = await navigator.credentials.get(options);

            challenge = {
                authenticatorAttachment: credential.authenticatorAttachment,
                id: credential.id.replace(/-/g, '+').replace(/_/g, '/'),
                type: credential.type,
                rawId: credential.rawId ? arrayBufferToBase64(credential.rawId) : null,
                response: {
                    attestationObject: credential.response.attestationObject ? arrayBufferToBase64(credential.response.attestationObject) : null,
                    clientDataJSON: credential.response.clientDataJSON  ? arrayBufferToBase64(credential.response.clientDataJSON) : null,
                    authenticatorData: credential.response.authenticatorData ? arrayBufferToBase64(credential.response.authenticatorData) : null,
                    signature: credential.response.signature ? arrayBufferToBase64(credential.response.signature) : null,
                    userHandle: credential.response.userHandle ? arrayBufferToBase64(credential.response.userHandle) : null
                }
            }

            var f = document.createElement('form');
            var i = document.createElement('input');
            i.type = 'hidden';
            i.name = 'challenge';
            i.value = JSON.stringify(challenge);
            f.appendChild(i);
            f.method = 'post';
            f.action = window.location.href;
            document.body.appendChild(f);
            console.log(f);
            f.submit();
        } catch (err) {
            window.alert('통신 오류\n' + err.message || 'unknown error occured');
        }
    }),
    $('#webauthnInput').trigger('click')
);