sudo cp ./loki-config.yaml /usr/local/bin/loki-config.yaml
sudo cp ./loki.service /etc/systemd/system/loki.service
sudo useradd --system loki
sudo mkdir /loki
sudo chmod 700 /loki
sudo chown loki:loki /loki
cd /usr/local/bin
sudo curl -fSL -o loki.zip "https://github.com/grafana/loki/releases/download/v2.4.2/loki-linux-amd64.zip"
sudo unzip loki.zip
sudo mv loki-linux-amd64 loki
sudo chmod a+x loki
sudo rm loki.zip
sudo systemctl daemon-reload
sudo service loki start
sudo service loki status
# start on boot:
# sudo systemctl enable loki.service