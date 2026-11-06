import json, os, re, secrets, shutil, subprocess, threading, uuid
from http.server import ThreadingHTTPServer, BaseHTTPRequestHandler
from pathlib import Path
from urllib.parse import urlparse
ROOT = Path(__file__).resolve().parent
OUT = ROOT / 'downloads'
OUT.mkdir(exist_ok=True)
TOKEN = secrets.token_urlsafe(32)
jobs = {}
lock = threading.Lock()
def ffmpeg():
    if shutil.which('ffmpeg'): return shutil.which('ffmpeg')
    try:
        import imageio_ffmpeg
        return imageio_ffmpeg.get_ffmpeg_exe()
    except Exception: return None
FF = ffmpeg()
try:
    import yt_dlp
    READY = bool(FF)
except ImportError: READY = False
GPU = bool(FF and 'h264_videotoolbox' in subprocess.run([FF, '-encoders'], capture_output=True, text=True).stdout)
def work(jid, url, quality, mode):
    job = jobs[jid]
    def hook(d):
        if d['status'] == 'downloading':
            total = d.get('total_bytes') or d.get('total_bytes_estimate')
            job.update(status='download', message='Video en audio downloaden', percent=round(d.get('downloaded_bytes',0)/total*100) if total else None)
        elif d['status'] == 'finished': job.update(status='processing', message='Beeld en geluid verwerken…', percent=None)
    try:
        folder = OUT / jid
        folder.mkdir()
        opts = {'format': f'bv*[height<={quality}][ext=mp4]+ba[ext=m4a]/b[height<={quality}][ext=mp4]/bv*[height<={quality}]+ba/b[height<={quality}]', 'outtmpl': str(folder/'source.%(ext)s'), 'noplaylist': True, 'ffmpeg_location': FF, 'progress_hooks':[hook], 'quiet':True, 'merge_output_format':'mp4', 'socket_timeout':30}
        with yt_dlp.YoutubeDL(opts) as ydl: info = ydl.extract_info(url, download=True)
        job['title'] = info.get('title','Video')
        source = next(p for p in folder.iterdir() if p.suffix in ('.mp4','.webm','.mkv','.mov'))
        target = folder/'video.mp4'
        if mode == 'auto' and source.suffix == '.mp4': source.rename(target)
        else:
            job.update(status='processing', message='Converteren naar MP4…', percent=None)
            encoder = 'h264_videotoolbox' if mode == 'gpu' else 'libx264'
            args = [FF,'-y','-i',str(source),'-c:v',encoder]
            args += ['-b:v','6M'] if mode == 'gpu' else ['-preset','fast','-crf','22']
            result = subprocess.run(args+['-c:a','aac','-movflags','+faststart',str(target)],capture_output=True,text=True)
            if result.returncode: raise RuntimeError('Conversie mislukt. Probeer CPU in plaats van hardwareversnelling.')
            source.unlink()
        job.update(status='done',message='Je MP4 staat klaar',percent=100,download=f'/download/{jid}')
    except Exception as e: job.update(status='error',message=str(e)[:600],percent=None)
class Handler(BaseHTTPRequestHandler):
    def send(self, data, code=200, kind='application/json'):
        body = json.dumps(data).encode() if kind == 'application/json' else data
        self.send_response(code); self.send_header('Content-Type',kind); self.send_header('Content-Length',str(len(body))); self.send_header('Cache-Control','no-store'); self.end_headers(); self.wfile.write(body)
    def do_GET(self):
        if self.path == '/': return self.send((ROOT/'index.html').read_bytes().replace(b'__TOKEN__',TOKEN.encode()),kind='text/html; charset=utf-8')
        if self.path == '/api/status': return self.send({'ready':READY,'gpu':GPU})
        if self.path.startswith('/api/jobs/'):
            job=jobs.get(self.path.rsplit('/',1)[-1]); return self.send(job or {'message':'Niet gevonden'},200 if job else 404)
        if self.path.startswith('/download/'):
            jid=self.path.rsplit('/',1)[-1]
            if jid not in jobs or jobs[jid]['status'] != 'done': return self.send({},404)
            path=OUT/jid/'video.mp4'
            self.send_response(200); self.send_header('Content-Type','video/mp4'); self.send_header('Content-Disposition','attachment; filename="video.mp4"'); self.send_header('Content-Length',str(path.stat().st_size)); self.end_headers()
            with path.open('rb') as f: shutil.copyfileobj(f,self.wfile)
            return
        self.send({},404)
    def do_POST(self):
        if self.path != '/api/jobs': return self.send({},404)
        if self.headers.get('X-Local-Token') != TOKEN: return self.send({'message':'Ongeldige sessie. Herlaad de pagina.'},403)
        try:
            size=int(self.headers.get('Content-Length','0'))
            if size>4096: raise ValueError('Aanvraag te groot')
            data=json.loads(self.rfile.read(size)); url=data.get('url',''); parsed=urlparse(url)
            if parsed.scheme != 'https' or parsed.hostname not in ('youtube.com','www.youtube.com','m.youtube.com','youtu.be') or parsed.port not in (None,443): raise ValueError('Plak een geldige HTTPS-link van YouTube.')
            quality=int(data.get('quality',1080)); mode=data.get('mode','auto')
            if quality not in (720,1080,2160) or mode not in ('auto','cpu','gpu'): raise ValueError('Ongeldige instelling')
            if not READY: return self.send({'message':'Installeer eerst yt-dlp en imageio-ffmpeg. Zie README.'},503)
            if mode=='gpu' and not GPU: raise ValueError('Hardwareversnelling is niet beschikbaar.')
            with lock:
                if any(j['status'] not in ('done','error') for j in jobs.values()): return self.send({'message':'Er loopt al een download. Wacht tot die klaar is.'},409)
                jid=uuid.uuid4().hex; jobs[jid]={'status':'starting','message':'YouTube-video ophalen…','percent':None}
            threading.Thread(target=work,args=(jid,url,quality,mode),daemon=True).start()
            self.send({'id':jid},202)
        except (ValueError,TypeError) as e: self.send({'message':str(e)},400)
print('Cliplocal: http://127.0.0.1:8765',flush=True)
ThreadingHTTPServer(('127.0.0.1',8765),Handler).serve_forever()
