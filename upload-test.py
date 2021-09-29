import requests
import os
import time
import json

session = requests.session()
server_url = "http://192.168.1.15"
file_path = "/tmp/tts.wav"
appkey = "Nw==:vzPEmI9uI4hk5awZFeeLQiLx4i1ApRAE7ywKc+rgkHw="
os.system('espeak -s 150 "this is a test." --stdout | sox - -r 8000 /tmp/tts.wav')
with open(file_path, 'rb') as file:
    req = session.post('{0}/upload.php'.format(server_url), data=file)
    if req.status_code == 200:
        # The file uploaded to the server.
        # It still needs things a like title though.
        data = req.json()
        #print(data)
        if data['media_supported'] == False:
            exit(1)
        log_date = time.strftime("%c", time.localtime())
        metadata = {"media": [{"local_id":"2", "artist": "OBPlayer logs", "title": "Station Audio Log ({0} Local)".format(log_date), "album":"Station audio log", "year": time.strftime("%Y", time.localtime()), "country_id":"231","category_id":"10","language_id":"54","genre_id":"999","comments":"","is_copyright_owner":"0","is_approved":"1","status":"private","dynamic_select":"0","file_id": data['file_id'],"file_key": data['file_key'],"advanced_permissions_users":[],"advanced_permissions_groups":[]}]}
        file_data = json.dumps(metadata)
        req = session.post('{0}/api.php'.format(server_url), data={'appkey':appkey, 'c':'media', 'a': 'save', 'd': file_data})
        if req.status_code == 200:
            print("Audio log uploaded at {0}".format(log_date), 'audiolog')
        else:
            print("Got status code {0} from api call for server media save.".format(req.status_code), 'audiolog')
    else:
        print("Got status code {0} from api call for server media upload.".format(req.status_code), 'audiolog')

