from http.server import SimpleHTTPRequestHandler, HTTPServer
import os
import sys

HOST = "localhost"
PORT = 8000

class MyHandler(SimpleHTTPRequestHandler):
    # Setzt den Webroot auf aktuelles Verzeichnis
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=os.getcwd(), **kwargs)

    # Optional: explizit index.html setzen (eigentlich schon Standard)
    def do_GET(self):
        if self.path == "/":
            self.path = "/index.html"
        return super().do_GET()


if __name__ == "__main__":
    server = HTTPServer((HOST, PORT), MyHandler)
    print(f"Server läuft auf http://{HOST}:{PORT}")
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("Shutting down...")
        sys.exit(0)