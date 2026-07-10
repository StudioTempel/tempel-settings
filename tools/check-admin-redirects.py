#!/usr/bin/env python3
import csv
import re
import subprocess
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import datetime
from pathlib import Path
from urllib.parse import urlparse


SITES = [
    "kooistrafeenstra.nl",
    "taplite.com",
    "bregbreg.nl",
    "vanlithbouw.nl",
    "me-2.nl",
    "dycotrade.com",
    "stoommachinemuseum.nl",
    "marksmen.nl",
    "horecabier.nl",
    "horecabier.be",
    "knipsalonhoutenbos.nl",
    "mkbkredietnederland.nl",
    "frisia.nl",
    "bouwbrug.nl",
    "vlaartechniek.nl",
    "overinzicht.nl",
    "vdheuveltrade.nl",
    "kmsadvisering.nl",
    "kmsadvisreringen.nl",
    "bit4u.nl",
    "ondernemersfondsmedemblik.nl",
    "taxlex.nl",
    "deraadreinders.nl",
    "deslimmechef.nl",
    "jasperfijma.nl",
    "limo4u.nl",
    "wlkm.nl",
    "nzvschilders.nl",
    "kbgmontage.nl",
    "mkba.info",
    "karaat.nu",
    "vanduintotdijk.nl",
    "pgenkhuizen.nl",
    "sinusjevi.nl",
    "vanteeffelenexecutive.nl",
    "brynx.nl",
    "boltcab.io",
    "rosabian.nl",
    "yvonnekuipers.nl",
    "bijoost.nl",
    "psmedemblik.nl",
    "jerbohaarden.nl",
    "horizonflowerfamily.nl",
    "chatlicense.com",
    "ragbag.eu",
    "studiotempel.nl",
    "samcity.nl",
    "leblancacademy.nl",
    "pknhoornzwaagblokker.nl",
    "epiphanyhomes.pt",
    "medemblikkerbedrijvengroep.nl",
    "significant.nl",
    "axplain.nl",
    "ofmedemblik.nl",
    "dianatromp.nl",
    "buysmankruiden.nl",
    "trend-center.nl",
    "funfestijn.nl",
    "quicksilverbar.nl",
    "maartendegraaf.nl",
    "thefamilyhaarverzorging.nl",
    "micheldoorn.nl",
    "bierfabriek.com",
    "taxiid.nl",
    "hoogtij.amsterdam",
    "bvscout.nl",
    "wardenaar.com",
    "woodstock-vloeren.nl",
    "forexfordynamics.com",
    "taxiwebbooker.com",
    "hoffdakentimmerwerken.nl",
    "hetwapenvanmedemblik.nl",
    "becam.nl",
    "postkrediet.nl",
    "nr7groep.nl",
    "nr7finance.nl",
    "moneycare.nl",
    "salonannemieke.nl",
    "raat.nl",
    "finelines.nl",
    "tcwf.nl",
    "senshake.nl",
    "cocorico.nl",
    "liefamsterdam.nl",
    "koggevaarder.nl",
    "dataspark.nl",
    "treviancollectief.nl",
    "hmg.studiotempel.nl",
    "zwaanvakschilders.nl",
    "troostonline.nl",
    "aica-summit.com",
    "nct.studiotempel.nl",
    "woet.eu",
]


def run_curl(url):
    marker = "\n__CURL_META__ %{http_code} %{url_effective} %{num_redirects} %{errormsg}\n"
    cmd = [
        "curl",
        "-k",
        "-sS",
        "-L",
        "-D",
        "-",
        "-o",
        "/dev/null",
        "--max-redirs",
        "10",
        "--connect-timeout",
        "10",
        "--max-time",
        "35",
        "-A",
        "Mozilla/5.0 admin-redirect-check",
        "-w",
        marker,
        url,
    ]
    proc = subprocess.run(cmd, capture_output=True, text=True, timeout=45)
    output = proc.stdout + proc.stderr
    meta_line = ""
    for line in output.splitlines():
        if line.startswith("__CURL_META__"):
            meta_line = line
            break
    parts = meta_line.split(" ", 4)
    http_code = parts[1] if len(parts) > 1 else "000"
    final_url = parts[2] if len(parts) > 2 else ""
    redirects = parts[3] if len(parts) > 3 else ""
    err = parts[4] if len(parts) > 4 else ""
    statuses = re.findall(r"^HTTP/\S+\s+(\d+)", output, re.MULTILINE)
    locations = re.findall(r"(?im)^location:\s*(.+?)\r?$", output)
    return {
        "exit_code": proc.returncode,
        "http_code": http_code,
        "final_url": final_url,
        "redirects": redirects,
        "error": err.strip(),
        "statuses": " > ".join(statuses),
        "locations": " | ".join(locations),
    }


def classify(path, result):
    final_path = urlparse(result["final_url"]).path.lower()
    locations = result["locations"].lower()
    error = result["error"].lower()
    code = result["http_code"]
    exit_code = result["exit_code"]

    if exit_code != 0 or code == "000":
        if "maximum" in error or exit_code == 47:
            return "LOOP"
        return "ERROR"

    if path == "/st-beheer/":
        if "wp-admin" in final_path or "wp-admin" in locations:
            return "BAD_WP_ADMIN"
        if code in {"200", "401", "403"} and "st-beheer" in final_path:
            return "OK"
        if code in {"301", "302", "307", "308"}:
            return "REDIRECT_UNFINISHED"
        return "CHECK"

    if "maximum" in error or exit_code == 47:
        return "LOOP"
    if "wp-admin" in final_path and code == "403":
        return "OK_BLOCKED"
    if "wp-admin" in final_path and code not in {"404", "410"}:
        return "WP_ADMIN_REACHABLE"
    if "wp-admin" in locations and "st-beheer" not in locations:
        return "WP_ADMIN_REDIRECT"
    return "OK"


def main():
    checks = [(site, path, f"http://{site}{path}") for site in SITES for path in ("/st-beheer/", "/wp-admin/")]
    rows = []
    with ThreadPoolExecutor(max_workers=16) as executor:
        future_map = {executor.submit(run_curl, url): (site, path) for site, path, url in checks}
        for future in as_completed(future_map):
            site, path = future_map[future]
            try:
                result = future.result()
            except Exception as exc:
                result = {
                    "exit_code": 124,
                    "http_code": "000",
                    "final_url": f"http://{site}{path}",
                    "redirects": "",
                    "error": str(exc),
                    "statuses": "",
                    "locations": "",
                }
            row = {
                "site": site,
                "path": path,
                "status": classify(path, result),
                **result,
            }
            rows.append(row)
            print(f"{site}{path} {row['status']} {result['http_code']} -> {result['final_url']}", flush=True)

    rows.sort(key=lambda row: (row["site"], row["path"]))

    stamp = datetime.now().strftime("%Y-%m-%d-%H%M")
    out_dir = Path("reports")
    csv_path = out_dir / f"admin-redirect-check-{stamp}.csv"
    md_path = out_dir / f"admin-redirect-check-{stamp}.md"

    with csv_path.open("w", newline="") as f:
        writer = csv.DictWriter(f, fieldnames=rows[0].keys())
        writer.writeheader()
        writer.writerows(rows)

    grouped = {}
    for row in rows:
        grouped.setdefault(row["status"], []).append(row)

    with md_path.open("w") as f:
        f.write(f"# Admin redirect check ({stamp})\n\n")
        f.write("Checked `/st-beheer/` and `/wp-admin/` via HTTP with curl following up to 10 redirects.\n\n")
        for status in sorted(grouped):
            f.write(f"## {status} ({len(grouped[status])})\n\n")
            for row in grouped[status]:
                f.write(
                    f"- `{row['site']}{row['path']}`: HTTP `{row['http_code']}`, "
                    f"redirects `{row['redirects']}`, final `{row['final_url']}`"
                )
                if row["error"]:
                    f.write(f", error `{row['error']}`")
                f.write("\n")
            f.write("\n")

    print(f"\nWrote {csv_path} and {md_path}")


if __name__ == "__main__":
    main()
