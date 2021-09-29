from logging import exception
from flask import Flask, request
from tts_backend import *

app = Flask(__name__)

@app.route("/tts", methods=['POST'])
def tts_http():
    try:
        args = {'speed': request.form['speed'], 'aws_access_key_id': request.form['aws_access_key_id'],
        'aws_secret_access_key': request.form['aws_secret_access_key'], 'voice': request.form['voice'],
        'music_bed_track': request.form['music_bed_track'], 'music_bed_track_volume': request.form['music_bed_track_volume'],
        'text': request.form['text']
        }
    except Exception:
        return json.dumps({'status': False, 'msg': '', 'data': None})
    if args_veify(args):
        tts = TTS(speed=args.speed, aws_access_key_id=args.aws_access_key_id, aws_secret_access_key=args.aws_secret_access_key, voice=args.voice)
        if args.music_bed_track != "":
            output = tts.gen(args.text, args.music_bed_track, args.music_bed_track_volume)
        else:
            output = tts.gen(args.text, None, args.music_bed_track_volume)

        return output
    return 'test'

app.run('127.0.0.1', port=8081)