// Accessibility toolbar: bigger text, high contrast, easier-to-read font, read aloud.
// Choices are saved in the browser, so they stay on every page.

var settings = JSON.parse(localStorage.getItem('a11y') || '{}');
var size = settings.size || 100;                // text size in percent
var contrast = settings.contrast || false;
var readable = settings.readable || false;

function applySettings() {
  document.body.style.zoom = size / 100;
  document.body.classList.toggle('high-contrast', contrast);
  document.body.classList.toggle('readable', readable);
  document.getElementById('a11yContrast').setAttribute('aria-pressed', contrast);
  document.getElementById('a11yFont').setAttribute('aria-pressed', readable);
  localStorage.setItem('a11y', JSON.stringify({ size: size, contrast: contrast, readable: readable }));
}

function stopReading() {
  if (window.speechSynthesis) window.speechSynthesis.cancel();
  var btn = document.getElementById('a11yRead');
  if (btn) btn.textContent = 'Read page aloud';
}

function readPage() {
  if (window.speechSynthesis.speaking) { stopReading(); return; }
  var area = document.getElementById('main') || document.querySelector('main') || document.body;
  var speech = new SpeechSynthesisUtterance(area.innerText);
  speech.lang = 'en-GB';
  speech.onend = stopReading;
  window.speechSynthesis.speak(speech);
  document.getElementById('a11yRead').textContent = 'Stop reading';
}

function buildToolbar() {
  var box = document.createElement('div');
  box.className = 'a11y';
  var readButton = window.speechSynthesis
    ? '<button id="a11yRead" type="button">Read page aloud</button>' : '';
  box.innerHTML =
    '<div class="a11y-panel" id="a11yPanel" role="group" aria-label="Accessibility options" hidden>' +
      '<button id="a11yMinus" type="button" aria-label="Make text smaller">A−</button>' +
      '<button id="a11yPlus" type="button" aria-label="Make text bigger">A+</button>' +
      '<button id="a11yReset" type="button">Reset</button>' +
      '<button id="a11yContrast" type="button" aria-pressed="false">High contrast</button>' +
      '<button id="a11yFont" type="button" aria-pressed="false">Easier-to-read font</button>' +
      readButton +
    '</div>' +
    '<button id="a11yToggle" type="button" aria-expanded="false" aria-controls="a11yPanel">Accessibility</button>';
  document.body.appendChild(box);

  document.getElementById('a11yToggle').onclick = function () {
    var panel = document.getElementById('a11yPanel');
    panel.hidden = !panel.hidden;
    this.setAttribute('aria-expanded', !panel.hidden);
  };
  document.getElementById('a11yPlus').onclick = function () { if (size < 150) { size += 10; applySettings(); } };
  document.getElementById('a11yMinus').onclick = function () { if (size > 80) { size -= 10; applySettings(); } };
  document.getElementById('a11yReset').onclick = function () { size = 100; contrast = false; readable = false; applySettings(); };
  document.getElementById('a11yContrast').onclick = function () { contrast = !contrast; applySettings(); };
  document.getElementById('a11yFont').onclick = function () { readable = !readable; applySettings(); };
  if (window.speechSynthesis) document.getElementById('a11yRead').onclick = readPage;
}

buildToolbar();
applySettings();
