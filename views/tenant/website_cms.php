<div class="page-header">
    <div>
        <h1 class="page-title">School Public Website CMS</h1>
        <p class="page-subtitle">Customize public webpage content, banners, mission statements, and admissions announcements</p>
    </div>
    <div>
        <a href="<?= url('site/' . (current_school()['slug'] ?? 'apex')) ?>" class="btn btn-secondary" target="_blank">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
            <span>Live School Website</span>
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
    <!-- Pages list -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Manage Website Pages</div>
        </div>
        <div class="card-body" style="padding: 0.75rem;">
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <?php foreach ($pages as $p): ?>
                    <button type="button" class="btn btn-secondary" style="justify-content: space-between; text-align: left;" onclick="loadPageData('<?= e($p['page_slug']) ?>', '<?= e(addslashes($p['title'])) ?>', '<?= e(addslashes($p['banner_title'])) ?>', '<?= e(addslashes($p['banner_subtitle'])) ?>', '<?= e(addslashes($p['content'])) ?>')">
                        <span style="font-weight: 600; text-transform: capitalize;"><?= e($p['page_slug']) ?> Page</span>
                        <span class="badge badge-success">Published</span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Page Editor Form -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Edit Page Content: <span id="currentEditingPageSlug" style="color: #1e3a8a; text-transform: uppercase;">home</span></div>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('school/website-cms') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="page_slug" id="cmsPageSlug" value="home">

                <div class="form-group">
                    <label class="form-label">Page Title *</label>
                    <input type="text" name="title" id="cmsTitle" class="form-control" value="Welcome to <?= e(current_school()['name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Hero Banner Headline *</label>
                    <input type="text" name="banner_title" id="cmsBannerTitle" class="form-control" value="Inspiring Excellence, Fostering Global Leaders" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Hero Banner Subtitle</label>
                    <textarea name="banner_subtitle" id="cmsBannerSubtitle" class="form-control" rows="2">Providing holistic academic education, moral integrity and 21st-century technological acumen.</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Body Content / Introduction *</label>
                    <textarea name="content" id="cmsContent" class="form-control" rows="5" required>At our institution, we believe in unlocking the unique potential of every student through innovative curricula, world-class laboratory facilities, and dedicated faculty.</textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Update Page Content</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function loadPageData(slug, title, bannerTitle, bannerSubtitle, content) {
    document.getElementById('currentEditingPageSlug').textContent = slug;
    document.getElementById('cmsPageSlug').value = slug;
    document.getElementById('cmsTitle').value = title;
    document.getElementById('cmsBannerTitle').value = bannerTitle;
    document.getElementById('cmsBannerSubtitle').value = bannerSubtitle;
    document.getElementById('cmsContent').value = content;
}
</script>
