# positions_file=/var/log/positions.yaml
sudo useradd --system promtail
sudo usermod -a -G adm promtail
sudo usermod --shell /bin/bash promtail

# if [ ! -e "$positions_file" ] ; then
#     sudo touch "$positions_file"
#     sudo chown promtail:promtail "$positions_file"
#     sudo chmod 600 "$positions_file"
# fi
sudo mkdir /var/log/promtail
sudo chmod -R 700 /var/log/promtail
sudo chown -R promtail:promtail /var/log/promtail
sudo cp ./promtail-config.yaml /usr/local/bin/promtail-config.yaml
sudo cp ./promtail.service /etc/systemd/system/promtail.service
cd /usr/local/bin
sudo curl -fSL -o promtail.zip "https://github.com/grafana/loki/releases/download/v2.4.2/promtail-linux-amd64.zip"
sudo unzip promtail.zip
sudo mv promtail-linux-amd64 promtail
sudo chmod a+x promtail
sudo rm promtail.zip
sudo systemctl daemon-reload
sudo chmod 664 /data/www/tc-*/current/storage/logs/loki.log
sudo service promtail start
sudo service promtail status
sudo systemctl enable promtail