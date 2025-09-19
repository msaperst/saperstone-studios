import os
import gzip
import requests
from urllib.parse import urljoin
from datetime import datetime, timezone, timedelta

# TODO
# - want to pull galleries, not generic gallery pages
# - some blog categories?


# === CONFIGURATION ===
SITE_URL = "https://saperstonestudios.com/"     # your base URL
ROOT_DIR = "public"                          # local path to scan
OUTPUT_FILE = "public/sitemap.xml"           # where to save the sitemap
GZIP_FILE = "public/sitemap.xml.gz"         # compressed sitemap
VALID_EXTENSIONS = {".php"}                     # file types to include
IGNORE_FOLDERS = {"api", "user"}                # folders to ignore
RECENT_DAYS = 30                                # days considered "recent"


def is_url_ok(url: str) -> bool:
    """Check if URL returns HTTP 200."""
    try:
        response = requests.head(url, allow_redirects=True, timeout=5)
        return response.status_code == 200
    except requests.RequestException:
        return False


def calculate_priority(rel_url):
    """Assign priority based on folder depth."""
    depth = rel_url.count("/")  # number of slashes = depth
    priority = max(1.0 - (depth * 0.2), 0.2)  # never lower than 0.2
    return f"{priority:.1f}"


def calculate_changefreq(rel_url, filepath):
    """Decide changefreq based on lastmod date and depth."""
    last_modified = datetime.fromtimestamp(os.path.getmtime(filepath), tz=timezone.utc)
    now = datetime.now(timezone.utc)

    # Recently updated files → daily
    if now - last_modified <= timedelta(days=RECENT_DAYS):
        return "daily"

    # Otherwise, fallback to depth-based
    depth = rel_url.count("/")
    if depth <= 1:
        return "weekly"
    elif depth <= 3:
        return "monthly"
    else:
        return "yearly"


def format_lastmod(filepath):
    """Format file's last modified time as YYYY-MM-DD (UTC aware)."""
    timestamp = os.path.getmtime(filepath)
    last_modified = datetime.fromtimestamp(timestamp, tz=timezone.utc)
    return last_modified.strftime("%Y-%m-%d")


def build_sitemap():
    urls = []

    # Walk through the directory
    for dirpath, dirnames, filenames in os.walk(ROOT_DIR):
        # Remove ignored directories from dirnames so os.walk won't enter them
        dirnames[:] = [
            d for d in dirnames
            if os.path.relpath(os.path.join(dirpath, d), ROOT_DIR).replace(os.sep, "/") not in IGNORE_FOLDERS
        ]
        for filename in filenames:
            _, ext = os.path.splitext(filename.lower())
            if ext in VALID_EXTENSIONS:
                # Get relative path
                filepath = os.path.join(dirpath, filename)
                relpath = os.path.relpath(filepath, ROOT_DIR)

                # Convert local path to URL format
                rel_url = relpath.replace(os.sep, "/")
                if rel_url.endswith("index.php"):
                    rel_url = rel_url.replace("index.php", "")  # clean index pages

                full_url = urljoin(SITE_URL, rel_url)

                # only include if URL is reachable
                if is_url_ok(full_url):
                    # Metadata
                    priority = calculate_priority(rel_url)
                    changefreq = calculate_changefreq(rel_url, filepath)
                    lastmod = format_lastmod(filepath)

                    urls.append((full_url, lastmod, changefreq, priority))

    # Build XML
    sitemap_entries = []
    for url, lastmod, changefreq, priority in sorted(urls):
        sitemap_entries.append(
            f"  <url>\n"
            f"    <loc>{url}</loc>\n"
            f"    <lastmod>{lastmod}</lastmod>\n"
            f"    <changefreq>{changefreq}</changefreq>\n"
            f"    <priority>{priority}</priority>\n"
            f"  </url>"
        )

    sitemap_xml = (
        '<?xml version="1.0" encoding="UTF-8"?>\n'
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'
        + "\n".join(sitemap_entries) +
        "\n</urlset>"
    )

    # Write uncompressed XML
    with open(OUTPUT_FILE, "w", encoding="utf-8") as f:
        f.write(sitemap_xml)

    # Write compressed version
    with gzip.open(GZIP_FILE, "wt", encoding="utf-8") as f:
        f.write(sitemap_xml)

    print(f"Sitemap written to {OUTPUT_FILE} with {len(urls)} pages.")


if __name__ == "__main__":
    build_sitemap()
