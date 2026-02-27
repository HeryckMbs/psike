FROM node:20-alpine

WORKDIR /app

# Instalar dependências primeiro (cache layer)
COPY package*.json ./
RUN npm install

# Copiar código fonte
COPY . .

# Expor porta
EXPOSE 5173

# Comando padrão
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
