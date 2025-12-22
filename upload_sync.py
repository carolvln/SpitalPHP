import os
import json
from ftplib import FTP

# Load config
with open('config.json', 'r') as f:
    config = json.load(f)

ftp_host = config['ftp']['host']
ftp_port = config['ftp'].get('port', 21)
ftp_user = config['ftp']['username']
ftp_pass = config['ftp']['password']
remote_path = config['ftp']['remotePath']
local_folder = config['local']['folder']

# Connect to FTP
ftp = FTP()
ftp.connect(ftp_host, ftp_port)
ftp.login(ftp_user, ftp_pass)
print(f"✅ Connected to FTP: {ftp_host} as {ftp_user}")

# Ensure remote path exists
try:
    ftp.cwd(remote_path)
except:
    print(f"❌ Remote path not found: {remote_path}")
    ftp.quit()
    exit(1)

# Function to upload files recursively
def upload_dir(local_dir, remote_dir):
    for root, dirs, files in os.walk(local_dir):
        rel_path = os.path.relpath(root, local_dir).replace("\\", "/")
        target_dir = remote_dir if rel_path == '.' else f"{remote_dir}/{rel_path}"

        # Create remote directories if missing
        try:
            ftp.cwd(target_dir)
        except:
            path_parts = target_dir.split('/')
            current_path = ""
            for part in path_parts:
                if not part:
                    continue
                current_path += f"/{part}"
                try:
                    ftp.mkd(current_path)
                except:
                    pass
            ftp.cwd(target_dir)

        # Upload each file
        for filename in files:
            local_file = os.path.join(root, filename)
            with open(local_file, 'rb') as f:
                ftp.storbinary(f'STOR {filename}', f)
            print(f"↑ Uploaded: {filename} → {target_dir}")

        # Go back to root remote dir after each iteration
        ftp.cwd(remote_dir)

# Upload all files
upload_dir(local_folder, remote_path)

ftp.quit()
print("✅ Upload complete! All files are now live in public_html.")
