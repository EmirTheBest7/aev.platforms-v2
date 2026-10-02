const messages = [
    "Are you sure?",
    "Really sure??",
    "Are you positive?",
    "Pookie please...",
    "Just think about it!",
    "If you say no, I will be really sad...",
    "I will be very sad...",
    "I will be very very very sad...",
    "Ok fine, I will stop asking...",
    "Just kidding, say yes please! ❤️"
];

let messageIndex = 0;

function handleNoClick() {
    const noButton = document.querySelector('.no-button');
    const yesButton = document.querySelector('.yes-button');
    noButton.textContent = messages[messageIndex];
    messageIndex = (messageIndex + 1) % messages.length;
    const currentSize = parseFloat(window.getComputedStyle(yesButton).fontSize);
    yesButton.style.fontSize = `${currentSize * 1.5}px`;
}

function handleYesClick() {
    // The original opened the next page, which sent a Telegram message with an embedded bot token.
    // Now the browser only asks this site's API (CSRF-protected, rate-limited); the token never leaves the server.
    var go = function () { window.location.href = "yes_page.html"; };
    if (!window.fetch) { go(); return; }

    fetch("/home/_api/csrf", { credentials: "same-origin" })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            return fetch("/home/_api/valentine/yes", {
                method: "POST",
                credentials: "same-origin",
                headers: { "X-CSRF-Token": data.token, "Accept": "application/json" }
            });
        })
        .catch(function () { /* the page must work even if the notification does not */ })
        .then(go);
}

document.querySelector('.yes-button').addEventListener('click', handleYesClick);
document.querySelector('.no-button').addEventListener('click', handleNoClick);
