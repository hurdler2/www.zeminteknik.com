# Zemin Teknik Web Sitesi - AWS EC2 Deployment

Bu proje, Zemin Teknik web sitesinin AWS EC2'de Docker ile yayınlanması için gerekli tüm konfigürasyonları içerir.

## 🚀 Özellikler

- ✅ **SSL Sertifikası**: Let's Encrypt ile otomatik SSL
- 📧 **Mail Sunucusu**: Postfix + Dovecot + SpamAssassin
- 🔄 **Otomatik Deploy**: GitHub Actions ile CI/CD
- 🐳 **Docker Containerization**: Nginx tabanlı
- 🔒 **Güvenlik**: Firewall, SSL, HSTS
- 📊 **Monitoring**: Sistem durumu takibi
- 💾 **Backup**: Otomatik yedekleme
- 🌐 **Domain**: www.zeminteknik.com

## 📋 Gereksinimler

- AWS EC2 Instance (Ubuntu 20.04+)
- Domain: zeminteknik.com
- GitHub Repository
- AWS IAM User (ECR erişimi için)

## 🛠️ Kurulum Adımları

### 1. AWS EC2 Instance Oluşturma

```bash
# EC2 Instance özellikleri:
# - Instance Type: t3.medium (2 vCPU, 4 GB RAM)
# - Storage: 20 GB SSD
# - Security Group: HTTP(80), HTTPS(443), SSH(22), Mail ports
# - Key Pair: zeminteknik-key.pem
```

### 2. Sunucu Kurulumu

EC2 instance'ına SSH ile bağlanın ve kurulum scriptini çalıştırın:

```bash
# Sunucuya bağlan
ssh -i zeminteknik-key.pem ubuntu@your-ec2-ip

# Kurulum scriptini çalıştır
wget https://raw.githubusercontent.com/your-username/zeminteknik/main/scripts/setup-server.sh
chmod +x setup-server.sh
./setup-server.sh
```

### 3. Mail Sunucusu Kurulumu (Opsiyonel)

```bash
# Mail sunucusu kurulumu
wget https://raw.githubusercontent.com/your-username/zeminteknik/main/scripts/setup-mail.sh
chmod +x setup-mail.sh
./setup-mail.sh
```

### 4. Domain DNS Ayarları

Domain sağlayıcınızda aşağıdaki DNS kayıtlarını ekleyin:

```
A     @           your-ec2-ip
A     www         your-ec2-ip
A     mail        your-ec2-ip
MX    @           mail.zeminteknik.com
TXT   @           v=spf1 mx a ip4:your-ec2-ip ~all
```

### 5. SSL Sertifikası Alma

```bash
# SSL sertifikası al
cd /opt/zeminteknik
./ssl-setup.sh
```

### 6. GitHub Repository Ayarları

GitHub repository'nizde aşağıdaki secrets'ları ekleyin:

```
AWS_ACCESS_KEY_ID=your-aws-access-key
AWS_SECRET_ACCESS_KEY=your-aws-secret-key
EC2_HOST=your-ec2-ip
EC2_USERNAME=ubuntu
EC2_SSH_KEY=your-ssh-private-key
```

## 📁 Proje Yapısı

```
zeminteknik/
├── .github/
│   └── workflows/
│       └── deploy.yml          # GitHub Actions CI/CD
├── scripts/
│   ├── setup-server.sh         # Sunucu kurulum scripti
│   └── setup-mail.sh          # Mail sunucusu kurulum scripti
├── css/
│   └── style.css              # Web sitesi CSS
├── images/                    # Web sitesi görselleri
├── Dockerfile                 # Docker image tanımı
├── docker-compose.yml         # Docker Compose konfigürasyonu
├── nginx.conf                 # Nginx konfigürasyonu
├── index.html                 # Ana sayfa
└── README.md                  # Bu dosya
```

## 🔧 Docker Komutları

```bash
# Container'ı build et
docker build -t zeminteknik-web .

# Container'ı çalıştır
docker run -d -p 80:80 -p 443:443 --name zeminteknik-web zeminteknik-web

# Docker Compose ile çalıştır
docker-compose up -d

# Logları görüntüle
docker-compose logs -f

# Container'ı durdur
docker-compose down
```

## 📊 Monitoring

```bash
# Sistem durumu
/opt/zeminteknik/monitor.sh

# Docker container durumu
docker ps

# Nginx logları
docker-compose logs web

# Sistem kaynakları
htop
```

## 💾 Backup

```bash
# Manuel backup
/opt/zeminteknik/backup.sh

# Backup dosyalarını listele
ls -la /opt/backups/
```

## 🔐 SSL Sertifikası Yenileme

SSL sertifikası otomatik olarak her 90 günde bir yenilenir. Manuel yenileme için:

```bash
cd /opt/zeminteknik
docker-compose run --rm certbot renew
docker-compose restart web
```

## 📧 Mail Sunucusu Ayarları

Mail sunucusu kurulduktan sonra:

### Mail Kullanıcısı Oluşturma

```bash
# Mail kullanıcısı ekle
sudo useradd -m -s /bin/bash kullanici@zeminteknik.com
sudo passwd kullanici@zeminteknik.com
```

### Mail İstemci Ayarları

```
SMTP Ayarları:
- Sunucu: mail.zeminteknik.com
- Port: 587 (STARTTLS) veya 465 (SSL)
- Kullanıcı adı: kullanici@zeminteknik.com
- Şifre: belirlediğiniz şifre

IMAP Ayarları:
- Sunucu: mail.zeminteknik.com
- Port: 143 (STARTTLS) veya 993 (SSL)
- Kullanıcı adı: kullanici@zeminteknik.com
- Şifre: belirlediğiniz şifre
```

## 🚀 Deployment

### Otomatik Deploy (GitHub Actions)

1. Kodu GitHub'a push edin
2. GitHub Actions otomatik olarak çalışacak
3. Docker image build edilip ECR'ye push edilecek
4. EC2'de container güncellenecek

### Manuel Deploy

```bash
# EC2'de manuel deploy
cd /opt/zeminteknik
git pull origin main
docker-compose down
docker-compose up -d --build
```

## 🔧 Troubleshooting

### SSL Sertifikası Sorunları

```bash
# SSL sertifikası durumunu kontrol et
ls -la /opt/zeminteknik/certbot/conf/live/zeminteknik.com/

# Certbot loglarını kontrol et
docker-compose logs certbot
```

### Mail Sunucusu Sorunları

```bash
# Postfix durumu
sudo systemctl status postfix

# Dovecot durumu
sudo systemctl status dovecot

# Mail logları
sudo tail -f /var/log/mail.log
```

### Nginx Sorunları

```bash
# Nginx konfigürasyon testi
docker exec zeminteknik-web nginx -t

# Nginx logları
docker-compose logs web
```

## 📞 Destek

Herhangi bir sorun yaşarsanız:

1. Logları kontrol edin: `docker-compose logs`
2. Sistem durumunu kontrol edin: `/opt/zeminteknik/monitor.sh`
3. SSL sertifikası durumunu kontrol edin
4. Firewall ayarlarını kontrol edin: `sudo ufw status`

## 📝 Lisans

Bu proje Zemin Teknik için özel olarak hazırlanmıştır.

## 🔄 Güncellemeler

- **v1.0.0**: İlk sürüm - Temel web sitesi ve SSL
- **v1.1.0**: Mail sunucusu eklendi
- **v1.2.0**: Monitoring ve backup özellikleri eklendi 