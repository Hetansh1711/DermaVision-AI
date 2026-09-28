<?php
// public/api/diagnose.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

function respond(array $data, int $code = 200): void {
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  exit;
}

function isWindows(): bool {
  return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(["success" => false, "error" => "Invalid request method. Use POST."], 405);
  }

  if (!isset($_FILES['skin_image']) || $_FILES['skin_image']['error'] !== UPLOAD_ERR_OK) {
    $err = $_FILES['skin_image']['error'] ?? 'no_file';
    respond(["success" => false, "error" => "No valid file uploaded.", "upload_error" => $err], 400);
  }

  // ✅ Validate mime (basic)
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($_FILES['skin_image']['tmp_name']);
  $allowed = ['image/jpeg', 'image/png', 'image/webp'];
  if (!in_array($mime, $allowed, true)) {
    respond(["success" => false, "error" => "Invalid file type: $mime. Allowed: JPG/PNG/WEBP"], 400);
  }

  // ✅ Paths (based on your folder structure)
  $root = realpath(__DIR__ . "/../.."); // -> derma_ai_website/public/api/../.. = derma_ai_website
  if ($root === false) {
    respond(["success" => false, "error" => "Server path error: project root not resolved."], 500);
  }

  $pythonExe = $root . "\\python_ai\\venv\\Scripts\\python.exe";
  $scriptPy  = $root . "\\python_ai\\skin_ai_infer.py";

  if (!file_exists($pythonExe)) {
    respond(["success" => false, "error" => "Python venv not found.", "expected" => $pythonExe], 500);
  }
  if (!file_exists($scriptPy)) {
    respond(["success" => false, "error" => "AI engine script not found.", "expected" => $scriptPy], 500);
  }

  // ✅ Upload destination
  $uploadDir = $root . "\\public\\uploads";
  if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
  }
  if (!is_dir($uploadDir)) {
    respond(["success" => false, "error" => "Upload directory not writable.", "dir" => $uploadDir], 500);
  }

  $ext = match($mime) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    default      => 'img'
  };

  $safeName = "scan_" . date("Ymd_His") . "_" . bin2hex(random_bytes(4)) . "." . $ext;
  $destPath = $uploadDir . "\\" . $safeName;

  if (!move_uploaded_file($_FILES['skin_image']['tmp_name'], $destPath)) {
    respond(["success" => false, "error" => "Failed to save uploaded file."], 500);
  }

  // ✅ Build command safely (IMPORTANT)
  // We run: python skin_ai_infer.py --image "path"
  // We also force UTF-8 safe output:
  // -X utf8 helps on Windows for consistent stdout
  $cmd = '"' . $pythonExe . '" -X utf8 "' . $scriptPy . '" --image "' . $destPath . '"';

  // ✅ Capture output + errors
  // 2>&1 is critical (otherwise PHP gets partial output and JS says "invalid JSON")
  $cmdWithErr = $cmd . " 2>&1";

  $outputLines = [];
  $exitCode = 0;

  // Use exec to capture output lines + exit code
  exec($cmdWithErr, $outputLines, $exitCode);

  $raw = trim(implode("\n", $outputLines));

  // ✅ Some TF prints INFO lines. We must extract JSON object from raw.
  // Find first "{" and last "}" and slice.
  $start = strpos($raw, "{");
  $end   = strrpos($raw, "}");
  $jsonText = null;

  if ($start !== false && $end !== false && $end > $start) {
    $jsonText = substr($raw, $start, ($end - $start + 1));
  }

  if ($jsonText === null) {
    respond([
      "success" => false,
      "error" => "AI returned invalid response (no JSON detected).",
      "exit_code" => $exitCode,
      "raw_output" => mb_substr($raw, 0, 2000)
    ], 500);
  }

  $decoded = json_decode($jsonText, true);

  if (!is_array($decoded)) {
    respond([
      "success" => false,
      "error" => "AI returned invalid JSON (cannot decode).",
      "exit_code" => $exitCode,
      "json_snippet" => mb_substr($jsonText, 0, 2000),
      "raw_output" => mb_substr($raw, 0, 2000)
    ], 500);
  }

  // ✅ Always include server debug fields (helps you)
  $decoded["_server_exit_code"] = $exitCode;
  $decoded["_server_raw_prefix"] = mb_substr($raw, 0, 400);

  respond($decoded, 200);

} catch (Throwable $e) {
  respond([
    "success" => false,
    "error" => "Unexpected server error: " . $e->getMessage()
  ], 500);
}
