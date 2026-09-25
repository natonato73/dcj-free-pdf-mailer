#!/usr/bin/env python3
"""Build and verify the complete DCJ Free PDF Mailer release ZIP."""

from __future__ import annotations

import argparse
import hashlib
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

REQUIRED = [
    Path("dcj-free-pdf-mailer.php"),
    Path("includes/class-dcj-fpm-admin-notices.php"),
    Path("includes/class-dcj-fpm-csv-exporter.php"),
    Path("includes/class-dcj-fpm-recaptcha.php"),
    Path("includes/class-dcj-fpm-subscriber-helper.php"),
    Path("includes/class-dcj-fpm-unsubscribe.php"),
    Path("includes/index.php"),
]

PACKAGE_ROOT = "dcj-free-pdf-mailer"


def sha256(path: Path) -> str:
    h = hashlib.sha256()
    with path.open("rb") as f:
        for chunk in iter(lambda: f.read(1024 * 1024), b""):
            h.update(chunk)
    return h.hexdigest()


def plugin_version() -> str:
    text = (ROOT / "dcj-free-pdf-mailer.php").read_text(encoding="utf-8")
    header = re.search(r"^\s*\*\s*Version:\s*([0-9.]+)", text, re.M)
    const = re.search(r"const\s+VERSION\s*=\s*'([0-9.]+)'", text)
    if not header or not const:
        raise RuntimeError("Version metadata is missing.")
    if header.group(1) != const.group(1):
        raise RuntimeError(
            f"Version mismatch: header={header.group(1)} const={const.group(1)}"
        )
    return header.group(1)


def package_files() -> list[Path]:
    missing = [str(p) for p in REQUIRED if not (ROOT / p).is_file()]
    if missing:
        raise RuntimeError("Required plugin files are missing: " + ", ".join(missing))

    files = list(REQUIRED)
    for name in ("README.md", "README-ja.md"):
        p = Path(name)
        if (ROOT / p).is_file():
            files.append(p)
    docs = ROOT / "docs"
    if docs.is_dir():
        files.extend(
            p.relative_to(ROOT)
            for p in sorted(docs.rglob("*"))
            if p.is_file()
        )
    return sorted(set(files), key=lambda p: str(p))


def build(output: Path) -> Path:
    version = plugin_version()
    files = package_files()
    output.parent.mkdir(parents=True, exist_ok=True)
    if output.exists():
        output.unlink()

    with zipfile.ZipFile(output, "w", compression=zipfile.ZIP_DEFLATED) as zf:
        for rel in files:
            zf.write(ROOT / rel, f"{PACKAGE_ROOT}/{rel.as_posix()}")

    with zipfile.ZipFile(output) as zf:
        names = set(zf.namelist())
    missing = [
        f"{PACKAGE_ROOT}/{rel.as_posix()}"
        for rel in REQUIRED
        if f"{PACKAGE_ROOT}/{rel.as_posix()}" not in names
    ]
    if missing:
        raise RuntimeError("Built ZIP is incomplete: " + ", ".join(missing))

    print(f"version={version}")
    print(f"zip={output}")
    print(f"files={len(names)}")
    print(f"sha256={sha256(output)}")
    return output


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--output", type=Path)
    args = parser.parse_args()
    version = plugin_version()
    output = args.output or ROOT / "output" / f"dcj-free-pdf-mailer-v{version}.zip"
    build(output.resolve())
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
