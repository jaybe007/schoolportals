<div style="max-width: 800px; margin: 3rem auto; padding: 0 1.5rem;">
    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #0a1128, #1e3a8a); color: white; padding: 1.75rem 2rem;">
            <div>
                <h1 style="color: white; font-size: 1.5rem; font-weight: 800; margin-bottom: 0.25rem;">
                    Online Admission Application
                </h1>
                <p style="color: #94a3b8; font-size: 0.875rem;">
                    <?= e($school['name']) ?> &bull; Session 2025/2026 Enrollment
                </p>
            </div>
        </div>
        <div class="card-body" style="padding: 2rem;">
            <form method="POST" action="<?= url('site/' . $currentSlug . '/apply') ?>">
                <?= csrf_field() ?>

                <div style="font-weight: 700; color: #1e3a8a; font-size: 1rem; margin-bottom: 1rem; border-bottom: 2px solid #eff6ff; padding-bottom: 0.5rem;">
                    1. Student Applicant Information
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="applicant_first_name" class="form-control" placeholder="e.g. Chukwuma" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="applicant_last_name" class="form-control" placeholder="e.g. Adeleke" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Gender *</label>
                        <select name="applicant_gender" class="form-select" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Birth *</label>
                        <input type="date" name="date_of_birth" class="form-control" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Applying for Class Level *</label>
                        <select name="applied_class_level_id" class="form-select" required>
                            <option value="">Select Class...</option>
                            <?php foreach ($classLevels as $cl): ?>
                                <option value="<?= $cl['id'] ?>"><?= e($cl['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Previous School Attended</label>
                        <input type="text" name="previous_school" class="form-control" placeholder="e.g. Corona Primary School">
                    </div>
                </div>

                <div style="font-weight: 700; color: #1e3a8a; font-size: 1rem; margin: 1.5rem 0 1rem; border-bottom: 2px solid #eff6ff; padding-bottom: 0.5rem;">
                    2. Parent / Legal Guardian Contact
                </div>

                <div class="form-group">
                    <label class="form-label">Parent / Guardian Full Name *</label>
                    <input type="text" name="parent_name" class="form-control" placeholder="e.g. Dr. & Mrs. O. Adeleke" required>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Contact Phone Number *</label>
                        <input type="text" name="parent_phone" class="form-control" placeholder="+234 803 000 0000" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="parent_email" class="form-control" placeholder="parent@example.com" required>
                    </div>
                </div>

                <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem;">
                        Submit Online Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
