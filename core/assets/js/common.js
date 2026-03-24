$(function() {
	"use strict";
	skinChanger();
    initSparkline();
    
    setTimeout(function() {
        $('.page-loader-wrapper').fadeOut();
    }, 50);
});

// Sparkline
function initSparkline() {
	$(".sparkline").each(function() {
		var $this = $(this);
		$this.sparkline('html', $this.data());
	});
}

//Skin changer
function skinChanger() {
    $('.choose-skin li').on('click', function() {
        var $body = $('body');
        var $this = $(this);

        var existTheme = $('.choose-skin li.active').data('theme');
        $('.choose-skin li').removeClass('active');
        $body.removeClass('theme-' + existTheme);
        $this.addClass('active');
        $body.attr('data-theme','theme-' + $this.data('theme'));
    });
}

$(document).ready(function() {

	// sidebar navigation
	$("#main-menu").metisMenu({
		preventDefault: false
	});


	// sidebar nav scrolling
	// $('#left-sidebar .sidebar-scroll').slimScroll({
	// 	height: 'calc(100vh - 65px)',
	// 	wheelStep: 10,
	// 	touchScrollStep: 50,
	// 	color: '#efefef',
	// 	size: '2px',
	// 	borderRadius: '3px',
	// 	alwaysVisible: false,
	// 	position: 'right',
	// });

	// cwidget scroll
	// $('.cwidget-scroll').slimScroll({
	// 	height: '263px',
	// 	wheelStep: 10,
	// 	touchScrollStep: 50,
	// 	color: '#efefef',
	// 	size: '2px',
	// 	borderRadius: '3px',
	// 	alwaysVisible: false,
	// 	position: 'right',
	// });

	// toggle fullwidth layout
	$('.btn-toggle-fullwidth').on('click', function() {
		if(!$('body').hasClass('layout-fullwidth')) {
			$('body').addClass('layout-fullwidth');
			$(this).find(".fa").toggleClass('fa-arrow-left fa-arrow-right');

		} else {
			$('body').removeClass('layout-fullwidth');
			$(this).find(".fa").toggleClass('fa-arrow-left fa-arrow-right');
		}
	});

	// off-canvas menu toggle
	$('.btn-toggle-offcanvas').on('click', function() {
		$('body').toggleClass('offcanvas-active');
	});

	$('#main-content').on('click', function() {
		$('body').removeClass('offcanvas-active');
	});

	// adding effect dropdown menu
	$('.dropdown').on('show.bs.dropdown', function() {
		$(this).find('.dropdown-menu').first().stop(true, true).animate({
			top: '100%'
		}, 200);
	});

	$('.dropdown').on('hide.bs.dropdown', function() {
		$(this).find('.dropdown-menu').first().stop(true, true).animate({
			top: '80%'
		}, 200);
	});

	// navbar search form
	$('.navbar-form.search-form input[type="text"]')
	.on('focus', function() {
		$(this).animate({
			width: '+=50px'
		}, 300);
	})
	.on('focusout', function() {
		$(this).animate({
			width: '-=50px'
		}, 300);
	});

	// Bootstrap tooltip init
	if($('[data-bs-toggle="tooltip"]').length > 0) {
		$('[data-bs-toggle="tooltip"]').tooltip();
	}

	if($('[data-bs-toggle="popover"]').length > 0) {
		$('[data-bs-toggle="popover"]').popover();
	}

	$(window).on('load', function() {
		// for shorter main content
		if($('#main-content').height() < $('#left-sidebar').height()) {
			$('#main-content').css('min-height', $('#left-sidebar').innerHeight() - $('footer').innerHeight());
		}
	});

	$(window).on('load resize', function() {
		if($(window).innerWidth() < 420) {
			$('.navbar-brand logo.svg').attr('src', '../assets/images/logo-icon.svg');
		} else {
			$('.navbar-brand logo-icon.svg').attr('src', '../assets/images/logo.svg');
		}
	});

});

// toggle function
$.fn.clickToggle = function( f1, f2 ) {
	return this.each( function() {
		var clicked = false;
		$(this).bind('click', function() {
			if(clicked) {
				clicked = false;
				return f2.apply(this, arguments);
			}

			clicked = true;
			return f1.apply(this, arguments);
		});
	});

};

// Select all checkbox
$('.select-all').on('click',function(){
   
	if(this.checked){
		$(this).parents('table').find('.checkbox-tick').each(function(){
		this.checked = true;
		});
	}else{
		$(this).parents('table').find('.checkbox-tick').each(function(){
		this.checked = false;
		});
	}
	});

	$('.checkbox-tick').on('click',function(){   
	if($(this).parents('table').find('.checkbox-tick:checked').length == $(this).parents('table').find('.checkbox-tick').length){
		$(this).parents('table').find('.select-all').prop('checked',true);
	}else{
		$(this).parents('table').find('.select-all').prop('checked',false);
	}
});


window.lucid= {
	colors: {
	    'blue': '#467fcf',
	    'blue-darkest': '#0e1929',
	    'blue-darker': '#1c3353',
	    'blue-dark': '#3866a6',
	    'blue-light': '#7ea5dd',
	    'blue-lighter': '#c8d9f1',
	    'blue-lightest': '#edf2fa',
	    'azure': '#45aaf2',
	    'azure-darkest': '#0e2230',
	    'azure-darker': '#1c4461',
	    'azure-dark': '#3788c2',
	    'azure-light': '#7dc4f6',
	    'azure-lighter': '#c7e6fb',
	    'azure-lightest': '#ecf7fe',
	    'indigo': '#6574cd',
	    'indigo-darkest': '#141729',
	    'indigo-darker': '#282e52',
	    'indigo-dark': '#515da4',
	    'indigo-light': '#939edc',
	    'indigo-lighter': '#d1d5f0',
	    'indigo-lightest': '#f0f1fa',
	    'purple': '#a55eea',
	    'purple-darkest': '#21132f',
	    'purple-darker': '#42265e',
	    'purple-dark': '#844bbb',
	    'purple-light': '#c08ef0',
	    'purple-lighter': '#e4cff9',
	    'purple-lightest': '#f6effd',
	    'pink': '#f66d9b',
	    'pink-darkest': '#31161f',
	    'pink-darker': '#622c3e',
	    'pink-dark': '#c5577c',
	    'pink-light': '#f999b9',
	    'pink-lighter': '#fcd3e1',
	    'pink-lightest': '#fef0f5',
	    'red': '#e74c3c',
	    'red-darkest': '#2e0f0c',
	    'red-darker': '#5c1e18',
	    'red-dark': '#b93d30',
	    'red-light': '#ee8277',
	    'red-lighter': '#f8c9c5',
	    'red-lightest': '#fdedec',
	    'orange': '#fd9644',
	    'orange-darkest': '#331e0e',
	    'orange-darker': '#653c1b',
	    'orange-dark': '#ca7836',
	    'orange-light': '#feb67c',
	    'orange-lighter': '#fee0c7',
	    'orange-lightest': '#fff5ec',
	    'yellow': '#f1c40f',
	    'yellow-darkest': '#302703',
	    'yellow-darker': '#604e06',
	    'yellow-dark': '#c19d0c',
	    'yellow-light': '#f5d657',
	    'yellow-lighter': '#fbedb7',
	    'yellow-lightest': '#fef9e7',
	    'lime': '#7bd235',
	    'lime-darkest': '#192a0b',
	    'lime-darker': '#315415',
	    'lime-dark': '#62a82a',
	    'lime-light': '#a3e072',
	    'lime-lighter': '#d7f2c2',
	    'lime-lightest': '#f2fbeb',
	    'green': '#5eba00',
	    'green-darkest': '#132500',
	    'green-darker': '#264a00',
	    'green-dark': '#4b9500',
	    'green-light': '#8ecf4d',
	    'green-lighter': '#cfeab3',
	    'green-lightest': '#eff8e6',
	    'teal': '#2bcbba',
	    'teal-darkest': '#092925',
	    'teal-darker': '#11514a',
	    'teal-dark': '#22a295',
	    'teal-light': '#6bdbcf',
	    'teal-lighter': '#bfefea',
	    'teal-lightest': '#eafaf8',
	    'cyan': '#17a2b8',
	    'cyan-darkest': '#052025',
	    'cyan-darker': '#09414a',
	    'cyan-dark': '#128293',
	    'cyan-light': '#5dbecd',
	    'cyan-lighter': '#b9e3ea',
	    'cyan-lightest': '#e8f6f8',
	    'gray': '#868e96',
	    'gray-darkest': '#1b1c1e',
	    'gray-darker': '#36393c',
	    'gray-dark': '#6b7278',
	    'gray-light': '#aab0b6',
	    'gray-lighter': '#dbdde0',
	    'gray-lightest': '#f3f4f5',
	    'gray-dark': '#343a40',
	    'gray-dark-darkest': '#0a0c0d',
	    'gray-dark-darker': '#15171a',
	    'gray-dark-dark': '#2a2e33',
	    'gray-dark-light': '#717579',
	    'gray-dark-lighter': '#c2c4c6',
	    'gray-dark-lightest': '#ebebec'
	}
};

// light and dark theme setting js
$(function() {
    "use strict";
    var toggleSwitch = document.querySelector('.theme-switch input[type="checkbox"]');
    var toggleHcSwitch = document.querySelector('.theme-high-contrast input[type="checkbox"]');
    var currentTheme = localStorage.getItem('theme');
    if (currentTheme) {
        document.documentElement.setAttribute('data-theme', currentTheme);
    
        if (currentTheme === 'dark') {
            toggleSwitch.checked = true;
        }
        if (currentTheme === 'high-contrast') {
            toggleHcSwitch.checked = true;
            toggleSwitch.checked = false;
        }
    }
    function switchTheme(e) {
        if (e.target.checked) {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            $('.theme-high-contrast input[type="checkbox"]').prop("checked", false);
        }
        else {        
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        }    
    }
    function switchHc(e) {
        if (e.target.checked) {
            document.documentElement.setAttribute('data-theme', 'high-contrast');
            localStorage.setItem('theme', 'high-contrast');
            $('.theme-switch input[type="checkbox"]').prop("checked", false);
        }
        else {        
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        }  
    }
    toggleSwitch.addEventListener('change', switchTheme, false);
    toggleHcSwitch.addEventListener('change', switchHc, false);

    $('.theme-rtl input:checkbox').on('click', function () {
        if($(this).is(":checked")) {
            $('body').addClass("rtl");
        } else {
            $('body').removeClass("rtl");
        }
    });
});



// ── EES global utilities ────────────────────────────────────────────────────
window.EES = window.EES || {};

/**
 * Custom alert modal — replaces window.alert().
 * @param {string} message
 * @param {string} type     'success' | 'error' | 'warning' | 'info'  (default: 'info')
 * @param {string} [title]  optional override title
 */
EES.alert = function(message, type, title) {
    type  = type  || 'info';
    var _titles = { success: 'Success', error: 'Error', warning: 'Warning', info: 'Notice' };
    title = title || _titles[type] || 'Notice';
    var _colors = { success: '#70AD47', error: '#dc3545', warning: '#e6a817', info: '#17a2b8' };
    var _icons  = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
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

/**
 * Put a button into a loading/spinner state.
 * @param {HTMLElement|string} btn  element or CSS selector
 * @param {string} [text]           optional label while loading (default: 'Loading…')
 */
EES.btnLoad = function(btn, text) {
    if (!btn) return;
    btn = typeof btn === 'string' ? document.querySelector(btn) : btn;
    if (!btn || btn.disabled) return;
    btn.setAttribute('data-ees-orig', btn.innerHTML);
    btn.innerHTML = '<i class="fa fa-spinner fa-spin" style="margin-right:5px;"></i>' + (text || btn.getAttribute('data-loading-text') || 'Loading…');
    btn.disabled = true;
};

/**
 * Restore a button previously put into loading state.
 * @param {HTMLElement|string} btn
 */
EES.btnReset = function(btn) {
    if (!btn) return;
    btn = typeof btn === 'string' ? document.querySelector(btn) : btn;
    if (!btn) return;
    var orig = btn.getAttribute('data-ees-orig');
    if (orig !== null) { btn.innerHTML = orig; btn.removeAttribute('data-ees-orig'); }
    btn.disabled = false;
};
// ── end EES utilities ───────────────────────────────────────────────────────

// Global 401 handler: redirect to login when the server signals session expiry
$(document).ajaxError(function(event, xhr) {
    if (xhr.status === 401) {
        if (!window._ees_redirecting) {
            window._ees_redirecting = true;
            window.location.replace('login.php');
        }
    }
});

// Wraptheme Website live
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/5c6d4867f324050cfe342c69/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
