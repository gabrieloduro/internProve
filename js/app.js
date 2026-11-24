// app.js - the only JavaScript in the project. Three tiny jobs.

// 1. Ask "are you sure?" before forms that have data-confirm
var forms = document.querySelectorAll('form[data-confirm]');
for (var i = 0; i < forms.length; i++) {
    forms[i].addEventListener('submit', function (event) {
        if (!confirm(this.getAttribute('data-confirm'))) {
            event.preventDefault();   // stop the form from being sent
        }
    });
}

// 2. Character counter under the description box
var box = document.getElementById('description');
var counter = document.getElementById('counter');
if (box && counter) {
    var update = function () {
        counter.textContent = box.value.length + ' / 1000';
    };
    box.addEventListener('input', update);
    update();
}

// 3. Fade out green success messages after 8 seconds (long enough to read)
var messages = document.querySelectorAll('.flash.success');
setTimeout(function () {
    for (var j = 0; j < messages.length; j++) {
        messages[j].style.opacity = '0';
    }
}, 8000);
