(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var config = window.xhDeactivateFeedback || {};
        if (!config.pluginSlug) return;

        var deactivateLink = document.querySelector(
            'tr[data-slug="' + config.pluginSlug + '"] .deactivate a, ' +
            '#deactivate-' + config.pluginSlug
        );
        if (!deactivateLink) return;

        var deactivateUrl = deactivateLink.getAttribute('href');

        deactivateLink.addEventListener('click', function (e) {
            e.preventDefault();
            showFeedbackModal(deactivateUrl);
        });

        function showFeedbackModal(deactivateUrl) {
            var overlay = document.createElement('div');
            overlay.id = 'xh-deactivate-overlay';
            overlay.innerHTML = buildModalHTML();
            document.body.appendChild(overlay);

            addModalStyles();

            bindModalEvents(overlay, deactivateUrl);

            requestAnimationFrame(function () {
                overlay.classList.add('xh-modal-visible');
            });
        }

        function buildModalHTML() {
            var i18n = config.i18n || {};
            var reasons = config.reasons || {};

            var reasonsHTML = '';
            for (var key in reasons) {
                if (reasons.hasOwnProperty(key)) {
                    reasonsHTML += '<label class="xh-reason-item">' +
                        '<input type="radio" name="xh_reason" value="' + escapeHtml(key) + '">' +
                        '<span class="xh-reason-radio"></span>' +
                        '<span class="xh-reason-text">' + escapeHtml(reasons[key]) + '</span>' +
                        '</label>';
                }
            }

            return '<div class="xh-modal-card">' +
                '<div class="xh-modal-header">' +
                '<button type="button" id="xh-close-btn" class="xh-close-btn">' +
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
                '</button>' +
                '<div class="xh-header-content">' +
                '<div class="xh-header-emoji">👋</div>' +
                '<div class="xh-header-text">' +
                '<h3 class="xh-modal-title">' + escapeHtml(i18n.title || '即将离开？') + '</h3>' +
                '<p class="xh-modal-subtitle">' + escapeHtml(i18n.subtitle || '感谢您的使用，请告诉我们可以改进的地方') + '</p>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="xh-modal-body">' +
                '<div class="xh-reasons-list">' + reasonsHTML + '</div>' +
                '<div id="xh-feedback-box" class="xh-feedback-box">' +
                '<label id="xh-feedback-label" class="xh-feedback-label">' + escapeHtml(i18n.feedbackLabel || '其他反馈或建议（可选）') + '</label>' +
                '<textarea id="xh-feedback-text" class="xh-feedback-textarea" rows="3" maxlength="500" placeholder="' + escapeHtml(i18n.feedbackPlaceholder || '请告诉我们如何改进...') + '"></textarea>' +
                '</div>' +
                '</div>' +
                '<div class="xh-modal-footer">' +
                '<button type="button" id="xh-submit-btn" class="xh-btn xh-btn-primary xh-btn-skip">' +
                '<span class="xh-btn-text">' + escapeHtml(i18n.skip || '跳过反馈，直接停用') + '</span>' +
                '<span class="xh-btn-loading" style="display:none;">' +
                '<svg class="xh-spinner" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="60" stroke-linecap="round"/></svg>' +
                '</span>' +
                '</button>' +
                '<p class="xh-footer-hint">' + escapeHtml(i18n.footerHint || '您的反馈将帮助我们不断改进') + '</p>' +
                '</div>' +
                '</div>';
        }

        function addModalStyles() {
            if (document.getElementById('xh-deactivate-styles')) return;

            var style = document.createElement('style');
            style.id = 'xh-deactivate-styles';
            style.textContent =
                '#xh-deactivate-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,.6);display:flex;align-items:center;justify-content:center;z-index:999999;opacity:0;transition:opacity .2s ease;backdrop-filter:blur(4px)}' +
                '#xh-deactivate-overlay.xh-modal-visible{opacity:1}' +
                '.xh-modal-card{background:#fff;border-radius:16px;width:100%;max-width:440px;margin:20px;box-shadow:0 25px 50px -12px rgba(0,0,0,.3);transform:translateY(20px) scale(.95);transition:transform .3s ease;overflow:hidden}' +
                '.xh-modal-visible .xh-modal-card{transform:translateY(0) scale(1)}' +
                '.xh-modal-header{padding:24px 24px 20px;position:relative;background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%)}' +
                '.xh-close-btn{position:absolute;top:12px;right:12px;width:32px;height:32px;border:none;background:transparent;cursor:pointer;border-radius:8px;display:flex;align-items:center;justify-content:center;transition:background .15s ease}' +
                '.xh-close-btn:hover{background:rgba(0,0,0,.05)}' +
                '.xh-close-btn svg{width:18px;height:18px;color:#94a3b8}' +
                '.xh-header-content{display:flex;align-items:center;gap:16px}' +
                '.xh-header-emoji{font-size:40px;line-height:1}' +
                '.xh-header-text{flex:1}' +
                '.xh-modal-title{margin:0 0 4px;font-size:18px;font-weight:600;color:#1e293b}' +
                '.xh-modal-subtitle{margin:0;font-size:13px;color:#64748b;line-height:1.5}' +
                '.xh-modal-body{padding:20px 24px}' +
                '.xh-reasons-list{display:flex;flex-direction:column;gap:8px}' +
                '.xh-reason-item{display:flex;align-items:center;padding:12px 14px;border:1px solid #e2e8f0;border-radius:10px;cursor:pointer;transition:all .15s ease}' +
                '.xh-reason-item:hover{border-color:#cbd5e1;background:#f8fafc}' +
                '.xh-reason-item input{display:none}' +
                '.xh-reason-item input:checked ~ .xh-reason-radio{background:#3b82f6;border-color:#3b82f6}' +
                '.xh-reason-item input:checked ~ .xh-reason-radio::after{opacity:1}' +
                '.xh-reason-item input:checked ~ .xh-reason-text{color:#1e40af;font-weight:500}' +
                '.xh-reason-radio{width:18px;height:18px;border:2px solid #cbd5e1;border-radius:50%;margin-right:12px;flex-shrink:0;position:relative;transition:all .15s ease}' +
                '.xh-reason-radio::after{content:"";position:absolute;left:4px;top:4px;width:6px;height:6px;background:#fff;border-radius:50%;opacity:0;transition:opacity .15s ease}' +
                '.xh-reason-text{font-size:14px;color:#475569;line-height:1.4}' +
                '.xh-feedback-box{margin-top:16px;max-height:0;opacity:0;overflow:hidden;transition:max-height .35s cubic-bezier(.4,0,.2,1),opacity .25s ease,margin-top .35s ease}' +
                '.xh-feedback-box.xh-visible{max-height:200px;opacity:1;margin-top:16px}' +
                '.xh-feedback-box.xh-hidden{max-height:0;opacity:0;margin-top:0}' +
                '.xh-feedback-label{display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:8px;transition:opacity .2s ease}' +
                '.xh-feedback-textarea{width:100%;padding:12px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:14px;color:#334155;resize:none;transition:all .15s ease;box-sizing:border-box;font-family:inherit}' +
                '.xh-feedback-textarea:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1)}' +
                '.xh-feedback-textarea::placeholder{color:#94a3b8}' +
                '.xh-modal-footer{padding:16px 24px 24px;display:flex;flex-direction:column;align-items:center;gap:10px}' +
                '.xh-btn{display:inline-flex;align-items:center;justify-content:center;padding:12px 24px;border-radius:10px;font-size:14px;font-weight:500;cursor:pointer;transition:all .25s ease;border:none}' +
                '.xh-btn-primary{background:linear-gradient(135deg,#64748b 0%,#475569 100%);color:#fff;width:100%;box-shadow:0 2px 8px rgba(100,116,139,.2)}' +
                '.xh-btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(100,116,139,.3)}' +
                '.xh-btn-primary.xh-btn-submit{background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%);box-shadow:0 2px 8px rgba(59,130,246,.3)}' +
                '.xh-btn-primary.xh-btn-submit:hover{box-shadow:0 4px 12px rgba(59,130,246,.4)}' +
                '.xh-btn-primary:disabled{background:#94a3b8;cursor:not-allowed;transform:none;box-shadow:none}' +
                '.xh-footer-hint{margin:0;font-size:12px;color:#94a3b8;text-align:center}' +
                '.xh-spinner{width:18px;height:18px;animation:xh-spin 1s linear infinite}' +
                '@keyframes xh-spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}';
            document.head.appendChild(style);
        }

        function bindModalEvents(overlay, deactivateUrl) {
            var submitBtn = overlay.querySelector('#xh-submit-btn');
            var closeBtn = overlay.querySelector('#xh-close-btn');
            var card = overlay.querySelector('.xh-modal-card');
            var radios = overlay.querySelectorAll('input[name="xh_reason"]');
            var feedbackBox = overlay.querySelector('#xh-feedback-box');
            var feedbackLabel = overlay.querySelector('#xh-feedback-label');
            var feedbackTextarea = overlay.querySelector('#xh-feedback-text');
            var btnText = submitBtn.querySelector('.xh-btn-text');
            var i18n = config.i18n || {};

            radios.forEach(function (radio) {
                radio.addEventListener('change', function () {
                    var selectedReason = this.value;

                    updateFeedbackBox(selectedReason, feedbackBox, feedbackLabel, feedbackTextarea, i18n);

                    updateButtonState(submitBtn, btnText, true, i18n);
                });
            });

            submitBtn.addEventListener('click', function () {
                submitFeedback(deactivateUrl, submitBtn);
            });

            closeBtn.addEventListener('click', function () {
                closeModal(overlay);
            });

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    closeModal(overlay);
                }
            });

            document.addEventListener('keydown', function escHandler(e) {
                if (e.key === 'Escape') {
                    closeModal(overlay);
                    document.removeEventListener('keydown', escHandler);
                }
            });

            card.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        function updateButtonState(submitBtn, btnText, hasSelection, i18n) {
            if (hasSelection) {
                submitBtn.classList.remove('xh-btn-skip');
                submitBtn.classList.add('xh-btn-submit');
                btnText.textContent = i18n.submit || '提交反馈并停用';
            } else {
                submitBtn.classList.remove('xh-btn-submit');
                submitBtn.classList.add('xh-btn-skip');
                btnText.textContent = i18n.skip || '跳过反馈，直接停用';
            }
        }

        function updateFeedbackBox(reason, feedbackBox, feedbackLabel, feedbackTextarea, i18n) {
            var prompts = config.feedbackPrompts || {};

            if (reason === 'temporary') {
                feedbackBox.classList.remove('xh-visible');
                feedbackBox.classList.add('xh-hidden');
                return;
            }

            var prompt = prompts[reason] || i18n.feedbackLabel || '其他反馈或建议（可选）';
            var placeholder = prompts[reason + '_placeholder'] || i18n.feedbackPlaceholder || '请告诉我们如何改进...';

            feedbackLabel.textContent = prompt;
            feedbackTextarea.placeholder = placeholder;

            feedbackBox.classList.remove('xh-hidden');
            feedbackBox.classList.add('xh-visible');
        }

        function submitFeedback(deactivateUrl, submitBtn) {
            var overlay = document.getElementById('xh-deactivate-overlay');
            var selectedRadio = overlay.querySelector('input[name="xh_reason"]:checked');
            var feedbackTextarea = overlay.querySelector('#xh-feedback-text');
            var feedbackText = feedbackTextarea ? feedbackTextarea.value.trim() : '';

            var selectedReason = selectedRadio ? selectedRadio.value : 'skipped';

            var btnText = submitBtn.querySelector('.xh-btn-text');
            var btnLoading = submitBtn.querySelector('.xh-btn-loading');
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-flex';
            submitBtn.disabled = true;

            var timestamp = Math.floor(Date.now() / 1000);
            var data = {
                type: 'feedback',
                pluginsver: config.pluginsVer || '',
                apitoken: config.apiToken || '',
                timestamp: timestamp,
                args: {
                    reason: selectedReason,
                    feedback: feedbackText,
                    wp_version: config.wpVersion || '',
                    php_version: config.phpVersion || '',
                    locale: document.documentElement.lang || ''
                }
            };

            fetch(config.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + (config.apiToken || ''),
                    'Referer': window.location.origin
                },
                body: JSON.stringify(data),
                referrer: window.location.origin,
                referrerPolicy: 'origin'
            }).finally(function () {
                window.location.href = deactivateUrl;
            });
        }

        function closeModal(overlay) {
            overlay.classList.remove('xh-modal-visible');
            setTimeout(function () {
                if (overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
            }, 200);
        }

        function escapeHtml(str) {
            if (!str) return '';
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    });
})();
