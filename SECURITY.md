# 🔒 Política de Segurança - Arremate Imóveis Online

## ⚠️ DESCOBERTA CRÍTICA (2026-05-02)

**Credenciais SSH foram expostas no repositório público!**

### O que aconteceu:
- Senha VPS: `M@lu1710` estava hardcoded em 9 scripts JavaScript
- Repositório é PÚBLICO no GitHub
- Qualquer pessoa poderia ter acessado a VPS

### O que foi feito:
✅ Refatorado para usar variáveis de ambiente  
✅ `.env.example` criado como template  
✅ `.gitignore` atualizado  
✅ Novo commit: `216dd41` (segurança)  

### ⚡ AÇÃO OBRIGATÓRIA - FAÇA AGORA:

#### 1. MUDE A SENHA ROOT DA VPS IMEDIATAMENTE
```bash
ssh root@lcmcreativestudio.vps-kinghost.net
passwd
# Digite uma senha forte (20+ caracteres):
# - Letras maiúsculas + minúsculas
# - Números + símbolos (!@#$%^&*)
# - Sem palavras dicionário
# Exemplo: Tr0p1c@lR@1nf0rest#2026!
```

#### 2. Crie o arquivo `.env` localmente (NUNCA comitte!)
```bash
cp .env.example .env
# Edite .env com a NOVA senha da VPS
```

#### 3. Use variável de ambiente para deployar
```bash
# Windows:
set VPS_PASS=sua_nova_senha_forte && node deploy-hotfix.js

# Linux/Mac:
export VPS_PASS=sua_nova_senha_forte && node deploy-hotfix.js
```

#### 4. Confirme que o `.env` nunca será commitado
```bash
git status  # não deve aparecer .env
git ls-files -o --exclude-standard | grep .env  # deve estar vazio
```

---

## 🔐 Boas Práticas de Segurança

### 1. Credenciais & Secrets
- ❌ NUNCA hardcode senhas, tokens ou chaves no código
- ✅ Use variáveis de ambiente (`.env`)
- ✅ `.env` deve estar no `.gitignore`
- ✅ Commite apenas `.env.example`

### 2. Repositório GitHub
- ⚠️ Repositório é **PÚBLICO** (qualquer pessoa pode ver o código)
- ✅ Nunca coloque informações sensíveis (senhas, tokens, emails de clientes)
- ✅ Use GitHub Secrets para CI/CD (GitHub Actions)

### 3. SSH & Acesso à VPS
- ✅ Prefira SSH keys ao invés de passwords
- ✅ Chave privada deve estar em `~/.ssh/id_ed25519` (local)
- ✅ Nunca adicione chave privada ao Git
- ✅ Altere senha root regularmente (a cada 3 meses)

### 4. Banco de Dados
- ✅ Senhas do banco separadas em variáveis de ambiente
- ✅ Permissões mínimas (`PRAGMA query_only=1` para reads)
- ✅ Backups regulares (você já faz ✅)

### 5. Logs & Alertas
- ✅ Logs não devem expor senhas (você já trata isso ✅)
- ✅ Monitorar tentativas de acesso SSH falhadas
- ✅ Alertar em caso de mudanças no código (GitHub webhooks)

---

## 📋 Checklist de Segurança

- [ ] Senha root da VPS alterada
- [ ] `.env` criado localmente (de `.env.example`)
- [ ] `.env` no `.gitignore` (verificar com `git status`)
- [ ] Deploy testado com nova senha via variável de ambiente
- [ ] GitHub repository verificado como PRIVADO (se necessário)
- [ ] Rever outros scripts (check-gd.js, fix-desc.js, etc.) para credenciais
- [ ] Ativar 2FA no GitHub (Settings → Security)
- [ ] Revisar git history de credenciais expostas anteriormente

---

## 🚨 Descoberta de Segurança?

Se encontrar uma credencial exposta:
1. Altere a senha/token imediatamente
2. Revogue tokens ou reneove chaves SSH
3. Faça commit na branch, abra PR
4. Não exponha a credencial ANTIGA no PR (já foi exposta)
5. Sempre use variáveis de ambiente daqui em diante

---

## Referências

- [OWASP: Sensitive Data Exposure](https://owasp.org/www-project-top-ten/)
- [GitHub: Protecting secrets](https://docs.github.com/en/actions/security-guides/encrypted-secrets)
- [Node.js: dotenv](https://www.npmjs.com/package/dotenv)
- [SSH Best Practices](https://www.ssh.com/academy/ssh/best-practices)
