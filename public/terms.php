<?php

session_start();
$currentUser = $_SESSION['user'] ?? null;

require __DIR__ . '/partials/header.php';
?>
<body>
<?php require __DIR__ . '/partials/nav.php'; ?>

<main class="page">
  <section class="section section-narrow">
    <h1>Terms &amp; Conditions</h1>
    <p class="page-subtitle">
      Please read before using the AI Dermatologist.
    </p>

    <div class="card">
      <h2 class="mt-0">1. No Medical Diagnosis</h2>
      <p>
        DermaVision AI is <strong>not</strong> a doctor. It does not give a 
        medical diagnosis. It only provides educational suggestions based on
        the uploaded image.
      </p>

      <h2 class="mt-16">2. Always Consult a Dermatologist</h2>
      <p>
        You must always consult a qualified dermatologist before starting,
        changing, or stopping any treatment.
      </p>

      <h2 class="mt-16">3. Experimental AI</h2>
      <p>
        The AI model may be inaccurate or wrong. The creators of this project
        are not responsible for any decisions made using the AI result.
      </p>

      <h2 class="mt-16">4. Appropriate Use</h2>
      <p>
        Do not upload inappropriate, abusive, or non-skin-related images.
        The tool is only for educational skin health support.
      </p>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
