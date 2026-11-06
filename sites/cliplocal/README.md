# Cliplocal
Lokale YouTube-naar-MP4 webapp. Python 3.9+ vereist.

Start vanuit deze map:

```sh
python3 -m venv .venv
.venv/bin/python -m pip install yt-dlp imageio-ffmpeg
.venv/bin/python server.py
```

Open http://127.0.0.1:8765. Bestanden komen in `downloads/`.
De app accepteert één taak tegelijk. Automatisch kiest MP4-bronnen en voegt die waar mogelijk zonder hercodering samen. CPU forceert H.264; hardware gebruikt Apple VideoToolbox indien beschikbaar. De browser toont downloadvoortgang; conversie toont een onbepaalde voortgangsbalk. YouTube kan aanvullende authenticatie of een ondersteunde JavaScript-runtime vereisen. Houd yt-dlp actueel. Gebruik video's waarvoor je downloadrechten hebt.
