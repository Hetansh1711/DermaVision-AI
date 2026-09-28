<?php
// public/api/nearby.php  (UPDATED: less timeouts + better compatibility on lab PCs)
// ✅ Tries multiple Overpass mirrors
// ✅ Forces IPv4 + lower connect timeout + retries
// ✅ Falls back to a JSON error that frontend can use to open Google Maps

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function out($arr, $code = 200) {
  http_response_code($code);
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

function haversineKm($lat1, $lon1, $lat2, $lon2) {
  $R = 6371.0;
  $dLat = deg2rad($lat2 - $lat1);
  $dLon = deg2rad($lon2 - $lon1);
  $a = sin($dLat/2)*sin($dLat/2) + cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLon/2)*sin($dLon/2);
  $c = 2 * atan2(sqrt($a), sqrt(1-$a));
  return $R * $c;
}

function curl_post($url, $body, $attempt = 1) {
  $ch = curl_init($url);

  // IMPORTANT: many lab networks are slow or block IPv6
  $ua = 'DermaVisionAI/1.0 (XAMPP; local; contact: dev@localhost)';

  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,

    // shorter connect timeout, reasonable total timeout
    CURLOPT_CONNECTTIMEOUT => 6,
    CURLOPT_TIMEOUT => 16,

    CURLOPT_ENCODING => '',
    CURLOPT_HTTPHEADER => [
      'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
      'Accept: application/json, text/plain, */*',
      'User-Agent: ' . $ua
    ],

    // Prefer IPv4 to avoid IPv6 DNS stalls
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
  ]);

  // If your PC has broken certificates you can TEMPORARILY enable this:
  // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

  $resp = curl_exec($ch);
  $err  = curl_error($ch);
  $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $ctype = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
  curl_close($ch);

  return [$resp, $err, $code, $ctype, $attempt];
}

// -------------------- INPUT --------------------
$lat = isset($_GET['lat']) ? trim($_GET['lat']) : '';
$lon = isset($_GET['lon']) ? trim($_GET['lon']) : '';
$r   = isset($_GET['r']) ? (int)$_GET['r'] : 7000;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;

if ($lat === '' || $lon === '') out(["success" => false, "error" => "Missing lat/lon"], 400);
if (!is_numeric($lat) || !is_numeric($lon)) out(["success" => false, "error" => "Invalid lat/lon"], 400);

$lat = (float)$lat;
$lon = (float)$lon;

if ($r < 1500) $r = 1500;
if ($r > 25000) $r = 25000;
if ($limit < 1) $limit = 1;
if ($limit > 20) $limit = 20;

// -------------------- OVERPASS QUERY --------------------
$overpassQuery = <<<Q
[out:json][timeout:25];
(
  node(around:$r,$lat,$lon)["healthcare"="dermatologist"];
  way(around:$r,$lat,$lon)["healthcare"="dermatologist"];
  relation(around:$r,$lat,$lon)["healthcare"="dermatologist"];

  node(around:$r,$lat,$lon)["healthcare:speciality"~"dermatology|skin",i];
  way(around:$r,$lat,$lon)["healthcare:speciality"~"dermatology|skin",i];
  relation(around:$r,$lat,$lon)["healthcare:speciality"~"dermatology|skin",i];

  node(around:$r,$lat,$lon)["amenity"="clinic"]["name"];
  node(around:$r,$lat,$lon)["amenity"="hospital"]["name"];
  way(around:$r,$lat,$lon)["amenity"="clinic"]["name"];
  way(around:$r,$lat,$lon)["amenity"="hospital"]["name"];
  relation(around:$r,$lat,$lon)["amenity"="clinic"]["name"];
  relation(around:$r,$lat,$lon)["amenity"="hospital"]["name"];
);
out center tags;
Q;

$payload = "data=" . urlencode($overpassQuery);

// More mirrors + retry per endpoint
$endpoints = [
  "https://overpass.kumi.systems/api/interpreter",
  "https://overpass-api.de/api/interpreter",
  "https://overpass.nchc.org.tw/api/interpreter",
  "https://overpass.openstreetmap.ru/api/interpreter",
];

$decoded = null;
$lastDebug = [];
$respSnippet = "";

// Retry strategy: try each endpoint up to 2 times
foreach ($endpoints as $ep) {
  for ($attempt = 1; $attempt <= 2; $attempt++) {
    [$resp, $err, $code, $ctype, $att] = curl_post($ep, $payload, $attempt);

    $lastDebug = [
      "endpoint" => $ep,
      "attempt" => $att,
      "http_code" => $code,
      "curl_error" => $err,
      "content_type" => $ctype,
    ];

    if ($err) {
      // small backoff
      usleep(250000);
      continue;
    }
    if ($code < 200 || $code >= 300) {
      usleep(250000);
      continue;
    }

    $decoded = json_decode($resp, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded["elements"])) {
      break 2;
    }

    $decoded = null;
    $respSnippet = is_string($resp) ? mb_substr(trim($resp), 0, 280) : "";
    usleep(250000);
  }
}

if (!$decoded) {
  out([
    "success" => false,
    "error" => " ",
    "detail" => $lastDebug,
    "snippet" => $respSnippet,
    "fallback" => [
      "maps_search" => "https://www.google.com/maps/search/dermatologist/@{$lat},{$lon},14z",
      "lat" => $lat,
      "lon" => $lon
    ]
  ], 504);
}

// -------------------- PARSE RESULTS --------------------
$places = [];

foreach ($decoded["elements"] as $el) {
  $tags = isset($el["tags"]) && is_array($el["tags"]) ? $el["tags"] : [];

  $name = $tags["name"] ?? "";
  if (!$name) {
    $isDerm = (($tags["healthcare"] ?? "") === "dermatologist");
    if (!$isDerm) continue;
    $name = "Dermatology Clinic";
  }

  $plat = null; $plon = null;
  if (isset($el["lat"], $el["lon"])) {
    $plat = (float)$el["lat"];
    $plon = (float)$el["lon"];
  } elseif (isset($el["center"]["lat"], $el["center"]["lon"])) {
    $plat = (float)$el["center"]["lat"];
    $plon = (float)$el["center"]["lon"];
  } else {
    continue;
  }

  $type = "";
  if (($tags["healthcare"] ?? "") === "dermatologist") $type = "Dermatologist";
  elseif (($tags["amenity"] ?? "") === "hospital") $type = "Hospital";
  elseif (($tags["amenity"] ?? "") === "clinic") $type = "Clinic";
  elseif (!empty($tags["healthcare:speciality"])) $type = "Dermatology";

  $addressParts = [];
  foreach (["addr:housenumber","addr:street","addr:suburb","addr:city","addr:state"] as $k) {
    if (!empty($tags[$k])) $addressParts[] = $tags[$k];
  }
  $address = implode(", ", $addressParts);

  $distKm = haversineKm($lat, $lon, $plat, $plon);

  $places[] = [
    "name" => (string)$name,
    "type" => (string)$type,
    "address" => (string)$address,
    "lat" => $plat,
    "lon" => $plon,
    "distanceKm" => round($distKm, 2),
    "maps" => "https://www.google.com/maps/search/?api=1&query={$plat},{$plon}"
  ];
}

usort($places, fn($a,$b) => $a["distanceKm"] <=> $b["distanceKm"]);
$places = array_slice($places, 0, $limit);

out([
  "success" => true,
  "count" => count($places),
  "places" => $places
]);
