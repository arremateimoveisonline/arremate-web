# Documentação Técnica — Arremate Imóveis Online

## Como funciona este sistema de documentos

Cada documento importante do projeto tem **versões numeradas**. Nunca apagamos uma versão anterior — elas ficam aqui para consulta e comparação.

### Regra de versionamento

| Quando criar uma nova versão | Exemplo |
|---|---|
| Uma lógica importante mudou | Mudamos como o scraper prioriza imóveis |
| Um componente novo foi adicionado | Novo agente TI, novo cron |
| Um bug importante foi corrigido e afetou o fluxo | Correção de modalidade |
| A estrutura do banco mudou | Nova coluna adicionada |

### Como criar uma nova versão

1. Copie o arquivo atual (ex: `fluxo-atualizacao-v1.md`)
2. Renomeie para `fluxo-atualizacao-v2.md`
3. No topo do novo arquivo, preencha o bloco `## O que mudou nesta versão`
4. Faça as alterações necessárias no conteúdo
5. Atualize o link "Versão atual" abaixo
6. **Não apague** o arquivo anterior

---

## Documentos disponíveis

| Documento | Versão atual | O que descreve |
|---|---|---|
| [Fluxo de Atualização](fluxo-atualizacao-v1.md) | **v1** (2026-04-27) | Como os imóveis são importados, raspados e atualizados diariamente |

---

> **Dica:** Sempre que você ou o Claude fizerem uma mudança relevante no sistema, abra um documento de nova versão antes de esquecer o que foi feito e por quê.
