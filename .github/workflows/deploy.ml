name: Deploy to InfinityFree FTP

on:
  push:
    branches:
      - main  # Ganti ke 'master' jika branch utama repository kamu bernama master

jobs:
  web-deploy:
    name: 🎉 Deploying site
    runs-on: ubuntu-latest
    steps:
    - name: 🚚 Get latest code
      uses: actions/checkout@v4

    - name: 📂 Sync files to FTP
      uses: SamKirkland/FTP-Deploy-Action@v4.3.5
      with:
        server: ${{ secrets.FTP_SERVER }}
        username: ${{ secrets.FTP_USERNAME }}
        password: ${{ secrets.FTP_PASSWORD }}
        server-dir: ${{ secrets.FTP_SERVER_DIR }}