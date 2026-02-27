# Certificados SSL

Coloque seus certificados SSL aqui:

- `cert.crt` - Certificado SSL
- `cert.key` - Chave privada do certificado

Para desenvolvimento local, você pode gerar certificados auto-assinados:

```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout cert.key \
  -out cert.crt \
  -subj "/C=BR/ST=State/L=City/O=Organization/CN=localhost"
```

**IMPORTANTE**: Não commite certificados reais no repositório. Use variáveis de ambiente ou secrets management em produção.
