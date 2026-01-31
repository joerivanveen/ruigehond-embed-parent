function ruigehond018_iframe_src() {
    const iframe = document.getElementById('ruigehond018-iframe'),
        domains = window.ruigehond018_domains || false;
    let hostname = window.location.hostname;
    if (!iframe || !domains) {
        console.error('ruigehond018_iframe_src: iframe or localized object not found.');
        return;
    }
    if ('www.' === hostname.substring(0, 4)) hostname = hostname.substring(4);
    const src = domains[hostname] || null;
    if (!src) {
         console.error(`ruigehond018_iframe_src: no iframe src found for hostname ${hostname}`);
         return;
    }
    iframe.src = src;
}

/* only after everything is locked and loaded we’re initialising */
if (document.readyState === 'complete') {
    ruigehond018_iframe_src();
} else {
    document.addEventListener('DOMContentLoaded', ruigehond018_iframe_src);
}