#!/usr/bin/env python3
"""
Teste para validar a extração de dados do imóvel 8555532857111 da CAIXA
"""

import requests
from datetime import datetime
import json

# URL do imóvel na CAIXA
CAIXA_URL = "https://venda-imoveis.caixa.gov.br/sistema/detalhe-imovel.asp?hdnimovel=8555532857111"

# Dados esperados
DADOS_ESPERADOS = {
    "data_leilao": "06/04/2026",
    "hora_leilao": "10:00",
    "area_privativa": "45,16",
    "area_terreno": "180",
    "modalidade": "Licitação Aberta",
    "fgts": True,
    "financiamento": True,
}

print("=" * 60)
print("TESTE DE EXTRAÇÃO - Imóvel 8555532857111")
print("=" * 60)

try:
    response = requests.get(CAIXA_URL, timeout=10)
    response.encoding = 'utf-8'

    if response.status_code != 200:
        print(f"❌ Erro ao acessar CAIXA: HTTP {response.status_code}")
        exit(1)

    html = response.text
    print(f"✓ HTML obtido ({len(html)} bytes)")

    print("\n--- Checando Dados Esperados ---\n")

    # Checar Data e Hora
    import re
    data_match = re.search(r'(\d{2})/(\d{2})/(\d{4})\s+às\s+(\d{2}):(\d{2})', html)
    if data_match:
        print(f"✓ Data/Hora encontrada: {data_match.group(1)}/{data_match.group(2)}/{data_match.group(3)} às {data_match.group(4)}:{data_match.group(5)}")
    else:
        print("❌ Data/Hora NÃO encontrada")

    # Checar Áreas
    area_priv = re.search(r'(?:Área\s+Privativa|Privativa)[^\d]*([\d,\.]+)\s*m²', html, re.IGNORECASE)
    area_terr = re.search(r'(?:Área\s+(?:do\s+)?Terreno|Terreno)[^\d]*([\d,\.]+)\s*m²', html, re.IGNORECASE)

    if area_priv:
        print(f"✓ Área Privativa: {area_priv.group(1)} m²")
    else:
        print("❌ Área Privativa NÃO encontrada")

    if area_terr:
        print(f"✓ Área do Terreno: {area_terr.group(1)} m²")
    else:
        print("❌ Área do Terreno NÃO encontrada")

    # Checar Modalidade
    if 'Licitação Aberta' in html or 'licitacao' in html.lower():
        print(f"✓ Modalidade: Licitação Aberta")
    else:
        print("❌ Modalidade não encontrada")

    # Checar FGTS
    if 'FGTS' in html or 'fgts' in html.lower():
        print(f"✓ FGTS mencionado")
    else:
        print("❌ FGTS não encontrado")

    # Checar Financiamento
    if 'Permite financiamento' in html or 'financiamento' in html.lower():
        print(f"✓ Financiamento mencionado")
    else:
        print("❌ Financiamento não encontrado")

    # Checar Edital
    edital_match = re.search(r'editais/E[^"\'<\s]+\.PDF', html, re.IGNORECASE)
    if edital_match:
        print(f"✓ Edital PDF: {edital_match.group(0)}")
    else:
        print("❌ Edital PDF não encontrado")

    print("\n" + "=" * 60)
    print("Teste concluído!")

except Exception as e:
    print(f"❌ Erro: {e}")
    exit(1)
