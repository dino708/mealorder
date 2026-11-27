"""Make the static catalog QR. Run from the Laravel project root."""
import argparse
from urllib.parse import urlparse
from pathlib import Path
import qrcode

parser = argparse.ArgumentParser(description="Generate a QR for the public Meal Order catalog.")
parser.add_argument("url", help="Full catalog URL accessible from the customer's phone")
args = parser.parse_args()
url = urlparse(args.url)
if url.scheme not in ("http", "https") or not url.netloc:
    parser.error("Use a complete http/https URL.")
path = Path("public/images/catalog-qr.png")
path.parent.mkdir(parents=True, exist_ok=True)
qrcode.make(args.url).save(path)
print(f"QR saved to {path}. Test scanning it before printing.")
