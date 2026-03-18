# Portal A12 — Ambiente Local

WordPress containerizado para desenvolvimento local.  
Parte da arquitetura: **Local → DEV → STAGE → PROD (AWS ECS Fargate)**.

---

## Pré-requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) ≥ 4.x
- Docker Compose v2 (incluído no Docker Desktop)

---

## Primeiro uso

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

## Troubleshooting

**MySQL demora a subir:**  
O healthcheck aguarda até 30 tentativas. Se falhar, verifique: `docker compose logs mysql`

**Porta 8080 ocupada:**  
Altere `WP_PORT` no `.env` (ex.: `WP_PORT=9080`).

**Permissões em uploads:**  
```bash
docker exec a12-wordpress chown -R www-data:www-data /var/www/html/wp-content/uploads
```
