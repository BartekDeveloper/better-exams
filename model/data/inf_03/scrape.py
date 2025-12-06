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
OUT_CATEGORIES_CSV = "categories.csv"
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
        if os.path.exists(path):
            return path
        r = requests.get(url, headers=HEADERS, timeout=30)
        r.raise_for_status()
        with open(path, "wb") as f:
            f.write(r.content)
        return path
    except Exception:
        return ""

def clean(t: str) -> str:
    return " ".join((t or "").replace("\xa0", " ").split())

def parse(allowed_cats, cat_map):
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

        
        # Determine qualification from folder name
        folder_name = os.path.basename(os.path.dirname(os.path.abspath(__file__)))
        qual_map = {
            "inf_02": "INF.02",
            "inf_03": "INF.03",
            "inf_04": "INF.04"
        }
        qualification = qual_map.get(folder_name, "INF.03") # Default to INF.03 if unknown

        # Classify question
        global_cat_id = classify_question(question_text, allowed_cats)
        category_id = cat_map.get(global_cat_id, cat_map.get(11)) # Fallback to Other

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
            "qualification": qualification,
            "category_id": category_id
        })

    return questions




CATEGORIES = {
    1: "HTML",
    2: "CSS",
    3: "JavaScript",
    4: "PHP",
    5: "SQL/Bazy Danych",
    6: "Sprzęt",
    7: "Sieci Komputerowe",
    8: "Systemy Operacyjne",
    9: "Grafika/Multimedia",
    10: "Algorytmy/Programowanie",
    11: "Inne"
}

KEYWORDS = {
    1: ["html", "znacznik", "atrybut", "strona", "witryna", "href", "src", "div", "span", "table", "form", "input"],
    2: ["css", "style", "kolor", "tło", "font", "border", "margin", "padding", "selektor", "klasa", "id"],
    3: ["javascript", "js", "skrypt", "zmienna", "funkcja", "alert", "document", "window", "event", "dom"],
    4: ["php", "echo", "$_", "mysqli", "pdo", "session", "cookie", "include", "require"],
    5: ["sql", "select", "insert", "update", "delete", "baza", "tabela", "kwerenda", "klucz", "relacja", "join"],
    6: ["procesor", "ram", "płyta", "dysk", "zasilacz", "drukarka", "toner", "napięcie", "bios", "uefi", "karta"],
    7: ["ip", "adres", "maska", "sieć", "lan", "wan", "router", "switch", "protokół", "tcp", "udp", "dhcp", "dns"],
    8: ["linux", "windows", "system", "polecenie", "cmd", "terminal", "uprawnienia", "użytkownik", "plik", "katalog"],
    9: ["grafika", "kompresja", "rgb", "cmyk", "obraz", "dźwięk", "piksel", "rozdzielczość", "format"],
    10: ["algorytm", "pętla", "zmienna", "klasa", "obiekt", "metoda", "dziedziczenie", "polimorfizm"]
}

QUAL_BASE_ID = {
    "inf_02": 100,
    "inf_03": 200,
    "inf_04": 300
}


QUALIFICATION_CONFIG = {
    "inf_02": [6, 7, 8, 11], # Hardware, Networking, OS, Other
    "inf_03": [1, 2, 3, 4, 5, 9, 11], # HTML, CSS, JS, PHP, SQL, Graphics, Other
    "inf_04": [3, 4, 5, 10, 11] # JS, PHP, SQL, Algorithms, Other
}

def get_folder_name():
    return os.path.basename(os.path.dirname(os.path.abspath(__file__)))

def classify_question(text, allowed_cats):
    text = text.lower()
    scores = {cat_id: 0 for cat_id in allowed_cats}
    
    for cat_id in allowed_cats:
        if cat_id in KEYWORDS:
            for keyword in KEYWORDS[cat_id]:
                if keyword in text:
                    scores[cat_id] += 1
    
    # Find category with max score
    best_cat = 11 # Default to Other
    if 11 not in allowed_cats:
         best_cat = allowed_cats[-1] # Fallback to last allowed

    max_score = 0
    
    for cat_id, score in scores.items():
        if score > max_score:
            max_score = score
            best_cat = cat_id
            
    return best_cat

def get_qualification():
    folder_name = os.path.basename(os.path.dirname(os.path.abspath(__file__)))
    qual_map = {
        "inf_02": "INF.02",
        "inf_03": "INF.03",
        "inf_04": "INF.04"
    }
    return qual_map.get(folder_name, "INF.03")

def get_base_id():
    folder_name = os.path.basename(os.path.dirname(os.path.abspath(__file__)))
    return QUAL_BASE_ID.get(folder_name, 200)

def save_csv(rows, cat_map):
    fieldnames = ["id","question_id","question","a","b","c","d",
                  "correct","image","image_fallback", "category_id"]
    with open(OUT_CSV, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=fieldnames, delimiter=CSV_DELIMITER, lineterminator='\n')
        writer.writeheader()
        for row in rows:
            if "qualification" in row:
                del row["qualification"]
            
            # Remap category ID
            old_id = row["category_id"]
            # The row["category_id"] currently holds the GLOBAL ID (e.g. 6) + BASE (e.g. 100) from previous logic?
            # No, in previous step we did base_id + local_cat_id.
            # But here we need to remap based on the NEW logic.
            # Let's adjust parse() to return the RAW global ID (1-11), and map it here.
            
            # Wait, parse() calls classify_question. Let's fix parse() first.
            pass 
            writer.writerow(row)


CATEGORY_IMAGES = {
    1: "html.jpg",
    2: "css.jpg",
    3: "js.jpg",
    4: "php.jpg",
    5: "sql.jpg",
    6: "hardware.jpg",
    7: "network.jpg",
    8: "os.jpg",
    9: "multimedia.jpg",
    10: "algo.jpg",
    11: "other.jpg"
}

def save_categories():
    qualification = get_qualification()
    base_id = get_base_id()
    folder = get_folder_name()
    allowed = QUALIFICATION_CONFIG.get(folder, [11])
    
    # Map Global ID -> New Local ID (Base + 1..N)
    cat_map = {}
    
    fieldnames = ["id", "name", "qualification", "image"]
    with open(OUT_CATEGORIES_CSV, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=fieldnames, delimiter=CSV_DELIMITER, lineterminator='\n')
        writer.writeheader()
        
        for idx, global_id in enumerate(allowed, start=1):
            new_id = base_id + idx
            cat_map[global_id] = new_id
            writer.writerow({
                "id": new_id,
                "name": CATEGORIES[global_id],
                "qualification": qualification,
                "image": CATEGORY_IMAGES.get(global_id, "default.jpg")
            })
            
    return cat_map

if __name__ == "__main__":
    # 1. Save Categories and generate the map (Global ID -> New ID)
    cat_map = save_categories()
    
    # 2. Parse and Classify using the map
    # We need to pass the allowed list and map to parse, or handle it inside.
    # Let's redefine parse to take arguments or use globals.
    folder = get_folder_name()
    allowed = QUALIFICATION_CONFIG.get(folder, [11])
    
    data = parse(allowed, cat_map)
    save_csv(data, cat_map)
    
    print(f"Zapisano {len(data)} pytań do {OUT_CSV}.")
    print(f"Zapisano kategorię do {OUT_CATEGORIES_CSV}.")
    if IMG_DIR:
        print(f"Obrazy zapisano w katalogu: {IMG_DIR}")
