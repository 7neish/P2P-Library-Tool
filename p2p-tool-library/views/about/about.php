<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
$page_title = 'About Us';
require __DIR__ . '/../shared/dashboard_shell_top.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="card shadow-lg border-0">
        <div class="card-body p-5">
          <h1 class="display-5 fw-bold mb-4">About P2P Tool Library</h1>
          <p class="lead text-muted">We are a community‑driven platform that connects tool owners with people who need them, promoting sharing, sustainability, and local trust.</p>
          
          <div class="row mt-5 g-4">
            <div class="col-md-4">
              <div class="text-center">
                <i class="bi bi-people-fill display-4 text-primary"></i>
                <h4 class="mt-3">Community First</h4>
                <p class="small text-muted">Built by students, for the community. Our trust score and multi‑tier pricing ensure fairness.</p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="text-center">
                <i class="bi bi-shield-check display-4 text-success"></i>
                <h4 class="mt-3">Secure & Insured</h4>
                <p class="small text-muted">Every rental is protected with deposits held in escrow and strict KYC verification.</p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="text-center">
                <i class="bi bi-geo-alt-fill display-4 text-danger"></i>
                <h4 class="mt-3">Local & Nearby</h4>
                <p class="small text-muted">Our geospatial discovery helps you find tools within walking distance, reducing waste.</p>
              </div>
            </div>
          </div>
          
          <hr class="my-5">
          
          <h3 class="fw-bold mb-3">Our Team</h3>
          <p class="text-muted">We are a group of passionate computer science students from Cairo University, dedicated to building innovative solutions for everyday problems.</p>
          <div class="row mt-4">
            <div class="col-md-4 text-center">
              <img src="/assets/img/team1.jpg" class="rounded-circle mb-2" width="120" height="120" alt="Team">
              <h6>Ahmed Hassan</h6>
              <small class="text-muted">Full‑Stack Developer</small>
            </div>
            <div class="col-md-4 text-center">
              <img src="/assets/img/team2.jpg" class="rounded-circle mb-2" width="120" height="120" alt="Team">
              <h6>Mariam Ali</h6>
              <small class="text-muted">UI/UX Designer</small>
            </div>
            <div class="col-md-4 text-center">
              <img src="/assets/img/team3.jpg" class="rounded-circle mb-2" width="120" height="120" alt="Team">
              <h6>Khaled Omar</h6>
              <small class="text-muted">Backend Engineer</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../shared/dashboard_shell_bottom.php'; ?>