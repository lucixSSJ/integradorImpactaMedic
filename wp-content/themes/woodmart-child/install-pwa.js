let deferredPrompt = null;
jQuery("#install-app").hide();

window.addEventListener("beforeinstallprompt", (e) => {
  jQuery("#install-app").show();
  deferredPrompt = e;
});

window.addEventListener("load", () => {
  const installButton = document.getElementById("install-app");
  if (installButton) {
    installButton.addEventListener("click", (event) => {
      event.preventDefault();
      if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
          if (choiceResult.outcome === "accepted") {
            deferredPrompt = null;
          } else {
          }
        });
      }
    });
  }
});
