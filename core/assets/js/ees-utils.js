/**
 * EES global utilities — lightweight standalone build.
 * Loaded on public pages (login, register, forgot-password) that do not
 * include mainscripts.bundle.js.  The authenticated bundle also defines
 * these, so window.EES = window.EES || {} prevents double-initialisation.
 */
window.EES = window.EES || {};

EES.alert = function(message, type, title) {
    type  = type  || 'info';
    var _titles = { success: 'Success', error: 'Error', warning: 'Warning', info: 'Notice' };
    var _colors = { success: '#70AD47', error: '#dc3545', warning: '#e6a817', info: '#17a2b8' };
    var _icons  = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
    title = title || _titles[type] || 'Notice';
    var color = _colors[type] || _colors.info;
    var icon  = _icons[type]  || _icons.info;

    var el = document.getElementById('ees-alert-modal');
    if (!el) {
        el = document.createElement('div');
        el.id = 'ees-alert-modal';
        el.style.cssText = 'display:none;position:fixed;inset:0;z-index:99999;align-items:center;justify-content:center;background:rgba(0,0,0,.45);';
        document.body.appendChild(el);
        el.addEventListener('click', function(e) { if (e.target === el) EES._closeAlert(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') EES._closeAlert(); });
    }

    el.innerHTML =
        '<div style="background:#fff;border-radius:8px;padding:32px 28px 24px;max-width:420px;width:90%;text-align:center;box-shadow:0 8px 32px rgba(0,0,0,.2);">' +
            '<i class="fa ' + icon + '" style="font-size:44px;color:' + color + ';margin-bottom:14px;display:block;"></i>' +
            '<div style="font-size:1.05rem;font-weight:600;margin-bottom:8px;color:#333;">' + title + '</div>' +
            '<div style="color:#555;font-size:.92rem;margin-bottom:22px;line-height:1.5;">' + message + '</div>' +
            '<button onclick="EES._closeAlert()" style="background:' + color + ';color:#fff;border:none;border-radius:4px;padding:9px 32px;font-size:.92rem;cursor:pointer;font-weight:600;">OK</button>' +
        '</div>';
    el.style.display = 'flex';
    setTimeout(function() { var b = el.querySelector('button'); if (b) b.focus(); }, 50);
};

EES._closeAlert = function() {
    var el = document.getElementById('ees-alert-modal');
    if (el) el.style.display = 'none';
};

EES.btnLoad = function(btn, text) {
    if (!btn) return;
    btn = typeof btn === 'string' ? document.querySelector(btn) : btn;
    if (!btn || btn.disabled) return;
    btn.setAttribute('data-ees-orig', btn.innerHTML || btn.value || '');
    var label = text || btn.getAttribute('data-loading-text') || 'Loading\u2026';
    if (btn.tagName === 'INPUT') {
        btn.value = label;
    } else {
        btn.innerHTML = '<i class="fa fa-spinner fa-spin" style="margin-right:5px;"></i>' + label;
    }
    btn.disabled = true;
};

EES.btnReset = function(btn) {
    if (!btn) return;
    btn = typeof btn === 'string' ? document.querySelector(btn) : btn;
    if (!btn) return;
    var orig = btn.getAttribute('data-ees-orig');
    if (orig !== null) {
        if (btn.tagName === 'INPUT') { btn.value = orig; }
        else { btn.innerHTML = orig; }
        btn.removeAttribute('data-ees-orig');
    }
    btn.disabled = false;
};
