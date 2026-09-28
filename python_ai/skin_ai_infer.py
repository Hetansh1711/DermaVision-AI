import os
import sys
import json
import time
import argparse

import numpy as np
from PIL import Image

# MUST be set before importing tensorflow
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "3"
os.environ["TF_ENABLE_ONEDNN_OPTS"] = "0"

import tensorflow as tf
tf.get_logger().setLevel("ERROR")

BASE_DIR = os.path.dirname(__file__)

MODEL_PATH = os.path.join(BASE_DIR, "model.tflite")
LABELS_PATH = os.path.join(BASE_DIR, "labels.txt")          # optional
DB_PATH = os.path.join(BASE_DIR, "disease_db.json")         # optional


def eprint(*args):
    """Print to stderr (safe: won't break JSON output)."""
    print(*args, file=sys.stderr)


def load_labels(path: str):
    try:
        with open(path, "r", encoding="utf-8") as f:
            labels = [line.strip() for line in f if line.strip()]
        return labels
    except Exception:
        return []


def load_disease_db(path: str):
    """Your JSON: list of objects with label_index + name + etc."""
    try:
        with open(path, "r", encoding="utf-8") as f:
            data = json.load(f)
    except Exception:
        return {}

    if not isinstance(data, list):
        return {}

    lookup = {}
    for item in data:
        if not isinstance(item, dict):
            continue
        if "label_index" not in item:
            continue
        try:
            idx = int(item["label_index"])
            lookup[idx] = item
        except Exception:
            continue
    return lookup


def preprocess_image(image_path: str, w: int, h: int):
    """
    Strong preprocessing:
    - loads RGB
    - resizes with good filter
    - returns float32 0..1 base array (H,W,3)
    """
    img = Image.open(image_path).convert("RGB")
    img = img.resize((w, h), Image.BILINEAR)
    arr = np.asarray(img).astype(np.float32) / 255.0
    return arr


def apply_input_transform(x_float01: np.ndarray, in_details: dict):
    """
    Converts float 0..1 image to the exact input tensor type model expects:
    - float32 models: pass float 0..1 (or sometimes -1..1 if needed)
    - uint8/int8 quantized models: use scale/zero_point properly
    """
    in_dtype = in_details["dtype"]
    quant = in_details.get("quantization", (0.0, 0))

    # If quantization exists (scale > 0), convert properly
    scale, zero_point = quant if isinstance(quant, tuple) else (0.0, 0)

    if in_dtype == np.float32:
        # Most skin models use 0..1 float. If your model needs -1..1, uncomment:
        # x_float01 = (x_float01 * 2.0) - 1.0
        return x_float01.astype(np.float32)

    # Quantized (uint8 / int8)
    if scale and scale > 0:
        x_q = (x_float01 / scale) + zero_point
    else:
        # fallback: typical uint8 expects 0..255
        x_q = x_float01 * 255.0

    if in_dtype == np.uint8:
        x_q = np.clip(x_q, 0, 255).astype(np.uint8)
        return x_q

    if in_dtype == np.int8:
        x_q = np.clip(x_q, -128, 127).astype(np.int8)
        return x_q

    # other types (rare)
    return x_q.astype(in_dtype)


def dequantize_output(raw: np.ndarray, out_details: dict):
    """
    If model output is quantized, dequantize to float.
    """
    out_dtype = out_details["dtype"]
    quant = out_details.get("quantization", (0.0, 0))
    scale, zero_point = quant if isinstance(quant, tuple) else (0.0, 0)

    if out_dtype in (np.uint8, np.int8) and scale and scale > 0:
        return (raw.astype(np.float32) - float(zero_point)) * float(scale)

    return raw.astype(np.float32)


def softmax(x: np.ndarray):
    x = x.astype(np.float32)
    x = x - np.max(x)
    ex = np.exp(x)
    return ex / (np.sum(ex) + 1e-9)


def main():
    t0 = time.time()

    parser = argparse.ArgumentParser()
    parser.add_argument("--image", required=True, help="Full path to image")
    parser.add_argument("--topk", type=int, default=5, help="Debug top-k predictions to stderr")
    args = parser.parse_args()

    try:
        if not os.path.isfile(args.image):
            raise FileNotFoundError(f"Image not found: {args.image}")

        labels = load_labels(LABELS_PATH)
        disease_lookup = load_disease_db(DB_PATH)

        interpreter = tf.lite.Interpreter(model_path=MODEL_PATH)
        interpreter.allocate_tensors()

        in_details = interpreter.get_input_details()[0]
        out_details = interpreter.get_output_details()[0]

        in_index = in_details["index"]
        out_index = out_details["index"]

        # Input shape: [1, H, W, 3]
        in_shape = in_details["shape"]
        h = int(in_shape[1])
        w = int(in_shape[2])

        # Preprocess -> float 0..1
        x_float01 = preprocess_image(args.image, w, h)

        # Convert to exact model dtype (handles quantization)
        x = apply_input_transform(x_float01, in_details)

        # Add batch
        x = np.expand_dims(x, axis=0)

        # Inference
        interpreter.set_tensor(in_index, x)
        interpreter.invoke()

        raw_out = interpreter.get_tensor(out_index)[0]

        # Convert output to float scores
        scores = dequantize_output(raw_out, out_details)

        # Some models output logits, some output probabilities.
        # If values look not like probabilities, run softmax.
        if np.any(scores < 0) or np.any(scores > 1.0 + 1e-3) or abs(np.sum(scores) - 1.0) > 0.05:
            probs = softmax(scores)
        else:
            probs = scores
            # normalize just in case
            s = float(np.sum(probs))
            if s > 0:
                probs = probs / s

        idx = int(np.argmax(probs))
        conf = float(probs[idx])  # 0..1

        # Debug top-k to stderr (won't break JSON)
        k = max(1, int(args.topk))
        top_idx = np.argsort(-probs)[:k]
        eprint("Top predictions:")
        for j in top_idx:
            name = disease_lookup.get(int(j), {}).get("name") if disease_lookup else None
            if not name and labels and int(j) < len(labels):
                name = labels[int(j)]
            if not name:
                name = f"Class {int(j)}"
            eprint(f"  {int(j)} -> {name} ({probs[int(j)]*100:.2f}%)")

        # Pick label name
        db_item = disease_lookup.get(idx, {})
        if isinstance(db_item, dict) and db_item.get("name"):
            label_name = str(db_item["name"])
        elif labels and idx < len(labels):
            label_name = labels[idx]
        else:
            label_name = "Unknown condition"

        # Attach details from DB (optional)
        if isinstance(db_item, dict) and db_item:
            severity = db_item.get("severity", "Unknown")
            description = db_item.get("description", "No description available.")
            treatment = db_item.get("treatment_options", ["Please consult a dermatologist."])
            products = db_item.get("recommended_products", [])
            home = db_item.get("home_remedies_supportive", [])
            warning = db_item.get("warning", "Consult a dermatologist for accurate diagnosis.")
        else:
            severity = "Unknown"
            description = "No detailed description available for this label."
            treatment = ["Please consult a dermatologist for a proper diagnosis."]
            products = []
            home = []
            warning = "Consult a dermatologist for accurate diagnosis."

        # Ensure lists
        if not isinstance(treatment, list):
            treatment = [str(treatment)]
        if not isinstance(products, list):
            products = [str(products)]
        if not isinstance(home, list):
            home = [str(home)]

        out = {
            "success": True,
            "label_index": idx,
            "label_name": label_name,
            "confidence": round(conf * 100, 1),
            "severity": severity,
            "description": description,
            "treatment": treatment,
            "products": products,
            "home_remedies": home,
            "warnings": warning,
            "inference_ms": round((time.time() - t0) * 1000, 2),
        }

        # IMPORTANT: stdout ONLY JSON
        sys.stdout.write(json.dumps(out, ensure_ascii=False))

    except Exception as e:
        err = {
            "success": False,
            "error": f"AI processing error: {str(e)}",
            "inference_ms": round((time.time() - t0) * 1000, 2),
        }
        sys.stdout.write(json.dumps(err, ensure_ascii=False))


if __name__ == "__main__":
    main()
