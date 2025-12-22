from ftplib import FTP_TLS

ftp = FTP_TLS()
ftp.connect("lv-shared02.dapanel.net", 21)
ftp.auth()  # activează TLS
ftp.prot_p()  # protejează canalul de date
ftp.login("hospitalsync@cvilnoiu.daw.ssmr.ro", "parola_ta")

print("📂 Root directory listing:")
ftp.retrlines("LIST")

ftp.quit()
