# Portal A12 — Ambiente Local

WordPress containerizado para desenvolvimento local.  
Parte da arquitetura: **Local → DEV → STAGE → PROD (AWS ECS Fargate)**.

---

## Pré-requisitos

**macOS / Linux:**
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) ≥ 4.x
- Docker Compose v2 (incluído no Docker Desktop)

**Windows (Podman):**
- [Podman Desktop](https://podman-desktop.io/) ≥ 1.x
- Ver seção [Setup com Podman no Windows](#setup-com-podman-no-windows) abaixo

---

## Primeiro uso (macOS / Linux)

### 1. Configurar variáveis de ambiente

```bash
cp .env.example .env
```

Edite `.env` e defina:
- senhas do banco (`DB_PASSWORD`, `DB_ROOT_PASSWORD`)
- chaves do WordPress — gere em https://api.wordpress.org/secret-key/1.1/salt/

### 2. Subir os containers

```bash
docker compose up -d
```

Aguarde ~30 segundos para o MySQL inicializar.

### 3. Instalar o WordPress

```bash
./scripts/setup-local.sh
```

O script instala o WordPress, configura permalink e remove o conteúdo padrão.

### 4. Acessar

| URL | O quê |
|-----|-------|
| http://localhost:8080 | Front-end do portal |
| http://localhost:8080/wp-admin | Painel WordPress |

---

## Uso diário

```bash
# Subir
docker compose up -d

# Parar
docker compose down

# Ver logs
docker compose logs -f wordpress
docker compose logs -f mysql

# WP-CLI
./scripts/wp plugin list
./scripts/wp user list
./scripts/wp cache flush
```

### phpMyAdmin (opcional)

```bash
docker compose --profile tools up -d
# Acesse: http://localhost:8081
```

### Espelho local do portal

Se você quiser que outro dev tenha um espelho do ambiente atual, não basta o Git: é preciso compartilhar o estado do banco e dos uploads.

Exportar o espelho na máquina de origem:

```bash
./scripts/export-mirror.sh
```

Isso gera dois arquivos em `db/dumps/`:
- dump do banco `.sql.gz`
- pacote dos uploads `.tar.gz`

No clone do outro dev:

```bash
cp .env.example .env
docker compose up -d
./scripts/restore-mirror.sh caminho/para/dump.sql.gz caminho/para/uploads.tar.gz
```

Observações:
- `docker compose down` preserva o banco porque o MySQL usa volume Docker.
- `docker compose down -v` remove o volume e apaga o banco local.
- Os arquivos do espelho devem ser compartilhados fora do GitHub.

---

## Importar amostra de conteúdo

Para desenvolvimento local, use 500–2000 posts (amostra representativa):

```bash
./scripts/import-sample.sh caminho/para/export.xml
```

Gere o export em: **WP Admin → Ferramentas → Exportar** (do site atual).

---

## Estrutura

```
sz-a12-portal/
├── Dockerfile                  # Imagem customizada WordPress
├── docker-compose.yml          # Orquestra WordPress + MySQL
├── .env.example                # Variáveis de ambiente (modelo)
├── .gitignore
├── config/
│   └── php.ini                 # Configuração PHP (todos os ambientes)
├── db/
│   └── init/                   # SQLs executados na criação do banco
├── logs/                       # wp-debug.log (gerado em runtime)
├── scripts/
│   ├── wp                      # Wrapper WP-CLI
│   ├── setup-local.sh          # Primeiro setup
│   └── import-sample.sh        # Importação de amostra
└── wp-content/
    ├── mu-plugins/             # Must-use plugins (versionados)
    ├── themes/                 # Tema A12 (versionado)
    ├── plugins/                # Plugins aprovados (versionados)
    └── uploads/                # Mídia — NÃO versionado (volume Docker)
```

---

## Ambientes da arquitetura

| Ambiente | Banco | Storage | Containers |
|----------|-------|---------|-----------|
| **Local** | MySQL container | Volume Docker | Docker Compose |
| **DEV** | RDS Single-AZ | S3 `a12-dev-media` | ECS Fargate |
| **STAGE** | RDS Single-AZ | S3 `a12-stage-media` | ECS Fargate |
| **PROD** | RDS Multi-AZ | S3 `a12-prod-media` | ECS Fargate |

---

## Setup com Podman no Windows

Se você usa **Podman** em vez de Docker Desktop (ex.: Windows), siga estes passos.

### 1. Instalar Podman Desktop

Baixe e instale o [Podman Desktop](https://podman-desktop.io/).  
Ele inclui Podman Engine + Podman Compose e roda sobre WSL2.

### 2. Inicializar a máquina Podman

Abra o **PowerShell** (ou terminal do Podman Desktop):

```powershell
podman machine init
podman machine start
```

### 3. Habilitar compatibilidade Docker

No Podman Desktop: **Settings → Experimental → Docker Compatibility** → ativar.

Ou manualmente no PowerShell:

```powershell
# Testa se o alias funciona
podman compose version
```

> Com a compatibilidade ativada, os comandos `docker` e `docker compose` viram aliases para `podman` e `podman compose`. Todos os scripts do projeto funcionam sem alteração.

### 4. Clonar e subir

```powershell
git clone git@github.com:cbachini/sz-a12-portal.git
cd sz-a12-portal

copy .env.example .env
# Edite .env com as senhas e chaves (use notepad, VS Code, etc.)

podman compose up -d
```

Aguarde ~30s para o MySQL inicializar.

### 5. Rodar o setup (via WSL2)

Os scripts shell (.sh) não rodam nativamente no PowerShell.  
Use o **terminal WSL** do VS Code ou do Windows Terminal:

```bash
# Dentro do WSL (Ubuntu, por exemplo):
cd /mnt/c/Users/SEU_USUARIO/caminho/para/sz-a12-portal
./scripts/setup-local.sh
```

Ou rode os comandos WP-CLI diretamente:

```powershell
podman exec a12-wordpress wp core install --allow-root --url="http://localhost:8080" --title="Portal A12 (Local)" --admin_user=admin --admin_password=admin123 --admin_email=admin@a12.local --skip-email
podman exec a12-wordpress wp option update --allow-root permalink_structure "/%postname%/"
podman exec a12-wordpress wp rewrite flush --allow-root
```

### 6. Acessar

| URL | O quê |
|-----|-------|
| http://localhost:8080 | Front-end do portal |
| http://localhost:8080/wp-admin | Painel WordPress |

### Restaurar espelho (com dados do portal)

```powershell
# Descompactar o dump (use 7-Zip ou WSL):
# Copie os arquivos .sql.gz e .tar.gz para a pasta do projeto

# Via WSL:
./scripts/restore-mirror.sh db/dumps/dump.sql.gz db/dumps/uploads.tar.gz

# Ou manualmente via PowerShell:
podman exec -i a12-mysql mysql -ua12 -pSUA_SENHA a12_local < dump.sql
```

### Diferenças importantes Podman vs Docker

| Aspecto | Docker Desktop | Podman |
|---------|---------------|--------|
| Daemon | dockerd (sempre rodando) | Sem daemon (rootless) |
| Compose | `docker compose` nativo | `podman compose` (plugin ou podman-compose) |
| Volumes nomeados | Funciona igual | Funciona igual |
| `container_name` | Suportado | Suportado |
| Scripts .sh | Rodam no macOS/Linux | Rodam via WSL no Windows |
| Licença | Gratuito para uso pessoal | Gratuito (open source) |

### Troubleshooting Podman

**"podman compose" não encontrado:**  
Instale o plugin: `pip install podman-compose` ou atualize o Podman Desktop.

**Containers não comunicam entre si:**  
Verifique se a rede foi criada: `podman network ls`. Se não existir `a12-local-network`, rode `podman compose down` e `podman compose up -d` novamente.

**Porta não acessível no browser:**  
Confirme que a máquina Podman está rodando: `podman machine info`. Se parou, `podman machine start`.

**Scripts .sh não rodam:**  
Use WSL: abra o terminal WSL no VS Code (`Ctrl+Shift+P` → "WSL") ou rode `wsl` no PowerShell.

---

## Troubleshooting

**MySQL demora a subir:**  
O healthcheck aguarda até 30 tentativas. Se falhar, verifique: `docker compose logs mysql`

**Porta 8080 ocupada:**  
Altere `WP_PORT` no `.env` (ex.: `WP_PORT=9080`).

**Permissões em uploads:**  
```bash
docker exec a12-wordpress chown -R www-data:www-data /var/www/html/wp-content/uploads
```
