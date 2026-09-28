<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
$currentUser = getCurrentUser();
?>
<?php include __DIR__ . '/partials/header.php'; ?>
<body>
<?php include __DIR__ . '/partials/nav.php'; ?>

<main class="page">
  <section class="section">

    <h1>AI Dermatologist</h1>
    <p class="page-subtitle">
      Upload a clear skin photo or use your camera. DermaVision AI will suggest possible skin conditions,
      severity, confidence score, and supportive treatment options.
    </p>

    <div class="card card-soft mb-16">
      <strong>Logged in as: <?php echo htmlspecialchars($currentUser['name'] ?? 'User'); ?></strong><br>
      <span class="text-soft">
        Not a medical diagnosis — educational decision-support tool only.
      </span>
    </div>

    <div class="ai-grid">
      <div class="card">
        <h2 class="mt-0 mb-8">Scan your skin</h2>
        <p class="text-soft mb-12">
          Use a well-lit, close-up photo. Avoid makeup, filters, and heavy editing for more reliable results.
        </p>

        <div class="stepper mb-12">
          <div class="step step-active">
            <div class="step-circle">1</div> Upload / Capture Image
          </div>
          <div class="step">
            <div class="step-circle">2</div> AI Analyzes
          </div>
          <div class="step">
            <div class="step-circle">3</div> View Report
          </div>
        </div>

        <div class="camera-toggle mb-8">
          <button type="button" class="btn-outline btn-small" id="btn-upload-mode">
            📁 Upload from device
          </button>
          <button type="button" class="btn-outline btn-small" id="btn-camera-mode">
            📷 Use camera
          </button>
        </div>

        <form id="ai-form" enctype="multipart/form-data">
          <label class="form-label" for="skin_image">Skin Image (JPG / PNG / WEBP)</label>
          <input
            type="file"
            id="skin_image"
            name="skin_image"
            accept="image/jpeg,image/png,image/webp"
          />

          <p class="text-soft mt-4 mb-4">
            Tip: Take the photo in natural light, focus on the affected area, and keep the image sharp.
          </p>

          <div id="image-preview-wrapper" class="image-preview-wrapper">
            <span class="text-muted"></span>
            <img id="preview-image" src="" alt="" class="hidden" />
          </div>

          <div id="camera-wrapper" class="camera-wrapper hidden mt-8">
            <video id="camera-video" autoplay playsinline></video>
            <canvas id="camera-canvas" class="hidden"></canvas>
            <button type="button" class="btn-primary btn-small mt-8" id="capture-photo">
              Capture Photo
            </button>
          </div>

          <button type="submit" id="analyze-btn" class="btn-primary mt-12" style="width: 100%;">
            Analyze Image
          </button>

          <div id="ai-error" class="alert error hidden mt-12"></div>
        </form>
      </div>

      <div class="card card-highlight" id="ai-result-card">
        <p class="text-soft mb-4" id="ai-status">
          No analysis yet. Upload or capture an image and click “Analyze Image” to see your report.
        </p>

        <h2 class="mt-0 mb-8">AI Result</h2>

        <div id="ai-result-body" class="ai-report">

        </div>
      </div>
    </div>

  </section>
</main>

<footer class="footer">
  © 2025 DermaVision AI · ⚠ Educational support only — not a diagnosis
  <br>
  <a href="privacy.php">Privacy Policy</a> ·
  <a href="terms.php">Terms & Conditions</a>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>
