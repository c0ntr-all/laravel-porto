FROM node:20-alpine

WORKDIR /app

COPY client/package.json client/package-lock.json ./

RUN npm ci

EXPOSE 9000

CMD ["npm", "run", "dev", "--", "--hostname", "0.0.0.0", "--port", "9000"]
