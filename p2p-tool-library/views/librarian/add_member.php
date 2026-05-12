<?php require __DIR__ . '/../shared/dashboard_shell_top.php'; ?>

<div class="container mt-4">
    <h2><i class="bi bi-person-plus"></i> Add New Member</h2>
    <hr>
    <div class="card">
        <div class="card-body">
            
            <form action="/views/librarian/members.php" method="POST">
                <input type="hidden" name="action" value="add_member">
                
                <div class="mb-3">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Role *</label>
                    <select name="role" class="form-select" required>
                        <option value="">Choose...</option>
                        <option value="MEMBER">Member</option>
                        <option value="LIBRARIAN">Librarian</option>
                        <option value="ADMIN">ADMIN</option>
                        <option value="TECHNICIAN">Technician</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Save Member</button>
                <a href="/views/librarian/members.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>