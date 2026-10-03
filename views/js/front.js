/* Bouton « Arrondir mon panier » : appelle le contrôleur roundcart puis recharge la page.
 * JavaScript natif (pas de dépendance à jQuery : absent ou chargé tardivement selon les thèmes). */
(function () {
    'use strict';

    function post(url, done) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) {
                return;
            }
            var res = null;
            try {
                res = JSON.parse(xhr.responseText);
            } catch (e) {
                res = null;
            }
            done(res);
        };
        xhr.send('');
    }

    document.addEventListener('click', function (event) {
        var el = event.target;
        while (el && el !== document && !(el.classList && el.classList.contains('btn-cart-rounding'))) {
            el = el.parentNode;
        }
        if (!el || el === document) {
            return;
        }
        event.preventDefault();
        if (el.classList.contains('is-loading')) {
            return;
        }
        el.classList.add('is-loading');
        post(el.getAttribute('data-round'), function (result) {
            if (result && result.success === true) {
                window.location.reload();
            } else {
                el.classList.remove('is-loading');
            }
        });
    });
})();
