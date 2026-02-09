<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Movies Grid</title>

    <!-- Bootstrap (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .movie-card img {
            width: 100%;
            height: 340px;
            object-fit: cover;
            background: #f2f2f2;
        }
        .movie-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.5em;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand">MoviesDatabase - Teste Back-end - Parte 2</span>
    </div>
</nav>

<main class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h4 mb-0">Títulos</h1>
            <small class="text-muted">
                  Consumo via <code>/api/titles</code>.
                  Fallback de imagem implementado na tag <code>&lt;img&gt;</code> para casos de bloqueio do provedor.
            </small>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="badge text-bg-secondary" id="pageBadge">Página: -</span>
            <span class="badge text-bg-info" id="countBadge">Itens: -</span>
        </div>
    </div>

    <!-- Alerts -->
    <div id="alertBox" class="alert d-none" role="alert"></div>

    <!-- Loading -->
    <div id="loading" class="d-none mb-3">
        <div class="d-flex align-items-center gap-2">
            <div class="spinner-border" role="status" aria-hidden="true"></div>
            <span>Carregando títulos...</span>
        </div>
    </div>

    <!-- Grid -->
    <div id="grid" class="row g-3"></div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <button id="prevBtn" class="btn btn-outline-secondary" disabled>← Anterior</button>

        <div class="d-flex align-items-center gap-2">
            <input id="pageInput" type="number" min="1" class="form-control" style="width:110px" value="1">
            <button id="goBtn" class="btn btn-primary">Ir</button>
        </div>

        <button id="nextBtn" class="btn btn-outline-primary" disabled>Próxima →</button>
    </div>
</main>

<script>
    const gridEl = document.getElementById('grid');
    const loadingEl = document.getElementById('loading');
    const alertEl = document.getElementById('alertBox');

    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const goBtn = document.getElementById('goBtn');
    const pageInput = document.getElementById('pageInput');

    const pageBadge = document.getElementById('pageBadge');
    const countBadge = document.getElementById('countBadge');

    let currentPage = 1;
    let nextPage = null;

    function escapeHtml(str) {
        return String(str)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function showLoading(show) {
        loadingEl.classList.toggle('d-none', !show);
    }

    function showAlert(message, type = 'danger') {
        alertEl.className = `alert alert-${type}`;
        alertEl.textContent = message;
        alertEl.classList.remove('d-none');
    }

    function hideAlert() {
        alertEl.classList.add('d-none');
        alertEl.textContent = '';
    }

    function renderItems(items) {
        gridEl.innerHTML = '';

        if (!items || items.length === 0) {
            gridEl.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-warning mb-0">
                        Nenhum título encontrado nesta página.
                    </div>
                </div>
            `;
            return;
        }

        const html = items.map(item => {
            const title = item.title ?? 'Sem título';
            const safeTitle = escapeHtml(title);

            const year = item.year ?? '—';

            const poster = (item.poster ?? '').trim();
            const safePoster = escapeHtml(poster);

            return `
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card movie-card h-100 shadow-sm">
                        ${safePoster
                            ? `<img
                                src="${safePoster}"
                                alt="${safeTitle}"
                                loading="lazy"
                                onerror="this.onerror=null; this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%22300%22%20height%3D%22450%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%23e9ecef%22/%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20dominant-baseline%3D%22middle%22%20text-anchor%3D%22middle%22%20fill%3D%22%236c757d%22%20font-family%3D%22Arial%22%20font-size%3D%2220%22%3ESem%20Imagem%3C/text%3E%3C/svg%3E';"
                              />`
                            : `<div class="d-flex align-items-center justify-content-center bg-secondary text-white" style="height:340px;">
                                   Imagem indisponível
                               </div>`
                        }
                        <div class="card-body">
                            <div class="movie-title fw-semibold">${safeTitle}</div>
                            <div class="text-muted small mt-1">Ano: ${year}</div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        gridEl.innerHTML = html;
    }

    function updatePaginationButtons(hasMore) {
        prevBtn.disabled = currentPage <= 1;
        nextBtn.disabled = !hasMore;
    }

    function updateBadges(count) {
        pageBadge.textContent = `Página: ${currentPage}`;
        countBadge.textContent = `Itens: ${count ?? '-'}`;
    }

    async function loadPage(page) {
        hideAlert();
        showLoading(true);

        try {
            const resp = await fetch(`/api/titles?page=${page}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!resp.ok) {
                const err = await resp.json().catch(() => null);
                throw new Error(err?.message ?? `Erro HTTP ${resp.status}`);
            }

            const data = await resp.json();

            currentPage = data.page ?? page;
            nextPage = data.nextPage ?? null;

            const items = data.items ?? [];
            const hasMore = data.hasMore === true;
            const count = data.count ?? items.length;

            pageInput.value = currentPage;

            renderItems(items);
            updatePaginationButtons(hasMore);
            updateBadges(count);

        } catch (e) {
            showAlert(e.message || 'Falha ao carregar dados.');
            renderItems([]);
            updatePaginationButtons(false);
            updateBadges(null);
        } finally {
            showLoading(false);
        }
    }

    // Eventos
    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) loadPage(currentPage - 1);
    });

    nextBtn.addEventListener('click', () => {
        const target = nextPage ?? (currentPage + 1);
        loadPage(target);
    });

    goBtn.addEventListener('click', () => {
        const target = parseInt(pageInput.value || '1', 10);
        loadPage(isNaN(target) ? 1 : Math.max(1, target));
    });

    pageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') goBtn.click();
    });

    // Inicial
    loadPage(1);
</script>

</body>
</html>
