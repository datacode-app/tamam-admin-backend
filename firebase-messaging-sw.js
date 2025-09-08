importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');

firebase.initializeApp({
    apiKey: "AIzaSyDZIUBAwqInb1coDQPulQaloEhqF1KIh_U",
    authDomain: "tamam-35b98.firebaseapp.com",
    projectId: "tamam-35b98",
    storageBucket: "tamam-35b98.firebasestorage.app",
    messagingSenderId: "81567664901",
    appId: "1:81567664901:web:8ea043ca7aed6ba74a056c",
    measurementId: "G-RGB6KW73NN"
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function (payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body ? payload.data.body : '',
        icon: payload.data.icon ? payload.data.icon : ''
    });
});