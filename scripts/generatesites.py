import os
import shutil
from pathlib import Path

def load_template(template_folder: str, name: str = "main.html") -> str:
    template_path = Path(template_folder) / name
    with open(template_path, "r", encoding="utf-8") as f:
        return f.read()

def clear_output_folder(output_folder: str) -> None:
    if os.path.exists(output_folder):
        shutil.rmtree(output_folder)  # Löscht den Ordner rekursiv
    os.makedirs(output_folder)

def process_site_file(filepath: Path, template: str, output_folder: str) -> tuple[bool, str]:
    with open(filepath, "r", encoding="utf-8") as f:
        first_line = f.readline().strip()
        if not first_line:
            return False, f"Leere Pfadangabe in {filepath.name}"
        content = f.read()
    relative_path = first_line.lstrip("/")
    output_path = Path(output_folder) / relative_path
    if not output_path.resolve().is_relative_to(Path(output_folder).resolve()):
        return False, f"Unsicherer Pfad '{first_line}' in {filepath.name} – übersprungen."
    if output_path.exists():
        return False, f"Pfad '{first_line}' existiert bereits – {filepath.name} übersprungen."
    output_path.parent.mkdir(parents=True, exist_ok=True)
    output_path.write_text(template.replace("{{content}}", content), encoding="utf-8")
    return True, ""

def main():
    template_folder = "../templates"
    output_folder  = "../temp"
    sites_folder   = "../sites"
    try:
        template = load_template(template_folder)
    except FileNotFoundError:
        print("Fehler: Template-Datei 'main.html' nicht gefunden.")
        return
    clear_output_folder(output_folder)
    site_files = list(Path(sites_folder).glob("*.site"))
    total = len(site_files)
    if total == 0:
        print("Keine .site-Dateien gefunden.")
        return
    success_count = 0
    errors = []
    for i, filepath in enumerate(site_files, start=1):
        print(f"[{i}/{total}] Verarbeite {filepath.name}...", end="\r", flush=True)

        ok, error_msg = process_site_file(filepath, template, output_folder)
        if ok:
            success_count += 1
        else:
            errors.append(error_msg) #type: ignore
    print(f"\n✓ {success_count}/{total} Seiten erfolgreich generiert in '{output_folder}'.")
    if errors:
        print(f"\n{len(errors)} Fehler aufgetreten:") #type: ignore
        for err in errors: #type: ignore
            print(f"  • {err}")

if __name__ == "__main__":
    main()