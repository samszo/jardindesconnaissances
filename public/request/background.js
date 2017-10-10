//merci beaucoup à  https://developer.mozilla.org/en-US/Add-ons/WebExtensions/Intercept_HTTP_requests
function logURL(requestDetails) {
  console.log("Loading: " + requestDetails.url);
  console.log(requestDetails);
}

browser.webRequest.onBeforeRequest.addListener(
  logURL,
  {urls: ["<all_urls>"]}
);