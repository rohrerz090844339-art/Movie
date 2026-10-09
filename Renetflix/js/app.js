const API_URL = 'api.php';
let adminLoginRequested = false;

function showToast(message, type = 'success') {
  let toast = document.getElementById('renetflixToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'renetflixToast';
    toast.style.cssText = 'position:fixed;right:20px;bottom:20px;z-index:4000;padding:12px 18px;border-radius:8px;background:rgba(0,0,0,0.82);color:#fff;border:1px solid rgba(229,9,20,.3);box-shadow:0 12px 30px rgba(0,0,0,.35);font-size:.9rem;max-width:320px;';
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.style.borderColor = type === 'error' ? 'rgba(255,90,90,.7)' : 'rgba(70,211,105,.7)';
  toast.style.background = type === 'error' ? 'rgba(50,10,10,0.9)' : 'rgba(15,30,18,0.9)';
  toast.style.opacity = '1';
  clearTimeout(showToast.timeoutId);
  showToast.timeoutId = setTimeout(() => {
    toast.style.opacity = '0';
  }, 2200);
}

function apiRequest(action, data = {}, method = 'POST') {
  const formData = new FormData();
  formData.append('action', action);
  Object.entries(data).forEach(([key, value]) => formData.append(key, value));

  const init = {
    method,
    body: method === 'GET' ? undefined : formData
  };

  const query = method === 'GET' ? `?action=${encodeURIComponent(action)}${Object.entries(data).map(([k, v]) => `&${encodeURIComponent(k)}=${encodeURIComponent(v)}`).join('')}` : '';

  return fetch(`${API_URL}${query}`, init)
    .then(async (response) => {
      const text = await response.text();
      try {
        return JSON.parse(text);
      } catch (error) {
        return { status: 'error', message: 'Server response was not valid JSON.' };
      }
    })
    .catch(() => ({ status: 'error', message: 'Network connection failed.' }));
}

function openAuthModal(mode = 'login') {
  const modal = document.getElementById('authModal');
  if (!modal) return;
  if (!adminLoginRequested) {
    document.querySelectorAll('.auth-tab-btn').forEach((button) => {
      if (button.dataset.authTab === 'register') button.style.display = '';
    });
    const notice = document.getElementById('adminAuthNotice');
    if (notice) notice.style.display = 'none';
    const submitButton = document.querySelector('#loginForm button[type="submit"]');
    if (submitButton) submitButton.textContent = 'Sign In';
  }
  const tabs = document.querySelectorAll('.auth-tab-btn');
  const forms = document.querySelectorAll('.auth-panel');
  tabs.forEach((button) => {
    const isActive = button.dataset.authTab === mode;
    button.classList.toggle('active', isActive);
  });
  forms.forEach((panel) => {
    const active = panel.dataset.authPanel === mode;
    panel.classList.toggle('active', active);
    panel.style.display = active ? 'block' : 'none';
  });
  modal.classList.add('active');
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) modal.classList.remove('active');
  if (modalId === 'authModal') {
    adminLoginRequested = false;
    document.querySelectorAll('.auth-tab-btn').forEach((button) => {
      if (button.dataset.authTab === 'register') button.style.display = '';
    });
    const notice = document.getElementById('adminAuthNotice');
    if (notice) notice.style.display = 'none';
    const submitButton = document.querySelector('#loginForm button[type="submit"]');
    if (submitButton) submitButton.textContent = 'Sign In';
  }
}

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
  })[character]);
}

function handleAuthSubmit(event) {
  event.preventDefault();
  const form = event.target;
  const mode = form.dataset.authMode;
  const payload = Object.fromEntries(new FormData(form).entries());
  apiRequest(mode, payload).then((result) => {
    if (result.status === 'success') {
      if (adminLoginRequested && mode === 'login') {
        if (result.user?.role === 'admin') {
          window.location.href = 'admin.php';
        } else {
          adminLoginRequested = false;
          window.history.replaceState({}, '', 'index.php');
          closeModal('authModal');
          showToast('This account does not have admin access.', 'error');
        }
        return;
      }

      showToast(result.message || 'Success');
      if (form.closest('#authModal')) closeModal('authModal');
      fetchAuthState();
      if (mode === 'login' || mode === 'register') {
        setTimeout(() => window.location.reload(), 450);
      }
    } else {
      showToast(result.message || 'There was a problem.', 'error');
    }
  });
}

function handleLogout() {
  apiRequest('logout').then((result) => {
    if (result.status === 'success') {
      showToast(result.message || 'You are signed out.');
      setTimeout(() => window.location.href = 'index.php', 250);
    }
  });
}

function fetchAuthState() {
  apiRequest('get_auth_state', {}, 'GET').then((result) => {
    if (result.status !== 'success') return;
    const loggedIn = !!result.logged_in;
    const signInButton = document.getElementById('navSignInBtn');
    const profileWrapper = document.getElementById('navProfileWrapper');
    if (signInButton) signInButton.style.display = loggedIn ? 'none' : 'inline-flex';
    if (profileWrapper) profileWrapper.style.display = loggedIn ? 'flex' : 'none';
    if (loggedIn) {
      loadSavedMovies();
    } else {
      document.getElementById('myListRow').style.display = 'none';
      document.getElementById('myFavoritesRow').style.display = 'none';
      const badge = document.getElementById('myListCountBadge');
      if (badge) badge.textContent = '0';
    }
    if (loggedIn && result.user) {
      const currentAvatar = document.getElementById('currentProfileAvatar');
      if (currentAvatar && result.user.avatar) currentAvatar.src = result.user.avatar;
      const profileName = document.getElementById('profileAvatarBtn');
      if (profileName) profileName.title = result.user.name || 'Profile';
    }
  });
}

function openQuestionsModal() {
  const modal = document.getElementById('questionModal');
  if (modal) modal.classList.add('active');
  fetchQuestions();
}

function fetchQuestions() {
  apiRequest('get_questions', {}, 'GET').then((result) => {
    const list = document.getElementById('supportQuestionList');
    if (!list || !result.questions) return;
    list.innerHTML = result.questions.slice(0, 6).map((entry) => `
      <div class="support-question-card">
        <div class="support-q-header">
          <span>${entry.name}</span>
          <span class="support-q-badge">${entry.status || 'Answered'}</span>
        </div>
        <div class="support-q-text">${entry.question}</div>
        <div class="support-a-text">${entry.answer || 'Our team will respond soon.'}</div>
      </div>
    `).join('');
  });
}

function submitSupportQuestion(event) {
  event.preventDefault();
  const form = event.target;
  const payload = Object.fromEntries(new FormData(form).entries());
  apiRequest('submit_question', payload).then((result) => {
    if (result.status === 'success') {
      showToast(result.message || 'Question submitted.');
      form.reset();
      fetchQuestions();
      closeModal('questionModal');
    } else {
      showToast(result.message || 'Unable to send question.', 'error');
    }
  });
}

function openProfileEditor() {
  const profileName = document.getElementById('profileDisplayName');
  const currentName = document.getElementById('profileMenuUserName');
  if (profileName && currentName) profileName.value = currentName.textContent.trim();

  const currentAvatar = document.getElementById('currentProfileAvatar');
  if (currentAvatar) {
    const selectedAvatar = Array.from(document.querySelectorAll('#profileForm input[name="avatar"]'))
      .find((input) => input.value === currentAvatar.getAttribute('src'));
    if (selectedAvatar) selectedAvatar.checked = true;
  }
  closeProfileMenu();
  document.getElementById('profileModal')?.classList.add('active');
}

function closeProfileMenu() {
  const wrapper = document.getElementById('navProfileWrapper');
  if (wrapper) wrapper.classList.remove('profile-menu-open');
}

function submitProfile(event) {
  event.preventDefault();
  const form = event.currentTarget;
  const payload = Object.fromEntries(new FormData(form).entries());
  apiRequest('update_profile', payload).then((result) => {
    if (result.status !== 'success') {
      showToast(result.message || 'Unable to save profile.', 'error');
      return;
    }

    const name = result.user.name;
    const avatar = result.user.avatar;
    document.querySelectorAll('#profileMenuUserName').forEach((element) => {
      element.textContent = name;
    });
    document.querySelectorAll('#currentProfileAvatar').forEach((image) => {
      image.src = avatar;
      image.alt = name;
    });
    const avatarButton = document.getElementById('profileAvatarBtn');
    if (avatarButton) avatarButton.title = name;
    const dropdownAvatar = document.getElementById('profileDropdownMenu')?.querySelector('.profile-dropdown-header img');
    if (dropdownAvatar) {
      dropdownAvatar.src = avatar;
      dropdownAvatar.alt = name;
    }
    closeModal('profileModal');
    showToast(result.message || 'Profile saved.');
  });
}

function toggleWatchlist(movieId, button) {
  apiRequest('toggle_watchlist', { movie_id: movieId }).then((result) => {
    if (result.status === 'success') {
      const active = result.in_watchlist;
      updateActionButtons('watchlist', movieId, active);
      const badge = document.getElementById('myListCountBadge');
      if (badge) badge.textContent = result.total_count || 0;
      showToast(active ? 'Added to My List.' : 'Removed from My List.');
      loadSavedMovies();
    } else {
      if (result.message?.includes('sign in')) {
        openAuthModal('login');
      } else {
        showToast(result.message || 'Unable to update My List.', 'error');
      }
    }
  });
}

function toggleFavorite(movieId, button) {
  apiRequest('toggle_favorite', { movie_id: movieId }).then((result) => {
    if (result.status === 'success') {
      updateActionButtons('favorite', movieId, result.is_favorite);
      showToast(result.is_favorite ? 'Added to Favorites.' : 'Removed from Favorites.');
      loadSavedMovies();
    } else if (result.message?.includes('sign in')) {
      openAuthModal('login');
    } else {
      showToast(result.message || 'Unable to update Favorites.', 'error');
    }
  });
}

function updateActionButtons(type, movieId, active) {
  const attribute = type === 'watchlist' ? 'data-watchlist-id' : 'data-favorite-id';
  document.querySelectorAll(`[${attribute}="${movieId}"]`).forEach((button) => {
    const icon = button.querySelector('i');
    if (!icon) return;
    icon.classList.toggle('fa-plus', type === 'watchlist' && !active);
    icon.classList.toggle('fa-check', type === 'watchlist' && active);
    icon.classList.toggle('far', type === 'favorite' && !active);
    icon.classList.toggle('fas', type === 'favorite' && active);
    icon.classList.toggle('fa-thumbs-up', type === 'favorite');
    button.title = type === 'watchlist'
      ? (active ? 'Remove from My List' : 'Add to My List')
      : (active ? 'Remove from Favorites' : 'Add to Favorites');
  });
}

function renderSavedMovies(track, movies, type) {
  if (!track) return;
  track.innerHTML = movies.map((movie) => `
    <article class="saved-movie-card">
      <img src="${escapeHtml(movie.poster_url)}" alt="${escapeHtml(movie.title)}" loading="lazy">
      <div class="saved-movie-info">
        <button class="saved-movie-title" data-open-movie="${Number(movie.id)}">${escapeHtml(movie.title)}</button>
        <span>${escapeHtml(movie.release_year)}</span>
        <button class="saved-movie-remove" data-saved-action="${type}" data-movie-id="${Number(movie.id)}">
          <i class="fas ${type === 'favorite' ? 'fa-heart' : 'fa-bookmark'}"></i>
          Remove
        </button>
      </div>
    </article>
  `).join('');
  track.closest('.row-container').style.display = movies.length ? '' : 'none';
}

function loadSavedMovies() {
  Promise.all([
    apiRequest('get_watchlist', {}, 'GET'),
    apiRequest('get_favorites', {}, 'GET')
  ]).then(([watchlist, favorites]) => {
    if (watchlist.status !== 'success' || favorites.status !== 'success') {
      showToast(watchlist.message || favorites.message || 'Unable to load your saved movies.', 'error');
      return;
    }

    const moviesOnList = watchlist.data || [];
    const favoriteMovies = favorites.data || [];
    const badge = document.getElementById('myListCountBadge');
    if (badge) badge.textContent = String(moviesOnList.length);
    renderSavedMovies(document.getElementById('myListTrack'), moviesOnList, 'watchlist');
    renderSavedMovies(document.getElementById('myFavoritesTrack'), favoriteMovies, 'favorite');

    document.querySelectorAll('[data-watchlist-id]').forEach((button) => {
      const movieId = Number(button.dataset.watchlistId);
      updateActionButtons('watchlist', movieId, moviesOnList.some((movie) => Number(movie.id) === movieId));
    });
    document.querySelectorAll('[data-favorite-id]').forEach((button) => {
      const movieId = Number(button.dataset.favoriteId);
      updateActionButtons('favorite', movieId, favoriteMovies.some((movie) => Number(movie.id) === movieId));
    });
  });
}

function bindFAQ() {
  document.querySelectorAll('.faq-question-btn').forEach((button) => {
    button.addEventListener('click', () => {
      const item = button.closest('.faq-item');
      const isActive = item.classList.contains('active');
      document.querySelectorAll('.faq-item').forEach((faqItem) => faqItem.classList.remove('active'));
      if (!isActive) item.classList.add('active');
    });
  });
}

function openMovieDetails(movieId) {
  apiRequest('get_movie', { id: movieId }, 'GET').then((result) => {
    if (result.status !== 'success' || !result.data) {
      showToast(result.message || 'Unable to load movie details.', 'error');
      return;
    }

    const movie = result.data.movie;
    const reviews = result.data.reviews || [];
    const similar = result.data.similar || [];

    const modal = document.getElementById('movieDetailModal');
    if (!modal) return;
    document.getElementById('modalMovieTitle').textContent = movie.title;
    document.getElementById('modalMatchRate').textContent = `${movie.match_rate || 95}% Match`;
    document.getElementById('modalYear').textContent = movie.release_year || '2026';
    document.getElementById('modalRating').textContent = movie.maturity_rating || 'PG-13';
    document.getElementById('modalDuration').textContent = movie.duration || '2h 10min';
    document.getElementById('modalDescription').textContent = movie.description || '';
    document.getElementById('modalCast').textContent = movie.cast_members || 'Cast information unavailable';
    document.getElementById('modalDirector').textContent = movie.director || 'Director not listed';
    document.getElementById('modalGenres').textContent = movie.genres || 'Action';
    document.getElementById('modalHeaderBanner').style.backgroundImage = `url('${(movie.backdrop_url || movie.poster_url)}')`;

    const playBtn = document.getElementById('modalPlayBtn');
    if (playBtn) {
      playBtn.onclick = () => openCinemaPlayer(movie.id, movie.title, movie.video_url);
    }
    const watchlistButton = document.getElementById('modalWatchlistBtn');
    if (watchlistButton) {
      watchlistButton.dataset.watchlistId = movie.id;
      watchlistButton.onclick = () => toggleWatchlist(movie.id, watchlistButton);
      updateActionButtons('watchlist', movie.id, result.data.in_watchlist);
    }
    const favoriteButton = document.getElementById('modalFavoriteBtn');
    if (favoriteButton) {
      favoriteButton.dataset.favoriteId = movie.id;
      favoriteButton.onclick = () => toggleFavorite(movie.id, favoriteButton);
      updateActionButtons('favorite', movie.id, result.data.is_favorite);
    }

    const reviewList = document.getElementById('modalReviewsList');
    if (reviewList) {
      reviewList.innerHTML = reviews.length ? reviews.map((review) => `
        <div class="support-question-card">
          <div class="support-q-header">
            <span>${review.user_profile || 'Viewer'}</span>
            <span class="support-q-badge">${'★'.repeat(Math.max(1, Number(review.rating || 5)))} </span>
          </div>
          <div class="support-a-text">${review.review_text}</div>
        </div>
      `).join('') : '<p style="color:#aaa;">No reviews yet. Be the first to review this title.</p>';
    }

    const similarList = document.getElementById('modalSimilarContainer');
    if (similarList) {
      similarList.innerHTML = similar.length ? similar.map((item) => `
        <div class="similar-item" onclick="openMovieDetails(${item.id})" style="cursor:pointer;">
          <img src="${item.poster_url}" alt="${item.title}">
          <span>${item.title}</span>
        </div>
      `).join('') : '<p style="color:#aaa;">No related titles found.</p>';
    }

    modal.classList.add('active');
  });
}

function openCinemaPlayer(movieId, title, videoUrl) {
  const modal = document.getElementById('cinemaPlayerModal');
  const video = document.getElementById('cinemaVideo');
  if (!modal || !video) return;
  document.getElementById('cinemaMovieTitle').textContent = title || 'Movie Title';
  video.src = videoUrl || '';
  video.load();
  modal.classList.add('active');
  video.play().catch(() => {});

  const saveProgress = () => {
    const progress = (video.currentTime / video.duration) * 100;
    if (video.duration) {
      apiRequest('save_progress', { movie_id: movieId, progress: Math.round(progress) });
    }
  };

  video.addEventListener('timeupdate', saveProgress, { once: false });
}

function initMovieInteractions() {
  document.querySelectorAll('.movie-card').forEach((card) => {
    card.addEventListener('click', (event) => {
      if (!event.target.closest('button')) {
        openMovieDetails(card.dataset.id);
      }
    });
  });

  document.addEventListener('click', (event) => {
    const openButton = event.target.closest('[data-open-movie]');
    if (openButton) {
      openMovieDetails(openButton.dataset.openMovie);
      return;
    }

    const removeButton = event.target.closest('[data-saved-action]');
    if (removeButton) {
      const movieId = Number(removeButton.dataset.movieId);
      if (removeButton.dataset.savedAction === 'watchlist') {
        toggleWatchlist(movieId, removeButton);
      } else {
        toggleFavorite(movieId, removeButton);
      }
    }
  });

  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('input', (event) => {
      const term = event.target.value.trim().toLowerCase();
      document.querySelectorAll('.movie-card').forEach((card) => {
        const title = (card.querySelector('img')?.alt || '').toLowerCase();
        const visible = !term || title.includes(term);
        card.style.display = visible ? '' : 'none';
      });
    });
  }
}

function initModals() {
  document.querySelectorAll('[data-close-modal]').forEach((button) => {
    button.addEventListener('click', () => closeModal(button.dataset.closeModal));
  });

  document.querySelectorAll('.modal-close-btn').forEach((button) => {
    button.addEventListener('click', () => {
      const modal = button.closest('.modal-backdrop');
      if (modal) closeModal(modal.id);
    });
  });

  document.querySelectorAll('.modal-backdrop').forEach((modal) => {
    modal.addEventListener('click', (event) => {
      if (event.target === modal) closeModal(modal.id);
    });
  });

  document.getElementById('cinemaBackBtn')?.addEventListener('click', () => closeModal('cinemaPlayerModal'));
  document.getElementById('modalCloseBtn')?.addEventListener('click', () => closeModal('movieDetailModal'));
}

function initAuthForms() {
  document.querySelectorAll('.auth-tab-btn').forEach((button) => {
    button.addEventListener('click', () => openAuthModal(button.dataset.authTab));
  });

  document.getElementById('loginForm')?.addEventListener('submit', handleAuthSubmit);
  document.getElementById('registerForm')?.addEventListener('submit', handleAuthSubmit);
  document.getElementById('profileForm')?.addEventListener('submit', submitProfile);
  document.getElementById('supportForm')?.addEventListener('submit', submitSupportQuestion);

}

document.addEventListener('DOMContentLoaded', () => {
  bindFAQ();
  initAuthForms();
  initModals();
  initMovieInteractions();
  fetchAuthState();
  fetchQuestions();

  const pageQuery = new URLSearchParams(window.location.search);
  if (pageQuery.has('admin_login')) {
    openAdminLogin();
  }
  if (pageQuery.has('admin_access')) {
    showToast('Please sign in with an admin account to access the dashboard.', 'error');
    window.history.replaceState({}, '', 'index.php');
  }
  if (pageQuery.has('open_support')) {
    openQuestionsModal();
    window.history.replaceState({}, '', 'index.php');
  }

  const navSignInButton = document.getElementById('navSignInBtn');
  if (navSignInButton) navSignInButton.addEventListener('click', () => openAuthModal('login'));
  document.querySelectorAll('[data-admin-login-link]').forEach((link) => {
    link.addEventListener('click', (event) => {
      event.preventDefault();
      openAdminLogin();
    });
  });

  const closeAuth = document.getElementById('closeAuthModal');
  if (closeAuth) closeAuth.addEventListener('click', () => closeModal('authModal'));

  const closeSupport = document.getElementById('closeSupportModal');
  if (closeSupport) closeSupport.addEventListener('click', () => closeModal('questionModal'));
  document.getElementById('editProfileBtn')?.addEventListener('click', openProfileEditor);
  document.getElementById('closeProfileModal')?.addEventListener('click', () => closeModal('profileModal'));

  const profileWrapper = document.getElementById('navProfileWrapper');
  const profileButton = document.getElementById('profileAvatarBtn');
  profileButton?.addEventListener('click', () => {
    profileWrapper?.classList.toggle('profile-menu-open');
  });
  document.addEventListener('click', (event) => {
    if (!event.target.closest('#navProfileWrapper')) closeProfileMenu();
  });

  document.querySelectorAll('.footer-social-btn').forEach((link) => {
    link.addEventListener('click', (event) => {
      event.preventDefault();
      showToast('Social links are ready for your brand setup.');
    });
  });

  document.querySelectorAll('[href="#"]').forEach((link) => {
    link.addEventListener('click', (event) => event.preventDefault());
  });
});

function openAdminLogin() {
  adminLoginRequested = true;
  openAuthModal('login');
  const notice = document.getElementById('adminAuthNotice');
  if (notice) notice.style.display = 'block';
  document.querySelectorAll('.auth-tab-btn').forEach((button) => {
    if (button.dataset.authTab === 'register') button.style.display = 'none';
  });
  const submitButton = document.querySelector('#loginForm button[type="submit"]');
  if (submitButton) submitButton.textContent = 'Sign In to Admin Dashboard';
}
