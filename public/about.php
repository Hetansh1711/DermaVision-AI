<?php
session_start();
$currentUser = $_SESSION['user'] ?? null;

require __DIR__ . '/partials/header.php';
?>
<body>
<?php require __DIR__ . '/partials/nav.php'; ?>

<main class="page">
  <section class="section section-narrow">
    <h1>About DermaVision AI</h1>
    <p class="page-subtitle">
      Student-built AI assistant to help people understand common skin concerns.
    </p>

    <div class="card">
      <p>
        DermaVision AI is a learning project that combines 
        <strong>computer vision</strong> and <strong>dermatology knowledge</strong>
        to give <em>educational</em> insights about skin conditions.
      </p>

      <p class="mt-8">
        You can upload a clear skin photo, and our AI will try to:
      </p>
      <ul class="mt-8">
        <li>Suggest a possible skin condition (like acne, eczema, etc.)</li>
        <li>Estimate severity level (mild, moderate, severe)</li>
        <li>Show a confidence score</li>
        <li>List supportive tips and treatment options to discuss with a doctor</li>
      </ul>

      <p class="mt-12">
        This project is <strong>not</strong> a replacement for a real dermatologist.
        It is only a <strong>decision-support and educational tool</strong>.
      </p>

      <p class="mt-12">
        For any serious, painful, or rapidly worsening skin problems, 
        please visit a qualified dermatologist or a nearby hospital immediately.
      </p>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
