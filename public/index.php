<?php
session_start();
$currentUser = $_SESSION['user'] ?? null;

include __DIR__ . '/partials/header.php';
?>
<body>
<?php include __DIR__ . '/partials/nav.php'; ?>

<main class="page">
  <section class="hero">
    <div class="hero-main">
      <h1>Smarter Skin Insights with DermaVision AI</h1>
      <p>
        Upload a skin photo and let our AI provide possible conditions, severity,
        and supportive treatment guidance. This is an educational tool — not a medical diagnosis.
      </p>

      <div class="hero-badge-row">
        <span class="hero-badge">AI-powered analysis</span>
        <span class="hero-badge">Fast & private</span>
        <span class="hero-badge">Dermatology-inspired</span>
      </div>

      <div class="hero-cta-row">
        <?php if (!$currentUser): ?>
          <a href="signup.php" class="btn-primary">Create Free Account</a>
          <a href="login.php" class="btn-outline">Login</a>
        <?php else: ?>
          <a href="ai-dermatologist.php" class="btn-primary">Start Skin Scan</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="hero-features">
      <div class="card">
        <h3>📸 Upload Skin Photos</h3>
        <p>Use clear, well-lit photos for best results. No filters or makeup.</p>
      </div>

      <div class="card">
        <h3>🤖 AI Detection</h3>
        <p>The AI evaluates patterns linked to common skin conditions.</p>
      </div>

      <div class="card">
        <h3>📄 Instant Report</h3>
        <p>Receive a structured report showing severity, confidence, and care tips.</p>
      </div>
    </div>
  </section>
</main>

<footer class="footer">
  © 2025 DermaVision AI  
  · ⚠ Educational support only — not a diagnosis  
  <br>
  <a href="privacy.php">Privacy Policy</a> ·
  <a href="terms.php">Terms & Conditions</a>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>
