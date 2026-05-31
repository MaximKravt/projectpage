document.addEventListener('DOMContentLoaded', function () {
  var buttons = document.querySelectorAll('.wishlist-btn');
  if (!buttons.length || typeof window.gamestoreWishlist === 'undefined') return;

  function setButtonState(btn, active) {
    btn.classList.toggle('is-active', active);
    btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    var icon = btn.querySelector('i');
    if (icon) {
      icon.className = active ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
    }
    btn.setAttribute(
      'aria-label',
      active ? 'Убрать из избранного' : 'Добавить в избранное'
    );
  }

  async function toggle(btn) {
    var productId = btn.getAttribute('data-product-id');
    if (!productId) return;

    btn.disabled = true;
    btn.classList.add('is-loading');

    try {
      var body = new URLSearchParams();
      body.set('action', 'gamestore_wishlist_toggle');
      body.set('nonce', window.gamestoreWishlist.nonce);
      body.set('product_id', productId);

      var res = await fetch(window.gamestoreWishlist.ajaxUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: body.toString(),
        credentials: 'same-origin',
      });

      var data = await res.json();
      if (!data || !data.success) throw new Error('Request failed');

      setButtonState(btn, !!data.data.added);

      // Update header badge if present
      var badge = document.querySelector('.wishlist-count');
      if (badge) {
        var count = Number(data.data.count || 0);
        badge.textContent = String(count);
        badge.style.display = count > 0 ? 'inline-flex' : 'none';
      }
    } catch (e) {
      // ignore - keep previous state
    } finally {
      btn.classList.remove('is-loading');
      btn.disabled = false;
    }
  }

  buttons.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      toggle(btn);
    });
  });
});

