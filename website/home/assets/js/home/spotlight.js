const POPUP = document.querySelector("#spotlight");
if (!POPUP || typeof POPUP.showPopover !== "function") { console.warn("[spotlight] Popover API unavailable"); }
else (function () {
const CMD = 91;
const CTRL = 17;
const MOD = 74;
const STATE = {
  cmd: false,
  ctrl: false,
  mod: false
};

const handleActivation = e => {
  if (e.keyCode === CMD) STATE.cmd = true;
  if (e.keyCode === CTRL) STATE.ctrl = true;
  if (e.keyCode === MOD && (STATE.cmd || STATE.ctrl)) STATE.mod = true;
  
  if ((STATE.cmd || STATE.ctrl) && STATE.mod && !POPUP.matches(":open")) {
    STATE.cmd = STATE.ctrl = STATE.mod = false;
    POPUP.showPopover();
    OPTIONS.showPopover();
  }
};

const unload = e => {
  if (e.keyCode === CMD || e.keyCode === CTRL || e.keyCode === MOD) {
    STATE.cmd = STATE.ctrl = STATE.mod = false;
  }
};

document.body.addEventListener("keydown", handleActivation);
document.body.addEventListener("keyup", unload);

document.documentElement.setAttribute(
  "data-theme",
  window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"
);

let length = 0;
const OPTIONS = document.querySelector("#spotlight-options");
const SEARCH = document.querySelector("#spotlight-search");
const AVAILABLE_OPTIONS = [
  {
    id: "toggle-theme",
    title: '<i class="uil uil-moon-eclipse"></i> Toggle theme',
    action: () => {
      document.documentElement.setAttribute(
        "data-theme",
        document.documentElement.matches('[data-theme="dark"]')
          ? "light"
          : "dark"
      );
    }
  },
  {
    id: "telegram-reach",
    title:
      '<i class="uil uil-telegram-alt"></i> News on Telegram',
    action: () => {
      window.open("https://t.me/s/aev_platforms", "_blank", "noopener");
    }
  },
  {
    id: "popup-explainer",
    title:
      '<i class="uil uil-file-edit-alt"></i> Check out the Docs',
    action: () => {
      window.open("/home/_api/Docs/", "_blank");
    }
  },
  {
    id: "web-search",
    title: (value) =>
      `Search web for "${value}" <i class="uil uil-globe"></i>`,
    action: (value) => {
      window.open(`https://google.com/search?q=${encodeURIComponent(value)}`, "_blank", "noopener");
    }
  }
];

const fireAction = (actionId) => {
  AVAILABLE_OPTIONS.filter((option) => option.id === actionId)[0].action(
    SEARCH.value
  );
  if (actionId !== "toggle-theme") {
    SEARCH.value = "";
    POPUP.hidePopover();
  }
};

const buildOptions = (options, value) => {
  let items = "";
  options.forEach((option, index) => {
    if (option)
      items += `
      <div id="${index}" role="option" aria-selected="${
        index === 0
      }" data-option="${option.id}">
        ${typeof option.title === "string" ? option.title : option.title(value)}
      </div>
    `;
  });
  OPTIONS.innerHTML = items;
};

const onOptionsOpen = () => {
  const { bottom, left } = POPUP.getBoundingClientRect();
  SEARCH.setAttribute("aria-expanded", true);
  OPTIONS.style.setProperty("--top", bottom);
  OPTIONS.style.setProperty("--left", left);
};

const onOptionsHide = (e) => {
  SEARCH.setAttribute("aria-expanded", false);
};

const onActivated = (e) => {
  if (e.target === POPUP) {
    renderOptions(SEARCH.value);
  }
};

const selectOption = (e) => {
  let selected;
  if (e.type === "pointermove") {
    const opts = [...OPTIONS.children];
    opts.forEach((option) => {
      let target = e.target.tagName === "I" ? e.target.parentNode : e.target;
      option.setAttribute("aria-selected", option === target ? true : false);
      if (option === target) selected = option;
    });
  } else {
    e.preventDefault();
    const CURRENT = document.querySelector('[aria-selected="true"]');
    selected =
      CURRENT[
        e.keyCode === 38 ? "previousElementSibling" : "nextElementSibling"
      ];
    if (!selected)
      selected =
        CURRENT.parentNode[
          e.keyCode === 38 ? "lastElementChild" : "firstElementChild"
        ];
    CURRENT.setAttribute("aria-selected", false);
    selected.setAttribute("aria-selected", true);
  }
  if (selected)
    SEARCH.setAttribute(
      "aria-activedescendant",
      selected.getAttribute("data-option")
    );
};

const renderOptions = (value) => {
  length = value.length;
  let options = [
    ...AVAILABLE_OPTIONS.filter((option) => {
      return (
        typeof option.title === "string" &&
        option.title.toLowerCase().indexOf(value.toLowerCase()) !== -1
      );
    }),
    value !== ""
      ? AVAILABLE_OPTIONS.filter((option) => option.id === "web-search")[0]
      : null
  ];
  // Regardless of what's happening, you're building the options...
  buildOptions(options, value);

  if (!OPTIONS.matches(":open") && options.length) {
    // Throws when called while the spotlight itself is still opening (the caller opens it right after).
    try { OPTIONS.showPopover(); } catch (err) { /* opened by the caller */ }
  }
};

const handleActionClick = (e) => {
  fireAction(
    e.target.tagName === "I"
      ? e.target.parentNode.getAttribute("data-option")
      : e.target.getAttribute("data-option")
  );
};

const handleKeyboardInteraction = (e) => {
  // Handle building of options
  if (POPUP.matches(":open")) {
    if (e.keyCode === 13 && e.type === "keydown") {
      e.preventDefault();
      fireAction(
        document
          .querySelector('[aria-selected="true"]')
          .getAttribute("data-option")
      );
    } else if (SEARCH.value.length !== length) {
      renderOptions(SEARCH.value);
    } else if (
      OPTIONS.matches(":open") &&
      (e.keyCode === 38 || e.keyCode === 40) &&
      e.type === "keydown"
    ) {
      selectOption(e);
    } else if (e.keyCode === 27) {
      SEARCH.value = "";
    }
  }
};

// Directory of retained destinations, rendered server-side as JSON (config/apps.php)
let availableOptions = [];
function loadOptions() {
  try {
    const node = document.getElementById("aev-directory");
    availableOptions = (JSON.parse(node.textContent) || []).map((a) => ({ name: a.name, url: a.href, soon: a.soon, external: a.external }));
  } catch (err) {
    availableOptions = [];
  }
}

// Function to search for apps based on input
function searchApps(query) {
  return availableOptions.filter(app => (app.url || "").includes(query) || app.name.toLowerCase().includes(query.toLowerCase()));
}

// Function to update the search results
function updateSearchResults() {
  const query = SEARCH.value;
  OPTIONS.innerHTML = '';

  if (query) {
    const results = searchApps(query);
    results.forEach(app => {
      const div = document.createElement('div');
      div.className = 'resultItem';
      const icon = document.createElement('i');
      icon.className = 'uil uil-arrows-up-right';
      div.append(icon, document.createTextNode(` ${app.name} (${app.url})`));
      div.onclick = () => {
        if (app.soon && window.notis) { window.notis.create(app.name, 'This destination is not available yet.', 4); }
        else if (app.external) { window.open(app.url, '_blank', 'noopener'); }
        else { window.location.href = app.url; }
      };
      OPTIONS.appendChild(div);
    });
    OPTIONS.style.display = results.length ? 'block' : 'none';
  } else {
    OPTIONS.style.display = 'none';
  }
}

// Load options data and add event listener for search input
document.addEventListener('DOMContentLoaded', () => {
  loadOptions();

  SEARCH.addEventListener('input', updateSearchResults);
});


POPUP.addEventListener("beforetoggle", (e) => {
  if (e.newState === "open") onActivated(e);
});
window.addEventListener("keydown", handleKeyboardInteraction);
window.addEventListener("keypress", handleKeyboardInteraction);
window.addEventListener("keyup", handleKeyboardInteraction);
OPTIONS.addEventListener("beforetoggle", (e) => {
  if (e.newState === "open") onOptionsOpen(e);
  else onOptionsHide(e);
});
OPTIONS.addEventListener("pointermove", selectOption);
OPTIONS.addEventListener("click", handleActionClick);

// Catch case for clicking on the input
SEARCH.addEventListener("click", (e) => {
  // Don't want the click on the input to close the popup
  OPTIONS.showPopover();
});

// Open spotlight on clicking the div with class 'm-spotlight-search'
document.querySelector(".m-spotlight-search").addEventListener("click", () => {
  POPUP.showPopover();
  OPTIONS.showPopover();
});
})();
