"""
Lê /tmp/datas_leilao.json e gera /tmp/update_datas.sql
com os comandos UPDATE para atualizar as datas no banco SQLite.
"""
import json
import sys

try:
    datas = json.load(open('/tmp/datas_leilao.json'))
except Exception as e:
    print(f"Erro ao ler JSON: {e}")
    sys.exit(1)

if not datas:
    print("Nenhuma data para salvar.")
    sys.exit(0)

with open('/tmp/update_datas.sql', 'w') as f:
    for hdn, data in datas.items():
        hdn_safe  = hdn.replace("'", "''")
        data_safe = data.replace("'", "''")
        f.write(f"UPDATE imoveis SET data_encerramento='{data_safe}', scraped_at=datetime('now') WHERE hdnimovel='{hdn_safe}';\n")

print(f"{len(datas)} comandos SQL gerados em /tmp/update_datas.sql")
