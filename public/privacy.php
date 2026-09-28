<?php

session_start();
$currentUser = $_SESSION['user'] ?? null;

require __DIR__ . '/partials/header.php';
?>
<body>
<?php require __DIR__ . '/partials/nav.php'; ?>

<main class="page">
  <section class="section section-narrow">
    <h1>Privacy Policy</h1>
    <p class="page-subtitle">
      How DermaVision AI handles your data and images.
    </p>

    <div class="card">
      <h2 class="mt-0">1. Images and Scan Data</h2>
      <p>
        Uploaded photos are used <strong>only</strong> to run the AI analysis.
        They are processed on this server and are not shared with third parties.
      </p>

      <p class="mt-8">
        For this student project, images may be temporarily stored in
        server memory or temporary files just for the analysis. We do not
        publish or sell your images.
      </p>

      <h2 class="mt-16">2. Accounts and Personal Information</h2>
      <p>
        When you sign up, we store your name, email, and a <strong>hashed password</strong>.
        We never store your password in plain text.
      </p>

      <h2 class="mt-16">3. Educational Use Only</h2>
      <p>
        This tool is for educational and demo purposes. It is not a medical device.
      </p>

      <h2 class="mt-16">4. Contact</h2>
      <p>
        For any privacy concerns about this project demo, please contact the project owner
        (student / teacher) who is hosting this website.
      </p>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
