# **Slide 1 — Título**

**Arquitetura de Infraestrutura – Portal A12**  
WordPress Containerizado em AWS

**Soyuz Digital Studio**

Escopo da apresentação:

* Infraestrutura  
* Ambientes  
* Storage  
* Banco de dados  
* Pipeline de deploy

---

# **Slide 2 — Objetivo da Arquitetura**

Esta arquitetura foi projetada para garantir que o novo Portal A12 opere de forma estável, segura e escalável, suportando um grande volume de conteúdo e permitindo evolução contínua do sistema.

Principais objetivos:

* desenvolvimento isolado e seguro  
* deploy contínuo e rastreável  
* rollback controlado  
* operação resiliente  
* preservação do acervo editorial (\~90.000 posts)  
* separação clara entre ambientes  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 3 — Princípios Arquiteturais**

A arquitetura segue princípios modernos de engenharia de software e infraestrutura, focados em automação, previsibilidade e segurança operacional.

Esses princípios orientam como o sistema é construído, implantado e operado.

Principais princípios:

* infraestrutura imutável  
* containers  
* CI/CD  
* separação de responsabilidades  
* least privilege  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 4 — Separação de Ambientes**

Para evitar riscos operacionais, cada fase do desenvolvimento possui um ambiente próprio. Isso permite testar mudanças sem afetar usuários reais.

Ambientes utilizados:

* Local  
* Development  
* Stage (Homologação)  
* Production

Essa separação evita:

* vazamento de dados  
* testes em produção  
* dependência de ambiente único  
* interferência entre equipes  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 5 — Infraestrutura Imutável**

Nesta arquitetura, a aplicação não é alterada manualmente nos servidores. Em vez disso, cada versão é empacotada como uma imagem que pode ser implantada de forma consistente em qualquer ambiente.

Cada imagem Docker contém:

* WordPress core  
* tema customizado  
* plugins aprovados  
* mu-plugins  
* configuração PHP

Dados persistentes **não ficam dentro do container**.

Novo A12 \- Arquitetura de Infra…

---

# **Slide 6 — Separação de Camadas**

O sistema foi dividido em camadas independentes. Isso permite escalar partes específicas do sistema sem afetar as demais.

Camadas principais:

Aplicação

* containers WordPress

Storage

* uploads e mídia

Banco

* MySQL gerenciado

Benefícios:

* rollback da aplicação sem afetar conteúdo  
* troca de containers sem downtime  
* escalabilidade horizontal  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 7 — Arquitetura Geral**

O portal utiliza uma arquitetura em nuvem baseada em serviços gerenciados da AWS, com camadas de segurança e distribuição de tráfego.

Fluxo da arquitetura:

Internet  
↓  
Cloudflare (CDN \+ WAF)  
↓  
Application Load Balancer  
↓  
ECS Fargate  
↓  
Containers WordPress

Serviços conectados:

* RDS MySQL  
* S3 (uploads)  
* Redis  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 8 — Infraestrutura AWS**

A solução utiliza serviços gerenciados da AWS para reduzir complexidade operacional e aumentar confiabilidade.

Serviços utilizados:

* ECS Fargate — execução de containers  
* ECR — registry de imagens  
* Aurora MySQL / RDS — banco gerenciado  
* S3 — armazenamento de mídia  
* ElastiCache Redis — cache  
* ALB — balanceamento de carga  
* Cloudflare — CDN / WAF  
* CloudWatch — logs e monitoramento  
* Secrets Manager — credenciais  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 9 — Containers**

Toda versão do portal é distribuída como uma imagem Docker. Isso garante consistência entre ambientes e facilita o processo de deploy.

Conteúdo da imagem:

* WordPress  
* plugins aprovados  
* tema customizado  
* mu-plugins  
* configuração PHP  
* wp-cli

Não são incluídos:

* uploads  
* banco de dados  
* arquivos gerados em runtime  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 10 — Storage**

Os arquivos enviados pelos usuários (imagens, vídeos e mídia) precisam de armazenamento persistente e escalável. Para isso é utilizado o Amazon S3.

Buckets separados por ambiente:

* a12-dev-media  
* a12-stage-media  
* a12-prod-media

Recursos habilitados:

* versionamento  
* criptografia  
* lifecycle policies  
* bloqueio público  
* controle via IAM  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 11 — Banco de Dados**

O conteúdo editorial do portal é armazenado em banco MySQL gerenciado pela AWS, garantindo disponibilidade e backup automático.

Configuração por ambiente:

Local

* MySQL container

DEV

* RDS Single-AZ

STAGE

* RDS Single-AZ robusto

PROD

* RDS Multi-AZ

Backups automáticos:

* DEV → 7 dias  
* STAGE → 7–14 dias  
* PROD → 30–35 dias  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 12 — Cache**

Para melhorar a performance do portal e reduzir consultas repetidas ao banco de dados, é utilizado um sistema de cache.

Funções do Redis:

* object cache WordPress  
* cache de queries  
* redução de carga no banco  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 13 — Pipeline CI/CD**

A entrega de novas versões do sistema é automatizada por um pipeline de integração e deploy contínuo.

Fluxo do pipeline:

Pull Request  
↓  
Testes automatizados  
↓  
Build da imagem Docker  
↓  
Push para ECR

Deploy DEV automático após merge.

Novo A12 \- Arquitetura de Infra…

---

# **Slide 14 — Estratégia de Deploy**

Os deploys são feitos de forma gradual para evitar indisponibilidade e permitir rollback rápido caso algo falhe.

Estratégia utilizada:

* rolling update  
* blue/green deployment

Processo:

* novos containers iniciados  
* health checks verificados  
* tráfego migrado gradualmente  
* rollback automático em caso de falha  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 15 — Segurança**

A infraestrutura foi projetada com múltiplas camadas de segurança para proteger dados e acesso administrativo.

Medidas implementadas:

IAM

* least privilege  
* roles específicas por serviço

Secrets

* AWS Secrets Manager  
* nenhuma credencial em repositório

Rede

* RDS privado  
* Redis privado  
* containers em subnets privadas

Acesso administrativo:

* bastion ou console AWS  
* sem SSH direto nos containers  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 16 — Observabilidade**

Para manter o sistema estável em produção, logs e métricas são monitorados continuamente.

Logs centralizados em:

* CloudWatch

Tipos de logs:

* access logs  
* PHP logs  
* WordPress logs  
* container logs

Alertas para:

* erros HTTP  
* falha de deploy  
* uso excessivo de CPU  
* saturação de banco  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 17 — Escalabilidade**

A arquitetura permite que o portal aumente sua capacidade automaticamente conforme o volume de acesso cresce.

Escalabilidade via ECS:

* aumento automático de containers  
* balanceamento via ALB

Escala baseada em:

* CPU  
* memória  
* requisições  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 18 — Estratégia de Migração**

O portal atual possui um grande acervo editorial. A migração será realizada de forma gradual para reduzir riscos.

Volume estimado:

* \~90.000 posts

Estratégia:

Local

* amostra representativa (500–2000 posts)

DEV

* dataset intermediário

STAGE

* dataset quase completo

PROD

* acervo completo  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 19 — Backup e Recuperação**

A arquitetura inclui mecanismos de backup e recuperação para garantir continuidade do serviço em caso de falhas.

Backups incluem:

* banco RDS  
* snapshots  
* versionamento S3

Procedimentos previstos:

* restauração de banco  
* rollback de aplicação  
* restauração de mídia  
   Novo A12 \- Arquitetura de Infra…

---

# **Slide 20 — Governança de Deploy**

Mudanças em produção seguem um processo controlado para garantir rastreabilidade e segurança.

Requisitos para deploy:

* revisão de código  
* aprovação de deploy  
* registro no pipeline

Nenhuma alteração é feita manualmente no servidor.

Novo A12 \- Arquitetura de Infra…

---

# **Slide 21 — Operação**

Após o lançamento, a operação do sistema envolve monitoramento contínuo e manutenção controlada.

Atividades operacionais:

* monitoramento contínuo  
* atualização controlada de plugins  
* atualizações de segurança do WordPress  
* auditoria de acessos

