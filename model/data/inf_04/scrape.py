import csv
import os
from urllib.parse import urljoin, urlparse
import requests
from bs4 import BeautifulSoup

pathToExam = {
    "inf_02": "ee08",
    "inf_03": "inf03ee09e14",
    "inf_04": "inf04"
}

BASE_URL = "https://www.praktycznyegzamin.pl/"
BASE_PATH = "/teoria/wszystko/"

def path_to_url():
    url = BASE_URL
    # Get current parent folder name
    url += pathToExam[os.path.basename(os.path.dirname(os.path.abspath(__file__)))]
    url += BASE_PATH 
    
    return url

URL = path_to_url() 

OUT_CSV = "questions.csv"
IMG_DIR = "images"
CSV_DELIMITER = ";"

HEADERS = {
    # "User-Agent": "Mozilla/5.0 (compatible; MTI-scraper/1.0)"
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36"
}

def download_image(url: str, dest_dir: str) -> str:
    if not url or not dest_dir:
        return ""
    try:
        os.makedirs(dest_dir, exist_ok=True)
        name = os.path.basename(urlparse(url).path) or "image.jpg"
        path = os.path.join(dest_dir, name)
        r = requests.get(url, headers=HEADERS, timeout=30)
        r.raise_for_status()
        with open(path, "wb") as f:
            f.write(r.content)
        return path
    except Exception:
        return ""

def clean(t: str) -> str:
    return " ".join((t or "").replace("\xa0", " ").split())

def parse():
    r = requests.get(URL, headers=HEADERS, timeout=60)
    r.raise_for_status()
    soup = BeautifulSoup(r.text, "html.parser")

    questions = []
    for q_idx, qdiv in enumerate(soup.select("div.question"), start=1):
        title_el = qdiv.select_one(".title")
        question_text = clean(title_el.get_text(" ", strip=True)) if title_el else ""

        # Odpowiedzi
        answers = qdiv.select(".answer")
        # Zabezpieczenie: spodziewamy się 4 odpowiedzi
        # (jeśli jest ich więcej – bierzemy pierwsze 4)
        answers = answers[:4]

        # Mapowanie liter A–D -> treść
        options = {"A": "", "B": "", "C": "", "D": ""}
        correct_letter = ""
        correct_text = ""

        for a_el in answers:
            # litera siedzi w <strong>A. </strong> na początku
            strong = a_el.find("strong")
            if strong:
                lit = clean(strong.get_text())
                # typowo "A." / "B." → weź pierwszą literę
                letter = (lit[:1] if lit else "").upper()
            else:
                # awaryjnie spróbuj wydobyć literę z całego tekstu
                raw = clean(a_el.get_text())
                letter = raw[:1].upper() if raw[:2].endswith(".") else ""

            # Pełny tekst odpowiedzi bez wiodącego "A. "
            full = clean(a_el.get_text(" ", strip=True))
            if letter and full.startswith(letter):
                # usuń prefiks "A." / "B." itp.
                # po literze zwykle stoi kropka i spacja
                full = full[2:].strip()

            if letter in options:
                options[letter] = full

            # Wykrycie poprawnej odpowiedzi po klasie "correct"
            if "correct" in " ".join(a_el.get("class") or []):
                correct_letter = letter or correct_letter
                correct_text = full or correct_text

        # Obrazek
        img_el = qdiv.select_one(".image img")
        image_url = urljoin(URL, img_el["src"]) if img_el and img_el.get("src") else ""
        image_local = download_image(image_url, IMG_DIR) if IMG_DIR else ""

        questions.append({
            "id": "",
            "question_id": q_idx,
            "question": question_text,
            "a": options["A"],
            "b": options["B"],
            "c": options["C"],
            "d": options["D"],
            "correct": correct_letter,
            # "correct_text": correct_text,
            "image": image_local,
            "image_fallback": image_url,
            "category_name": os.path.basename(os.path.dirname(os.path.abspath(__file__)))
        })

    return questions

def save_csv(rows):
    fieldnames = ["id","question_id","question","a","b","c","d",
                  "correct","image","image_fallback", "category_name"]
    with open(OUT_CSV, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=fieldnames, delimiter=CSV_DELIMITER)
        writer.writeheader()
        for row in rows:
            writer.writerow(row)

if __name__ == "__main__":
    data = parse()
    save_csv(data)
    print(f"Zapisano {len(data)} pytań do {OUT_CSV}.")
    if IMG_DIR:
        print(f"Obrazy zapisano w katalogu: {IMG_DIR}")
